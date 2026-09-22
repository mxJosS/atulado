<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'panel' => \App\Http\Middleware\EnsureUserCanUsePanel::class,
            'professional' => \App\Http\Middleware\EnsureUserIsProfessional::class,
            'verified.code' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Aviso por correo de los errores reales (no 404, validación, sesión vencida…).
        // El registro normal en storage/logs sigue igual.
        $exceptions->report(fn (\Throwable $e) => \App\Support\AvisoErrores::avisar($e));

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
