<?php

namespace App\Http\Requests;

use App\Models\Institucion;
use App\Services\EstructuraInstitucionalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de una institución. La ruta de edición trae
 * {institucion}; la de alta no, y ahí las macro-áreas son obligatorias.
 */
class GuardarInstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    private function institucion(): ?Institucion
    {
        $institucion = $this->route('institucion');

        return $institucion instanceof Institucion ? $institucion : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rfc' => $this->filled('rfc')
                ? Str::upper(preg_replace('/[\s-]+/', '', (string) $this->input('rfc')))
                : null,
            'contacto_email' => $this->filled('contacto_email')
                ? Str::lower(trim((string) $this->input('contacto_email')))
                : null,
        ]);
    }

    public function rules(): array
    {
        $esAlta = $this->institucion() === null;

        return [
            // 1. Organización
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_corto' => ['required', 'string', 'max:80'],
            'sector' => ['required', 'string', 'max:150'],
            'rfc' => [
                'nullable',
                'string',
                'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/u',
                Rule::unique('instituciones', 'rfc')->ignore($this->institucion()?->id),
            ],
            'ciudad' => ['nullable', 'string', 'max:150'],

            // 2. Contacto administrativo
            'contacto_nombre' => ['required', 'string', 'max:255'],
            'contacto_puesto' => ['nullable', 'string', 'max:150'],
            'contacto_email' => ['required', 'email', 'max:255'],
            'contacto_telefono' => ['nullable', 'string', 'max:50'],

            // 3. Profesional clínico designado
            'profesional_nombre' => ['required', 'string', 'max:255'],
            'profesional_cedula' => ['nullable', 'string', 'max:30'],
            'profesional_email' => ['nullable', 'email', 'max:255'],
            'profesional_nda_hasta' => ['nullable', 'date'],

            // 4. Contrato y estructura
            'plan' => ['required', 'string', 'max:100'],
            'padron_estimado' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'vigencia_fin' => ['nullable', 'date'],
            'umbral_anonimato' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'meta_adopcion' => ['nullable', 'integer', 'min:1', 'max:100'],
            'macro_areas' => [$esAlta ? 'required' : 'nullable', 'string', 'max:2000'],
            'areas' => ['nullable', 'array'],
            'areas.*.nombre' => ['nullable', 'string', 'max:150'],
            'areas.*.nuevas' => ['nullable', 'string', 'max:2000'],
            'areas.*.quitar' => ['nullable', 'boolean'],
            'nuevas_macro' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->institucion() === null
                    && EstructuraInstitucionalService::parsearLista($this->input('macro_areas')) === []) {
                    $validator->errors()->add('macro_areas', 'Indica al menos una macro-área.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El :attribute no es un correo válido.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El :attribute debe ser un número entero.',
            'min' => 'El :attribute no puede ser negativo.',
            'max.string' => 'El campo :attribute admite como máximo :max caracteres.',
            'max.numeric' => 'El :attribute no puede ser mayor que :max.',
            'date' => 'La :attribute no es una fecha válida.',
            'rfc.regex' => 'El RFC no tiene un formato válido (12 o 13 caracteres, ej. CMA210415H21).',
            'rfc.unique' => 'Ya hay otra institución registrada con ese RFC.',
        ];
    }

    public function attributes(): array
    {
        return [
            'razon_social' => 'razón social',
            'nombre_corto' => 'nombre corto',
            'sector' => 'sector / giro',
            'rfc' => 'RFC',
            'ciudad' => 'ciudad',
            'contacto_nombre' => 'nombre del contacto',
            'contacto_puesto' => 'cargo o puesto',
            'contacto_email' => 'correo electrónico',
            'contacto_telefono' => 'teléfono de enlace',
            'profesional_nombre' => 'nombre del psicólogo / especialista',
            'profesional_cedula' => 'cédula profesional',
            'profesional_email' => 'correo del profesional',
            'profesional_nda_hasta' => 'vigencia del NDA',
            'umbral_anonimato' => 'mínimo de personas por reporte',
            'meta_adopcion' => 'meta de cuentas activadas',
            'plan' => 'plan institucional',
            'padron_estimado' => 'padrón estimado',
            'vigencia_fin' => 'fecha de renovación',
            'macro_areas' => 'macro-áreas operativas',
            'areas.*.nombre' => 'nombre del área',
            'nuevas_macro' => 'macro-áreas nuevas',
        ];
    }

    /**
     * Sólo los campos de la tabla instituciones, ya normalizados.
     *
     * @return array<string,mixed>
     */
    public function datosInstitucion(): array
    {
        $datos = $this->safe()->only([
            'razon_social', 'nombre_corto', 'sector', 'rfc', 'ciudad',
            'contacto_nombre', 'contacto_puesto', 'contacto_email', 'contacto_telefono',
            'profesional_nombre', 'profesional_cedula', 'profesional_email', 'profesional_nda_hasta',
            'plan', 'padron_estimado', 'vigencia_fin', 'umbral_anonimato', 'meta_adopcion',
        ]);

        $datos['padron_estimado'] = (int) ($datos['padron_estimado'] ?? 0);
        $datos['vigencia_fin'] = $datos['vigencia_fin'] ?? null;
        // Sólo si vienen en el formulario: una actualización parcial no los borra.
        if (array_key_exists('profesional_email', $datos)) {
            $datos['profesional_email'] = strtolower(trim((string) $datos['profesional_email'])) ?: null;
        }
        if (array_key_exists('meta_adopcion', $datos)) {
            $datos['meta_adopcion'] = min(100, max(1, (int) ($datos['meta_adopcion'] ?? 75)));
        }
        if (array_key_exists('umbral_anonimato', $datos)) {
            $datos['umbral_anonimato'] = max(1, (int) ($datos['umbral_anonimato'] ?? 1));
        }

        foreach (['razon_social', 'nombre_corto', 'ciudad', 'contacto_nombre', 'contacto_puesto',
            'contacto_telefono', 'profesional_nombre', 'profesional_cedula'] as $campo) {
            if (isset($datos[$campo])) {
                $datos[$campo] = trim((string) $datos[$campo]) ?: null;
            }
        }

        return $datos;
    }
}
