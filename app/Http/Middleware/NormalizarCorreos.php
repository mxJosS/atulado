<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Los correos no distinguen mayúsculas: «Ana@Correo.com» y «ana@correo.com»
 * son la misma cuenta. Se pasan a minúsculas antes de validar, así el login,
 * el registro (y su «ya está registrado»), la recuperación de contraseña, el
 * perfil, la administración de usuarios, el padrón y las instituciones
 * comparan y guardan siempre la misma forma.
 */
class NormalizarCorreos extends TransformsRequest
{
    private const CAMPOS = ['email', 'correo', 'contacto_email', 'profesional_email', 'responsable_email'];

    protected function transform($key, $value)
    {
        $campo = substr((string) strrchr('.' . $key, '.'), 1);

        if (!is_string($value) || !in_array($campo, self::CAMPOS, true)) {
            return $value;
        }

        return mb_strtolower(trim($value));
    }
}
