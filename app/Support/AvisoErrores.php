<?php

namespace App\Support;

use App\Mail\AvisoErrorMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Aviso por correo cuando la app falla en producción, para enterarse antes
 * que la persona usuaria. Sin servicios externos: usa el mismo correo de la app.
 *
 * Un aviso por tipo de error (clase + archivo + línea) cada ATULADO_ALERTAS_MINUTOS.
 * El aviso nunca incluye datos de la petición (cuerpo, texto libre, respuestas):
 * sólo el error, dónde ocurrió y la URL.
 */
class AvisoErrores
{
    public static function avisar(Throwable $e): void
    {
        $destinatarios = config('atulado.alertas_errores', []);
        if ($destinatarios === [] || !app()->isProduction()) {
            return;
        }

        $firma = 'aviso-error:' . sha1(get_class($e) . '|' . $e->getFile() . '|' . $e->getLine());
        $minutos = max(1, (int) config('atulado.alertas_minutos', 30));

        // Cache::add sólo escribe si no existe: el primero avisa, los demás esperan.
        try {
            if (!Cache::add($firma, true, now()->addMinutes($minutos))) {
                return;
            }
        } catch (Throwable) {
            // Si la caché (base de datos) está caída, se avisa igual: es justo cuando más importa.
        }

        $cuerpo = implode("\n", [
            'La app A Tu Lado registró un error en producción.',
            '',
            'Error:   ' . get_class($e),
            'Mensaje: ' . mb_substr($e->getMessage(), 0, 500),
            'Lugar:   ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $e->getFile()) . ':' . $e->getLine(),
            'URL:     ' . (app()->runningInConsole() ? 'consola / tarea programada' : request()->method() . ' ' . request()->path()),
            'Cuándo:  ' . now()->format('d/m/Y H:i:s'),
            '',
            "El detalle completo está en storage/logs/laravel.log del servidor.",
            "No se repetirá este aviso para el mismo error durante {$minutos} minutos.",
        ]);

        try {
            Mail::to($destinatarios)->send(new AvisoErrorMail(class_basename($e), $cuerpo));
        } catch (Throwable) {
            // El aviso nunca debe tumbar la petición ni generar otro error.
        }
    }

    /**
     * Picos de accesos rechazados (401, 403, 419, 429): suele ser alguien
     * probando contraseñas, códigos o rutas ajenas. Cuenta por ventanas de 10
     * minutos y avisa una vez por ventana al pasar el umbral.
     */
    public static function contarRechazo(int $estado, string $ip, string $ruta): void
    {
        $umbral = (int) config('atulado.alertas_rechazos', 60);
        if ($umbral <= 0) {
            return;
        }

        $ventana = 'rechazos:' . intdiv(now()->timestamp, 600);
        try {
            Cache::add($ventana, 0, now()->addMinutes(20));
            $total = Cache::increment($ventana);
            Cache::add($ventana . ':ip:' . $ip, 0, now()->addMinutes(20));
            Cache::increment($ventana . ':ip:' . $ip);

            if ($total !== $umbral) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        \Illuminate\Support\Facades\Log::warning("Pico de accesos rechazados: {$umbral} en menos de 10 minutos (último: {$estado} {$ruta} desde {$ip}).");

        $destinatarios = config('atulado.alertas_errores', []);
        if ($destinatarios === [] || !app()->isProduction()) {
            return;
        }

        $cuerpo = implode("\n", [
            "La app A Tu Lado respondió {$umbral} accesos rechazados (401/403/419/429) en menos de 10 minutos.",
            'Puede ser alguien probando contraseñas, códigos de acceso o rutas de otras personas.',
            '',
            "Último: {$estado} {$ruta}",
            "Desde:  {$ip}",
            'Cuándo: ' . now()->format('d/m/Y H:i:s'),
            '',
            'Revisa storage/logs/laravel.log y el access.log de Nginx.',
        ]);

        try {
            Mail::to($destinatarios)->send(new AvisoErrorMail('pico de accesos rechazados', $cuerpo));
        } catch (Throwable) {
        }
    }
}
