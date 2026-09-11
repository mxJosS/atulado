<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfessionalVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && !auth()->user()->isProfessional();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name'       => ['required', 'string', 'min:3', 'max:255'],
            'license_number'  => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'education_level' => ['required', 'in:licenciatura,especialidad,maestria,doctorado'],
            'document'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'], // Máximo 5MB
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'       => 'El nombre completo es obligatorio.',
            'full_name.min'            => 'El nombre debe tener al menos 3 caracteres.',
            'license_number.required'  => 'El número de cédula profesional es obligatorio.',
            'license_number.regex'     => 'El formato de cédula solo debe contener letras, números y guiones.',
            'education_level.required' => 'Selecciona tu grado escolar acreditado.',
            'education_level.in'       => 'El grado escolar seleccionado no es válido.',
            'document.mimes'           => 'El comprobante debe ser un archivo en formato PDF, JPG, PNG o WEBP.',
            'document.max'             => 'El archivo no debe exceder 5 MB de tamaño.',
        ];
    }
}
