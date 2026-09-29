<?php

namespace App\Support;

/**
 * Familia de navegador y sistema operativo a partir del User-Agent. Sólo la
 * familia: actualizar Chrome no lo convierte en «otro navegador».
 */
class Navegador
{
    /** @return array{navegador: string, sistema: string} */
    public static function detectar(?string $agente): array
    {
        $agente = (string) $agente;

        // El orden importa: Edge y Opera también dicen «Chrome»; Chrome también dice «Safari».
        $navegador = match (true) {
            str_contains($agente, 'Edg/') || str_contains($agente, 'Edge/') => 'Edge',
            str_contains($agente, 'OPR/') || str_contains($agente, 'Opera') => 'Opera',
            str_contains($agente, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($agente, 'Firefox/') || str_contains($agente, 'FxiOS') => 'Firefox',
            str_contains($agente, 'Chrome/') || str_contains($agente, 'CriOS') => 'Chrome',
            str_contains($agente, 'Safari/') => 'Safari',
            default => 'Otro navegador',
        };

        $sistema = match (true) {
            str_contains($agente, 'iPhone') || str_contains($agente, 'iPad') => 'iOS',
            str_contains($agente, 'Android') => 'Android',
            str_contains($agente, 'Windows') => 'Windows',
            str_contains($agente, 'Mac OS X') || str_contains($agente, 'Macintosh') => 'macOS',
            str_contains($agente, 'CrOS') => 'ChromeOS',
            str_contains($agente, 'Linux') => 'Linux',
            default => 'otro sistema',
        };

        return ['navegador' => $navegador, 'sistema' => $sistema];
    }

    /** «Chrome · Windows» */
    public static function nombre(?string $agente): string
    {
        $d = self::detectar($agente);

        return "{$d['navegador']} · {$d['sistema']}";
    }
}
