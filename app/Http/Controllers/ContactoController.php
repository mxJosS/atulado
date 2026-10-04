<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnviarContactoRequest;
use App\Mail\ContactoCorporativoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * «Contacto»: la página pública para empresas e instituciones que quieren
 * una prueba piloto o contratar un plan. Cada solicitud llega por correo al
 * buzón corporativo (atulado.contacto.correo) con «Responder a» de quien la
 * envía; no se guarda en la base de datos.
 */
class ContactoController extends Controller
{
    public function mostrar(Request $request)
    {
        $intereses = config('atulado.contacto.intereses');
        // Los botones de Inicio llegan con ?interes=piloto o ?interes=plan ya elegido.
        $pedido = $request->query('interes');

        return view('contacto', [
            'correo' => config('atulado.contacto.correo'),
            'intereses' => $intereses,
            'interesElegido' => old('interes', is_string($pedido) && array_key_exists($pedido, $intereses) ? $pedido : 'piloto'),
            'sectores' => config('atulado.sectores'),
            'personas' => config('atulado.contacto.personas'),
        ]);
    }

    public function enviar(EnviarContactoRequest $request)
    {
        // Trampa para bots: el campo está oculto y una persona nunca lo llena.
        // Se les responde como a todos para no darles pistas.
        if ($request->filled('sitio_web')) {
            return redirect()->route('contacto')->with('contacto_enviado', true);
        }

        $datos = $request->datosContacto();
        $correo = config('atulado.contacto.correo');

        try {
            Mail::to($correo)->send(new ContactoCorporativoMail($datos));
        } catch (\Throwable $e) {
            Log::error('Error al enviar una solicitud de contacto corporativo: ' . $e->getMessage());

            return back()->withInput()->with('error', 'No pudimos enviar tu solicitud. Inténtalo de nuevo o escríbenos directo a ' . $correo . '.');
        }

        return redirect()->route('contacto')
            ->with('contacto_enviado', true)
            ->with('success', '¡Gracias, ' . $datos['nombre'] . '! Recibimos tu solicitud.');
    }
}
