<?php

namespace App\Models;

use App\Support\Navegador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Un dispositivo donde administración o el equipo clínico ya confirmaron el
 * código por correo. El código se vuelve a pedir si cambia la IP, el
 * navegador o el dispositivo (otra cookie), o tras 90 días sin usarlo.
 */
class DispositivoConfiable extends Model
{
    protected $table = 'dispositivos_confiables';

    public const UPDATED_AT = null;

    public const COOKIE = 'atulado_dispositivo';

    public const DIAS_VIGENCIA = 90;

    protected $fillable = ['user_id', 'token_hash', 'navegador', 'firma_hash', 'ip', 'ultimo_uso_en'];

    protected function casts(): array
    {
        return ['ultimo_uso_en' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** El dispositivo de esta petición, si la cuenta ya lo había confirmado. */
    public static function reconoce(User $user, Request $request): ?self
    {
        $token = (string) $request->cookie(self::COOKIE);
        if ($token === '') {
            return null;
        }

        return self::where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('firma_hash', self::firma($request))
            ->where('ip', $request->ip())
            ->where('ultimo_uso_en', '>=', now()->subDays(self::DIAS_VIGENCIA))
            ->first();
    }

    /**
     * Marca el dispositivo de esta petición como confiable y devuelve el token
     * que va en la cookie. Si ya tenía cookie, se renueva ese mismo registro.
     */
    public static function confiar(User $user, Request $request): string
    {
        $token = Str::random(64);
        $anterior = (string) $request->cookie(self::COOKIE);

        if ($anterior !== '') {
            self::where('user_id', $user->id)->where('token_hash', hash('sha256', $anterior))->delete();
        }

        self::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'navegador' => Navegador::nombre($request->userAgent()),
            'firma_hash' => self::firma($request),
            'ip' => (string) $request->ip(),
            'ultimo_uso_en' => now(),
        ]);

        return $token;
    }

    /** Olvida todos los dispositivos de la cuenta (p. ej. al cambiar la contraseña). */
    public static function olvidarTodos(User $user): void
    {
        self::where('user_id', $user->id)->delete();
    }

    private static function firma(Request $request): string
    {
        return hash('sha256', Navegador::nombre($request->userAgent()));
    }
}
