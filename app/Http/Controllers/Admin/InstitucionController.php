<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarInstitucionRequest;
use App\Models\Institucion;
use App\Services\EstructuraInstitucionalService;
use App\Services\ExpedienteClinicoService;
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
        $instituciones = Institucion::query()
            ->withCount([
                'departamentos',
                'membresias as padron_count' => fn ($q) => $q->enPadron(),
                'membresias as activas_count' => fn ($q) => $q->where('estado', 'activo'),
                'eventosCrisis as casos_abiertos_count' => fn ($q) => $q->abiertos(),
            ])
            ->orderBy('nombre_corto')
            ->get();

        $padron = (int) $instituciones->sum('padron_count');
        $activas = (int) $instituciones->sum('activas_count');

        $metricas = [
            'instituciones' => $instituciones->count(),
            'instituciones_activas' => $instituciones->where('estado', 'activa')->count(),
            'padron' => $padron,
            'activas' => $activas,
            'adopcion' => $padron > 0 ? round($activas / $padron * 100) : null,
            'casos_abiertos' => (int) $instituciones->sum('casos_abiertos_count'),
        ];

        return view('admin.instituciones.index', compact('instituciones', 'metricas'));
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
        $arbol = $this->estructura->arbol($institucion);

        $resumen = [
            'padron' => $institucion->membresias()->enPadron()->count(),
            'activas' => $institucion->membresias()->where('estado', 'activo')->count(),
            'invitadas' => $institucion->membresias()->where('estado', 'invitado')->count(),
            'macro_areas' => count($arbol),
            'areas' => array_sum(array_map(fn ($macro) => count($macro['hijos']), $arbol)),
            'casos_abiertos' => $institucion->eventosCrisis()->abiertos()->count(),
        ];
        $resumen['adopcion'] = $resumen['padron'] > 0
            ? round($resumen['activas'] / $resumen['padron'] * 100)
            : null;

        $otras = Institucion::orderBy('nombre_corto')->get(['id', 'slug', 'nombre_corto']);

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
        $personas = $esAdmin
            ? $institucion->membresias()
                ->with(['user:id,name,email', 'departamento:id,nombre,parent_id'])
                ->get()
                ->sortBy([
                    fn ($a, $b) => ($a->estado === 'baja') <=> ($b->estado === 'baja'),
                    fn ($a, $b) => strcasecmp((string) $a->user?->name, (string) $b->user?->name),
                ])->values()
            : collect();

        $invitaciones = [
            'activas' => $personas->where('estado', 'activo')->count(),
            'enviadas' => $personas->whereIn('estado', ['invitado', 'suspendido'])->whereNotNull('invitado_en')->count(),
            'sin_enviar' => $personas->whereIn('estado', ['invitado', 'suspendido'])->whereNull('invitado_en')->count(),
        ];

        return view('admin.instituciones.show', compact(
            'institucion', 'arbol', 'resumen', 'otras', 'ultimaCarga', 'filasConProblema',
            'esClinico', 'esAdmin', 'filtros', 'colaboradores', 'opcionesArea', 'personas', 'invitaciones'
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
