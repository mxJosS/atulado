<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxies de confianza
    |--------------------------------------------------------------------------
    | Sólo de estas IPs se aceptan los encabezados X-Forwarded-* (IP real del
    | visitante, https, host). Confiar en cualquiera ('*') permite que un
    | atacante invente su IP en cada intento y se salte los límites de intentos.
    |
    | Sin valor (lo normal en el servidor con Nginx + PHP-FPM en la misma
    | máquina) no se confía en ningún proxy: la IP es la de la conexión.
    | Si hay un balanceador o Cloudflare delante, pon aquí sus IPs o rangos
    | separados por comas en TRUSTED_PROXIES.
    */
    'proxies' => env('TRUSTED_PROXIES') ?: null,

];
