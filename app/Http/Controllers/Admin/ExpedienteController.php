<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ResumenClinicoMail;
use App\Models\AuditoriaClinica;
use App\Models\EntregaResumen;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Services\ClinicalEngineService;
use App\Services\ExpedienteClinicoService;
use App\Services\ResumenClinicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Plano clínico: ficha individual y acciones de protocolo.
 *
 * Todo exige acreditación clínica, además de ser administrador. Cada
 * apertura de ficha y cada resumen generado quedan en auditoria_clinica,
 * que es inmutable.
 */
class ExpedienteController extends Controller
{
    public function __construct(
        private ExpedienteClinicoService $expediente,
        private ClinicalEngineService $motor,
    ) {
    }

    private function exigirClinico(Request $request, Institucion $institucion, Membresia $membresia): void
    {
        abort_unless($request->user()?->isClinicoAcreditado(), 403, 'Se requiere acreditación clínica.');
        abort_unless($membresia->institucion_id === $institucion->id, 404);
        // Un clínico sólo actúa en las instituciones que tiene asignadas.
        abort_unless($request->user()->puedeAtenderInstitucion($institucion->id), 404);
    }

    private function registrar(Request $request, Membresia $membresia, string $accion, string $motivo, ?string $detalle = null): AuditoriaClinica
    {
        return AuditoriaClinica::create([
            'profesional_id' => $request->user()->id,
            'usuario_consultado_id' => $membresia->user_id,
            'institucion_id' => $membresia->institucion_id,
            'accion' => $accion,
            'motivo' => $motivo,
            'detalle' => $detalle,
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Devuelve el HTML de la ficha. Es POST porque abrirla escribe en la bitácora.
     */
    public function ficha(Request $request, Institucion $institucion, Membresia $membresia)
    {
        $this->exigirClinico($request, $institucion, $membresia);

        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo.required' => 'Declara el motivo de la consulta: queda en la bitácora.',
            'motivo.min' => 'Describe el motivo con un poco más de detalle.',
        ]);

        $acceso = $this->registrar($request, $membresia, 'consulta_detalle', trim($datos['motivo']), 'Apertura de ficha individual.');

        return view('admin.instituciones.partials.ficha', $this->expediente->ficha($membresia) + [
            'institucion' => $institucion,
            'acceso' => $acceso->load('profesional:id,name'),
        ]);
    }

    public function escalar(Request $request, Institucion $institucion, Membresia $membresia)
    {
        $this->exigirClinico($request, $institucion, $membresia);

        $datos = $request->validate([
            'nivel' => ['required', 'in:NARANJA,ROJO,ROJO_AGUDO'],
            'justificacion' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'nivel.in' => 'Elige un nivel válido.',
            'justificacion.required' => 'La elevación manual necesita una justificación clínica.',
            'justificacion.min' => 'Describe la justificación con un poco más de detalle.',
        ]);

        $this->motor->elevarNivelManual($request->user(), $membresia->user, $datos['nivel'], trim($datos['justificacion']));

        $texto = ExpedienteClinicoService::ETIQUETA_NIVEL[$datos['nivel']];
        $mensaje = "{$membresia->folio} elevado a {$texto}.";
        if ($datos['nivel'] !== 'NARANJA') {
            $mensaje .= ' Se abrió un caso de crisis: no se cierra sin contacto humano verificado.';
        }

        return $this->volver($institucion)->with('success', $mensaje);
    }

    public function contacto(Request $request, Institucion $institucion, Membresia $membresia, EventoCrisis $caso)
    {
        $this->exigirClinico($request, $institucion, $membresia);
        abort_unless($caso->user_id === $membresia->user_id, 404);

        $datos = $request->validate(['nota' => ['nullable', 'string', 'max:1000']]);

        if ($caso->estaCerrado()) {
            return $this->volver($institucion)->with('error', 'Ese caso ya está cerrado.');
        }

        if (!$this->motor->registrarContactoHumano($caso, $request->user(), $datos['nota'] ?? null)) {
            return $this->volver($institucion)->with('error', 'Ese caso ya está cerrado.');
        }

        return $this->volver($institucion)
            ->with('success', 'Contacto humano registrado en ' . ExpedienteClinicoService::codigoCaso($caso) . '.');
    }

    public function reabrir(Request $request, Institucion $institucion, Membresia $membresia, EventoCrisis $caso)
    {
        $this->exigirClinico($request, $institucion, $membresia);
        abort_unless($caso->user_id === $membresia->user_id, 404);

        $datos = $request->validate(...ColaAtencionController::reglasReapertura());

        return $this->volver($institucion)->with(...ColaAtencionController::mensajeReapertura(
            $this->motor->reabrirCaso($caso, $request->user(), trim($datos['motivo'])),
            $caso
        ));
    }

    public function cerrar(Request $request, Institucion $institucion, Membresia $membresia, EventoCrisis $caso)
    {
        $this->exigirClinico($request, $institucion, $membresia);
        abort_unless($caso->user_id === $membresia->user_id, 404);

        $datos = $request->validate([
            'notas' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'notas.required' => 'El cierre necesita la nota del contacto verificado.',
            'notas.min' => 'Describe el cierre con un poco más de detalle.',
        ]);

        if (!$this->motor->verificarCierreCaso($caso, $request->user(), trim($datos['notas']))) {
            return $this->volver($institucion)->with('error', $caso->contactado_en === null
                ? 'No se puede cerrar un caso sin contacto humano registrado.'
                : 'El caso no se pudo cerrar.');
        }

        return $this->volver($institucion)
            ->with('success', ExpedienteClinicoService::codigoCaso($caso) . ' cerrado con contacto humano verificado.');
    }

    /**
     * Genera el resumen clínico y lo envía por correo como enlace que vence.
     *
     * Sólo a un profesional con correo y NDA vigente (el designado por la
     * institución) o a la guardia de A Tu Lado. El documento se guarda tal
     * como se generó: quien lo abre ve esa foto, con folio y marca de agua.
     */
    public function resumen(Request $request, Institucion $institucion, Membresia $membresia, ResumenClinicoService $resumenes)
    {
        $this->exigirClinico($request, $institucion, $membresia);

        $datos = $request->validate([
            'destinatario' => ['required', 'in:profesional,guardia'],
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
            'vigencia_horas' => ['required', 'in:24,72,168'],
            'alcance' => ['nullable', 'array'],
            'alcance.*' => ['string', 'in:' . implode(',', array_keys(ResumenClinicoService::ALCANCE))],
        ], [
            'destinatario.required' => 'Indica a quién se entrega el resumen.',
            'motivo.required' => 'Indica el motivo de la entrega.',
        ]);
        $alcance = array_values(array_unique($datos['alcance'] ?? []));

        [$nombre, $email, $problema] = $this->destinatario($institucion, $datos['destinatario']);
        if ($problema) {
            return $this->volver($institucion)->with('error', $problema);
        }

        $detalle = $datos['destinatario'] === 'profesional' && $institucion->profesional_cedula
            ? "{$nombre} · Céd. {$institucion->profesional_cedula}"
            : $nombre;

        $entrega = DB::transaction(function () use ($request, $institucion, $membresia, $datos, $nombre, $email, $detalle, $alcance, $resumenes) {
            $entrega = EntregaResumen::create([
                'folio' => 'RC-' . Str::upper(Str::random(10)), // provisional hasta tener id
                'membresia_id' => $membresia->id,
                'generado_por' => $request->user()->id,
                'destinatario_nombre' => $nombre,
                'destinatario_email' => $email,
                'motivo' => trim($datos['motivo']),
                'alcance' => $alcance,
                'contenido' => '',
                'vence_en' => now()->addHours((int) $datos['vigencia_horas']),
            ]);

            $folio = 'RC-' . now()->format('Y') . '-' . str_pad((string) $membresia->id, 4, '0', STR_PAD_LEFT) . '-' . $entrega->id;

            $documento = $resumenes->construir($membresia, $institucion, $alcance, [
                'folio' => $folio,
                'destinatario' => $nombre,
                'destinatario_detalle' => $detalle,
                'motivo' => trim($datos['motivo']),
                'generado_por' => $request->user(),
                'generado_en' => now(),
                'vence_en' => $entrega->vence_en,
            ]);

            $entrega->update([
                'folio' => $folio,
                'paginas' => $documento['paginas'],
                'contenido' => $documento['html'],
            ]);

            $secciones = implode(', ', array_map(fn ($c) => ResumenClinicoService::ALCANCE[$c], $alcance)) ?: 'sólo identificación, instrumentos y cronología';
            $this->registrar($request, $membresia, 'resumen_generado', trim($datos['motivo']),
                "Resumen {$folio} ({$documento['paginas']} págs.) enviado a {$nombre} <{$email}>; vence {$entrega->vence_en->format('d/m/Y H:i')}. Alcance: {$secciones}.");

            return $entrega;
        });

        Mail::to($email)->queue(new ResumenClinicoMail($entrega, URL::temporarySignedRoute(
            'resumen.ver', $entrega->vence_en, ['entrega' => $entrega->id]
        )));

        // Se abre el documento para imprimirlo o guardarlo como PDF en el momento.
        return redirect()->route('admin.resumenes.ver', $entrega)
            ->with('success', "Resumen {$entrega->folio} enviado a {$nombre}. El enlace vence el {$entrega->vence_en->format('d/m/Y H:i')}.");
    }

    /**
     * Visor del resumen emitido, dentro del panel: el mismo documento que
     * recibe el destinatario, con «Emitir otro» y «Descargar PDF».
     */
    public function verResumen(Request $request, EntregaResumen $entrega)
    {
        $entrega->loadMissing('membresia.institucion');
        $membresia = $entrega->membresia;
        abort_unless($membresia && $membresia->institucion, 404);
        $this->exigirClinico($request, $membresia->institucion, $membresia);

        $this->registrar($request, $membresia, 'resumen_consultado', 'Consulta del resumen emitido',
            "Apertura en el panel del resumen {$entrega->folio}.");

        $barra = view('admin.instituciones.resumen-visor-barra', [
            'entrega' => $entrega,
            'institucion' => $membresia->institucion->nombre_corto,
            'periodo' => null,
            'emitirOtro' => route('admin.instituciones.show', ['institucion' => $membresia->institucion, 'q' => $membresia->folio]) . '#colaboradores',
        ])->render();

        return response(self::conBarra($entrega->contenido, $barra))->withHeaders([
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    /** Inserta la barra del visor al inicio del <body> del documento guardado. */
    public static function conBarra(string $html, string $barra): string
    {
        $posicion = stripos($html, '<body>');

        return $posicion === false
            ? $barra . $html
            : substr_replace($html, '<body>' . $barra, $posicion, strlen('<body>'));
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string} nombre, correo y, si no se puede entregar, por qué
     */
    private function destinatario(Institucion $institucion, string $tipo): array
    {
        if ($tipo === 'guardia') {
            $email = config('atulado.guardia.email');

            return [config('atulado.guardia.nombre'), $email, $email ? null
                : 'No hay correo de la guardia clínica configurado (ATULADO_GUARDIA_EMAIL).'];
        }

        $nombre = $institucion->profesional_nombre;
        $problema = match (true) {
            !$nombre => 'La institución no tiene profesional designado.',
            !$institucion->profesional_email => "Falta el correo de {$nombre}. Agrégalo en «Editar empresa».",
            !$institucion->profesional_nda_hasta || $institucion->profesional_nda_hasta->isPast() =>
                "El acuerdo de confidencialidad de {$nombre} no está vigente. Actualiza su vigencia en «Editar empresa» antes de entregarle información.",
            default => null,
        };

        return [$nombre, $institucion->profesional_email, $problema];
    }

    private function volver(Institucion $institucion)
    {
        return redirect()->to(route('admin.instituciones.show', $institucion) . '#colaboradores');
    }
}
