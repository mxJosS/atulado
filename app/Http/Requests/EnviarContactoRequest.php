<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Solicitud pública de «Contacto»: una empresa o institución que quiere una
 * prueba piloto, un plan u otra cosa. Los intereses, los sectores y los
 * tamaños salen de config/atulado.php, las mismas listas que pinta el
 * formulario. «sitio_web» es la trampa para bots y no se valida aquí.
 */
class EnviarContactoRequest extends FormRequest
{
    private const CAMPOS = ['interes', 'nombre', 'puesto', 'organizacion', 'sector', 'personas', 'email', 'telefono', 'mensaje'];

    /**
     * El correo llega al buzón corporativo con «Responder a» de quien lo envía:
     * un intento de phishing traía texto en otros alfabetos y enlaces. Sólo
     * letras del español (latín básico y acentos) y nada que parezca dirección web.
     */
    private const SOLO_ESPANOL = 'not_regex:/[^\P{L}\x{00}-\x{FF}]/u';
    private const SIN_ENLACES = 'not_regex:/(:\/\/|www\.|[\p{L}\d-]\.[a-z]{2,}\b)/iu';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interes' => ['required', Rule::in(array_keys(config('atulado.contacto.intereses')))],
            'nombre' => ['required', 'string', 'max:120', self::SOLO_ESPANOL, self::SIN_ENLACES],
            'puesto' => ['nullable', 'string', 'max:120', self::SOLO_ESPANOL, self::SIN_ENLACES],
            'organizacion' => ['required', 'string', 'max:160', self::SOLO_ESPANOL, self::SIN_ENLACES],
            'sector' => ['nullable', Rule::in(array_merge(config('atulado.sectores'), ['Otro']))],
            'personas' => ['nullable', Rule::in(config('atulado.contacto.personas'))],
            'email' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\s.\-]{7,30}$/'],
            'mensaje' => ['nullable', 'string', 'max:2000', self::SOLO_ESPANOL, self::SIN_ENLACES],
            'acepto_aviso' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute admite como máximo :max caracteres.',
            'in' => 'Elige una opción válida de :attribute.',
            'not_regex' => 'El campo :attribute sólo admite texto en español, sin enlaces ni direcciones web.',
            'interes.required' => 'Elige qué te interesa: prueba piloto, plan u otra consulta.',
            'email.email' => 'Escribe un correo válido, por ejemplo nombre@empresa.com.',
            'telefono.regex' => 'Escribe el teléfono sólo con números; puede llevar +, espacios o guiones.',
            'acepto_aviso.accepted' => 'Para enviar tu solicitud, acepta el aviso de privacidad.',
        ];
    }

    public function attributes(): array
    {
        return [
            'interes' => 'interés',
            'nombre' => 'nombre',
            'puesto' => 'puesto',
            'organizacion' => 'organización',
            'sector' => 'sector',
            'personas' => 'número de personas',
            'email' => 'correo electrónico',
            'telefono' => 'teléfono',
            'mensaje' => 'mensaje',
        ];
    }

    /**
     * Un bot llena el campo oculto «sitio_web» o envía el formulario en menos
     * de 3 segundos («cargado» es la hora cifrada en que se mostró la página).
     */
    public function pareceBot(): bool
    {
        if ($this->filled('sitio_web')) {
            return true;
        }

        try {
            return now()->timestamp - (int) decrypt((string) $this->input('cargado')) < 3;
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Todos los campos del correo (los opcionales vacíos en null) y la
     * etiqueta legible del interés. Los de una línea quedan en una sola
     * línea: varios van al asunto y a «Responder a».
     *
     * @return array<string,string|null>
     */
    public function datosContacto(): array
    {
        $validados = $this->validated();
        $datos = [];

        foreach (self::CAMPOS as $campo) {
            $valor = trim((string) ($validados[$campo] ?? ''));
            if ($campo !== 'mensaje') {
                $valor = preg_replace('/\s+/u', ' ', $valor);
            }
            $datos[$campo] = $valor === '' ? null : $valor;
        }

        $datos['interes_texto'] = config('atulado.contacto.intereses')[$datos['interes']];

        return $datos;
    }
}
