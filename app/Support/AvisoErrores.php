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
}
