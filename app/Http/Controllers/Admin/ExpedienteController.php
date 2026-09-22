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

        $this->motor->registrarContactoHumano($caso, $request->user(), $datos['nota'] ?? null);

        return $this->volver($institucion)
            ->with('success', 'Contacto humano registrado en ' . ExpedienteClinicoService::codigoCaso($caso) . '.');
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
    public function resumen(Request $request, Institucion $institucion, Membresia $membresia)
    {
        $this->exigirClinico($request, $institucion, $membresia);

        $datos = $request->validate([
            'destinatario' => ['required', 'in:profesional,guardia'],
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
            'vigencia_horas' => ['required', 'in:24,72,168'],
        ], [
            'destinatario.required' => 'Indica a quién se entrega el resumen.',
            'motivo.required' => 'Indica el motivo de la entrega.',
        ]);

        [$nombre, $email, $problema] = $this->destinatario($institucion, $datos['destinatario']);
        if ($problema) {
            return $this->volver($institucion)->with('error', $problema);
        }

        $entrega = DB::transaction(function () use ($request, $institucion, $membresia, $datos, $nombre, $email) {
            $entrega = EntregaResumen::create([
                'folio' => 'RC-' . Str::upper(Str::random(10)), // provisional hasta tener id
                'membresia_id' => $membresia->id,
                'generado_por' => $request->user()->id,
                'destinatario_nombre' => $nombre,
                'destinatario_email' => $email,
                'motivo' => trim($datos['motivo']),
                'contenido' => '',
                'vence_en' => now()->addHours((int) $datos['vigencia_horas']),
            ]);

            $folio = 'RC-' . now()->format('Y') . '-' . str_pad((string) $membresia->id, 4, '0', STR_PAD_LEFT) . '-' . $entrega->id;

            $entrega->update([
                'folio' => $folio,
                'contenido' => view('admin.instituciones.resumen-clinico', $this->expediente->ficha($membresia) + [
                    'institucion' => $institucion,
                    'folio' => $folio,
                    'destinatario' => $nombre,
                    'motivo' => trim($datos['motivo']),
                    'generadoPor' => $request->user(),
                    'generadoEn' => now(),
                ])->render(),
            ]);

            $this->registrar($request, $membresia, 'resumen_generado', trim($datos['motivo']),
                "Resumen {$folio} enviado a {$nombre} <{$email}>; vence {$entrega->vence_en->format('d/m/Y H:i')}.");

            return $entrega;
        });

        Mail::to($email)->queue(new ResumenClinicoMail($entrega, URL::temporarySignedRoute(
            'resumen.ver', $entrega->vence_en, ['entrega' => $entrega->id]
        )));

        return $this->volver($institucion)
            ->with('success', "Resumen {$entrega->folio} enviado a {$nombre}. El enlace vence el {$entrega->vence_en->format('d/m/Y H:i')}.");
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
