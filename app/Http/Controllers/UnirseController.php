<?php

namespace App\Http\Controllers;

use App\Services\EnlaceInstitucionService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Página pública del enlace «Invitar por link» de una institución.
 *
 * Quien lo abre elige: continuar con Google (siempre disponible aquí, aunque
 * el acceso con Google esté apagado en el panel), crear su cuenta con correo,
 * o entrar con la cuenta que ya tiene. Al entrar queda en el padrón.
 */
class UnirseController extends Controller
{
    public function __construct(private EnlaceInstitucionService $enlaces)
    {
    }

    public function mostrar(Request $request, string $token)
    {
        $institucion = $this->enlaces->buscar($token);
        if (!$institucion) {
            return response()->view('unirse.invalido', [], 404);
        }

        // Con sesión: se une con un clic, sin pasar por la sesión del enlace.
        if (!$request->user()) {
            $this->enlaces->recordar($institucion);
        }

        return view('unirse.mostrar', [
            'institucion' => $institucion,
            'token' => $token,
            'googleConfigurado' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret')),
        ]);
    }

    /** Persona que ya tiene sesión y confirma que se une. */
    public function unir(Request $request, string $token)
    {
        $institucion = $this->enlaces->buscar($token);
        if (!$institucion) {
            return response()->view('unirse.invalido', [], 404);
        }

        $resultado = $this->enlaces->unir($request->user(), $institucion);

        return redirect()->route('dashboard')->with($resultado === 'unido' || $resultado === 'ya_miembro' ? 'success' : 'error',
            EnlaceInstitucionService::mensaje($resultado, $institucion));
    }

    /** «Continuar con Google» desde el enlace: no depende del interruptor del panel. */
    public function google(Request $request, string $token)
    {
        $institucion = $this->enlaces->buscar($token);
        if (!$institucion) {
            return response()->view('unirse.invalido', [], 404);
        }

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('unirse.mostrar', $token)->with('error', 'El acceso con Google no está configurado todavía. Crea tu cuenta con tu correo.');
        }

        $this->enlaces->recordar($institucion);

        return Socialite::driver('google')->enablePKCE()->redirect();
    }
}
