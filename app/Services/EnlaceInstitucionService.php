<?php

namespace App\Services;

use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * «Invitar por link»: un enlace por institución que cualquiera puede abrir
 * para crear su cuenta (con Google o con correo) y quedar en su padrón.
 *
 * Al abrir el enlace, la institución se recuerda en la sesión unas horas; la
 * unión ocurre en cuanto la persona entra por cualquier vía (registro,
 * contraseña o Google), desde el evento Login.
 */
class EnlaceInstitucionService
{
    private const SESION = 'union_enlace';

    private const HORAS_SESION = 2;

    /** Crea o reemplaza el enlace. El anterior deja de funcionar. */
    public function generar(Institucion $institucion): void
    {
        $institucion->forceFill([
            'enlace_token' => Str::random(40),
            'enlace_activo' => true,
            'enlace_generado_en' => now(),
        ])->save();
    }

    public function buscar(string $token): ?Institucion
    {
        if (strlen($token) < 20) {
            return null;
        }
        $institucion = Institucion::where('enlace_token', $token)->first();

        return $institucion?->enlaceVigente() ? $institucion : null;
    }

    public function url(Institucion $institucion): ?string
    {
        return $institucion->enlace_token ? route('unirse.mostrar', $institucion->enlace_token) : null;
    }

    /* ─────────── Sesión ─────────── */

    public function recordar(Institucion $institucion): void
    {
        session()->put(self::SESION, [
            'token' => $institucion->enlace_token,
            'expira' => now()->addHours(self::HORAS_SESION)->timestamp,
        ]);
    }

    /** La institución del enlace que esta sesión abrió, si sigue vigente. */
    public function enSesion(): ?Institucion
    {
        $datos = session()->get(self::SESION);
        if (!is_array($datos) || ($datos['expira'] ?? 0) < now()->timestamp) {
            return null;
        }

        return $this->buscar((string) ($datos['token'] ?? ''));
    }

    public function olvidar(): void
    {
        session()->forget(self::SESION);
    }

    /**
     * Llamado al iniciar sesión: si la persona venía de un enlace, la une y
     * deja un aviso. El enlace se consume aunque no se pueda unir, para que
     * no se aplique después a otra cuenta en el mismo navegador.
     */
    public function unirDesdeSesion(User $user): void
    {
        $institucion = $this->enSesion();
        if (!$institucion) {
            return;
        }
        $this->olvidar();

        $resultado = $this->unir($user, $institucion);
        session()->flash('info', self::mensaje($resultado, $institucion));
    }

    /**
     * @return 'unido'|'ya_miembro'|'personal'|'otra_institucion'|'baja'
     */
    public function unir(User $user, Institucion $institucion): string
    {
        if ($user->esPersonalDePlataforma()) {
            return 'personal';
        }

        return DB::transaction(function () use ($user, $institucion) {
            $existente = Membresia::where('institucion_id', $institucion->id)->where('user_id', $user->id)->lockForUpdate()->first();

            if ($existente?->estado === 'activo') {
                return 'ya_miembro';
            }
            if ($existente?->estado === 'baja') {
                // Si la institución la dio de baja, el enlace no la regresa: lo decide la administración.
                return 'baja';
            }
            if ($existente) {
                $existente->forceFill(['estado' => 'activo', 'activado_en' => $existente->activado_en ?? now()])->save();

                return 'unido';
            }

            // Una persona pertenece a una sola institución a la vez: su caso de crisis
            // se atribuye a ella. Cambiarla es decisión de la administración.
            $otra = Membresia::where('user_id', $user->id)->whereIn('estado', ['invitado', 'activo', 'suspendido'])->exists();
            if ($otra) {
                return 'otra_institucion';
            }

            Membresia::create([
                'institucion_id' => $institucion->id,
                'user_id' => $user->id,
                'departamento_id' => $institucion->enlace_departamento_id,
                'rol_institucional' => 'colaborador',
                'origen' => 'enlace',
                'estado' => 'activo',
                'activado_en' => now(),
            ]);

            return 'unido';
        });
    }

    public static function mensaje(string $resultado, Institucion $institucion): string
    {
        $nombre = $institucion->nombre_corto;

        return match ($resultado) {
            'unido' => "Listo: ya formas parte de {$nombre} en A tu lado.",
            'ya_miembro' => "Ya formabas parte de {$nombre}.",
            'personal' => 'Las cuentas de administración y del equipo clínico no se unen al padrón de una institución.',
            'otra_institucion' => "Tu cuenta ya pertenece a otra institución, así que no se unió a {$nombre}. Si cambiaste de organización, pide a la administración de A tu lado que te cambie.",
            'baja' => "{$nombre} te dio de baja de su padrón. Si es un error, habla con tu institución.",
            default => '',
        };
    }
}
