<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
| Requieren el cron del servidor (ver deploy/crontab.txt):
|   * * * * * cd /var/www/atulado && php8.5 artisan schedule:run
*/

// Regla R4 de vigilancia: silencio tras patrón regular.
Schedule::command('atulado:vigilancia-silencio')
    ->dailyAt('06:00')
    ->timezone('America/Merida')
    ->withoutOverlapping();

// Trabajos de la cola que fallaron hace más de una semana.
Schedule::command('queue:prune-failed --hours=168')->weekly();
