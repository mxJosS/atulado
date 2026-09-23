<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cuentas de Superadministración Protegidas
    |--------------------------------------------------------------------------
    | Correos que no pueden ser eliminados, suspendidos ni cambiados de rol
    | desde el módulo de usuarios. Separados por comas en la variable de entorno.
    */
    'superadmin_emails' => array_values(array_filter(array_map(
        fn ($email) => strtolower(trim($email)),
        explode(',', (string) env('ATULADO_SUPERADMIN_EMAILS', 'admin@atulado.com.mx,fgarcia@atulado.com.mx'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Catálogos del Alta de Institución
    |--------------------------------------------------------------------------
    | Una sola lista para el formulario de alta, el de edición y la validación.
    */
    'sectores' => [
        'Construcción y Obras',
        'Automotriz y Retail',
        'Tecnología y BPO',
        'Educación y Colegios',
        'Manufactura e Industria',
        'Servicios Corporativos',
        'Salud y Hospitales',
        'Hotelería y Turismo',
    ],

    'planes' => [
        'Institucional Anual',
        'Institucional Premium',
        'Pyme Salud',
        'Comunidad Educativa',
        'Piloto 90 días',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guardia clínica de A Tu Lado
    |--------------------------------------------------------------------------
    | Destinatario alterno del resumen clínico cuando la institución no tiene un
    | profesional designado con correo y NDA vigente.
    */
    'guardia' => [
        'nombre' => env('ATULADO_GUARDIA_NOMBRE', 'Guardia clínica A Tu Lado'),
        'email' => env('ATULADO_GUARDIA_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Avisos de errores en producción
    |--------------------------------------------------------------------------
    | Correos que reciben un aviso cuando la app falla (uno por tipo de error
    | cada ATULADO_ALERTAS_MINUTOS). Separados por comas. Vacío = sin avisos.
    */
    'alertas_errores' => array_values(array_filter(array_map(
        fn ($email) => strtolower(trim($email)),
        explode(',', (string) env('ATULADO_ALERTAS_ERRORES', ''))
    ))),
    'alertas_minutos' => (int) env('ATULADO_ALERTAS_MINUTOS', 30),

    /*
    |--------------------------------------------------------------------------
    | Acceso al Plano Clínico
    |--------------------------------------------------------------------------
    | Minutos que dura la concesión tras declarar el motivo de acceso a una
    | ficha individual. Al expirar se vuelve a pedir motivo.
    */
    'ficha' => [
        'minutos_concesion' => env('ATULADO_FICHA_MINUTOS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitor del servidor
    |--------------------------------------------------------------------------
    | Panel en tiempo real (CPU, RAM, disco, red, procesos, Nginx, PHP-FPM,
    | MySQL…). Sólo existe si ATULADO_MONITOR_RUTA tiene valor, sólo se llega
    | escribiendo esa ruta exacta (no hay enlaces a ella en ninguna parte) y
    | sólo lo ve una cuenta de administración con sesión iniciada; para
    | cualquier otra cuenta responde 404. La ruta vive en el .env del
    | servidor, nunca en el repositorio.
    |
    | nginx_status y fpm_status son las páginas de estado internas (sólo
    | 127.0.0.1); ver deploy/nginx-monitor.conf.
    */
    'monitor' => [
        'ruta' => trim((string) env('ATULADO_MONITOR_RUTA', ''), '/'),
        'nginx_status' => env('ATULADO_MONITOR_NGINX', 'http://127.0.0.1:8089/nginx_status'),
        'fpm_status' => env('ATULADO_MONITOR_FPM', 'http://127.0.0.1:8089/fpm-status?json'),
    ],
];
