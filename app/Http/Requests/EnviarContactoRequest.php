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

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interes' => ['required', Rule::in(array_keys(config('atulado.contacto.intereses')))],
            'nombre' => ['required', 'string', 'max:120'],
            'puesto' => ['nullable', 'string', 'max:120'],
            'organizacion' => ['required', 'string', 'max:160'],
            'sector' => ['nullable', Rule::in(array_merge(config('atulado.sectores'), ['Otro']))],
            'personas' => ['nullable', Rule::in(config('atulado.contacto.personas'))],
            'email' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\s.\-]{7,30}$/'],
            'mensaje' => ['nullable', 'string', 'max:2000'],
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
