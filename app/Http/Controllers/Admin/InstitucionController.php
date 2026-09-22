<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarInstitucionRequest;
use App\Models\Institucion;
use App\Services\EliminacionInstitucionService;
use App\Services\EstructuraInstitucionalService;
use App\Services\ExpedienteClinicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitucionController extends Controller
{
    public function __construct(private EstructuraInstitucionalService $estructura)
    {
    }

    /**
     * Listado de instituciones con su pulso operativo.
     */
    public function index()
    {
        // Un clínico sólo ve las instituciones que tiene asignadas.
        $visibles = request()->user()->institucionesVisiblesIds();

        $todas = Institucion::query()
            ->when($visibles !== null, fn ($q) => $q->whereIn('id', $visibles))
            ->with('archivadaPor:id,name')
            ->withCount([
                'departamentos',
                'membresias as padron_count' => fn ($q) => $q->enPadron()->soloColaboradores(),
                'membresias as activas_count' => fn ($q) => $q->where('estado', 'activo')->soloColaboradores(),
                'eventosCrisis as casos_abiertos_count' => fn ($q) => $q->abiertos(),
            ])
            ->orderBy('nombre_corto')
            ->get();

        // Las archivadas van aparte: se ven y se restauran, pero no operan.
        [$archivadas, $instituciones] = $todas->partition(fn (Institucion $i) => $i->estaArchivada());

        return view('admin.instituciones.index', [
            'instituciones' => $instituciones->values(),
            'archivadas' => $archivadas->values(),
        ]);
    }

    /** «Eliminar» desde la lista: la institución pasa a Archivadas. */
    public function archivar(Request $request, Institucion $institucion, EliminacionInstitucionService $servicio)
    {
        if (!$institucion->estaArchivada()) {
            $servicio->archivar($institucion, $request->user());
        }

        return redirect()->route('admin.instituciones.index')->with('success',
            "«{$institucion->nombre_corto}» se movió a Archivadas. Su información se conserva y puedes restaurarla cuando quieras.");
    }

    public function restaurar(Institucion $institucion, EliminacionInstitucionService $servicio)
    {
        $servicio->restaurar($institucion);

        return redirect()->route('admin.instituciones.show', $institucion)
            ->with('success', "«{$institucion->nombre_corto}» se restauró y vuelve a operar.");
    }

    /**
     * Eliminación definitiva: sólo desde Archivadas, escribiendo el nombre
     * corto para confirmar y sin casos de crisis abiertos.
     */
    public function destroy(Request $request, Institucion $institucion, EliminacionInstitucionService $servicio)
    {
        $volver = redirect()->to(route('admin.instituciones.index') . '#archivadas');

        if (!$institucion->estaArchivada()) {
            return $volver->with('error', 'Primero archiva la institución; sólo se elimina para siempre desde Archivadas.');
        }

        $request->validate(['confirmacion' => ['required', 'string']], [
            'confirmacion.required' => 'Escribe el nombre de la institución para confirmar.',
        ]);
        if (trim((string) $request->input('confirmacion')) !== $institucion->nombre_corto) {
            return $volver->with('error', "El nombre no coincide. Escribe exactamente «{$institucion->nombre_corto}» para eliminarla.");
        }

        if ($abiertos = $servicio->casosAbiertos($institucion)) {
            return $volver->with('error', "«{$institucion->nombre_corto}» tiene {$abiertos} " . ($abiertos === 1 ? 'caso de crisis abierto' : 'casos de crisis abiertos')
                . '. Ciérralos con contacto humano verificado antes de eliminarla.');
        }

        $nombre = $institucion->nombre_corto;
        $resultado = $servicio->eliminar($institucion, $request->user());

        return $volver->with('success', "«{$nombre}» se eliminó para siempre junto con {$resultado['cuentas']} "
            . ($resultado['cuentas'] === 1 ? 'cuenta' : 'cuentas') . ' de su padrón.');
    }

    public function store(GuardarInstitucionRequest $request)
    {
        $institucion = DB::transaction(function () use ($request) {
            $datos = $request->datosInstitucion();

            $institucion = Institucion::create($datos + [
                'slug' => Institucion::generarSlug($datos['nombre_corto']),
                'estado' => 'onboarding',
                'vigencia_inicio' => now()->toDateString(),
                'iniciales' => Institucion::calcularIniciales($datos['nombre_corto']),
            ]);

            $this->estructura->crearMacroAreas(
                $institucion,
                EstructuraInstitucionalService::parsearLista($request->input('macro_areas'))
            );

            return $institucion;
        });

        return redirect()
            ->route('admin.instituciones.show', $institucion)
            ->with('success', "Institución «{$institucion->nombre_corto}» registrada.");
    }

    public function show(Institucion $institucion)
    {
        abort_unless(request()->user()->puedeVerInstitucion($institucion->id), 404);

        $arbol = $this->estructura->arbol($institucion);

        // Administración y clínicos no cuentan en padrón ni en activaciones.
        $resumen = [
            'padron' => $institucion->membresias()->enPadron()->soloColaboradores()->count(),
            'activas' => $institucion->membresias()->where('estado', 'activo')->soloColaboradores()->count(),
            'invitadas' => $institucion->membresias()->where('estado', 'invitado')->soloColaboradores()->count(),
            'macro_areas' => count($arbol),
            'areas' => array_sum(array_map(fn ($macro) => count($macro['hijos']), $arbol)),
            'casos_abiertos' => $institucion->eventosCrisis()->abiertos()->count(),
        ];
        $resumen['adopcion'] = $resumen['padron'] > 0
            ? round($resumen['activas'] / $resumen['padron'] * 100)
            : null;

        $visibles = request()->user()->institucionesVisiblesIds();
        $otras = Institucion::query()->vigentes()
            ->when($visibles !== null, fn ($q) => $q->whereIn('id', $visibles))
            ->orderBy('nombre_corto')->get(['id', 'slug', 'nombre_corto']);

        $ultimaCarga = $institucion->cargas()->with('autor:id,name')->latest('id')->first();
        $filasConProblema = $ultimaCarga
            ? $ultimaCarga->filas()->whereIn('estado', ['error', 'advertencia'])
                ->orderByRaw("case when estado = 'error' then 0 else 1 end")->orderBy('numero_fila')->get()
            : collect();

        // Plano clínico: sólo con acreditación; un administrador sin ella no ve identidades ni puntajes.
        $esClinico = (bool) request()->user()?->isClinicoAcreditado();
        $filtros = request()->only(['q', 'departamento', 'turno', 'semaforo', 'bandera']);
        $colaboradores = $esClinico
            ? app(ExpedienteClinicoService::class)
                ->colaboradores($institucion, $filtros, (int) request('pagina', 1))
                ->withPath(route('admin.instituciones.show', $institucion))
                ->appends($filtros)
                ->fragment('colaboradores')
            : null;
        $opcionesArea = collect($arbol)->flatMap(fn ($macro) => collect([[$macro['modelo']->id, $macro['modelo']->nombre]])
            ->merge(collect($macro['hijos'])->map(fn ($h) => [$h['modelo']->id, $macro['modelo']->nombre . ' › ' . $h['modelo']->nombre])));

        // Padrón e invitaciones: operación de administración, sin datos clínicos.
        $esAdmin = (bool) request()->user()?->is_admin;
        // El clínico asignado ve padrón e invitaciones en modo lectura; sólo la administración los modifica.
        $vePadron = $esAdmin || $esClinico;
        $personas = $vePadron
            ? $institucion->membresias()
                ->with(['user:id,name,email,role,is_admin,is_clinico_atulado', 'departamento:id,nombre,parent_id'])
                ->get()
                ->sortBy([
                    fn ($a, $b) => ($a->estado === 'baja') <=> ($b->estado === 'baja'),
                    fn ($a, $b) => strcasecmp((string) $a->user?->name, (string) $b->user?->name),
                ])->values()
            : collect();

        $colaboradoresPadron = $personas->reject(fn ($p) => (bool) $p->user?->esPersonalDePlataforma());
        $invitaciones = [
            'activas' => $colaboradoresPadron->where('estado', 'activo')->count(),
            'enviadas' => $colaboradoresPadron->whereIn('estado', ['invitado', 'suspendido'])->whereNotNull('invitado_en')->count(),
            'sin_enviar' => $colaboradoresPadron->whereIn('estado', ['invitado', 'suspendido'])->whereNull('invitado_en')->count(),
        ];

        return view('admin.instituciones.show', compact(
            'institucion', 'arbol', 'resumen', 'otras', 'ultimaCarga', 'filasConProblema',
            'esClinico', 'esAdmin', 'vePadron', 'filtros', 'colaboradores', 'opcionesArea', 'personas', 'invitaciones',
            'colaboradoresPadron'
        ));
    }

    public function update(GuardarInstitucionRequest $request, Institucion $institucion)
    {
        $avisos = DB::transaction(function () use ($request, $institucion) {
            $datos = $request->datosInstitucion();

            if ($datos['nombre_corto'] !== $institucion->nombre_corto) {
                $datos['iniciales'] = Institucion::calcularIniciales($datos['nombre_corto']);
            }

            $institucion->update($datos);

            return $this->estructura->aplicarCambios(
                $institucion,
                (array) $request->input('areas', []),
                EstructuraInstitucionalService::parsearLista($request->input('nuevas_macro'))
            );
        });

        $respuesta = redirect()
            ->route('admin.instituciones.show', $institucion)
            ->with('success', 'Datos de la institución actualizados.');

        if ($avisos !== []) {
            $respuesta->with('info', implode(' ', $avisos));
        }

        return $respuesta;
    }
}
