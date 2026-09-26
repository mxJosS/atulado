<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cifrado en reposo del texto más sensible: lo que la persona escribe en su
 * registro diario, su plan de seguridad y sus contactos de emergencia. Si
 * alguien se lleva la base de datos, no puede leerlos sin el APP_KEY.
 *
 * IMPORTANTE: con este cambio, perder el APP_KEY del .env es perder estos
 * datos. Respáldalo aparte (ver deploy/respaldo.sh). Para rotarlo, pon la
 * llave vieja en APP_PREVIOUS_KEYS.
 *
 * Las columnas pasan a TEXT (el texto cifrado es más largo y no es JSON) y
 * se cifra lo que ya existe. Correrla dos veces no cifra doble.
 */
return new class extends Migration
{
    private const COLUMNAS = [
        'mood_logs' => ['journal_entry', 'gratitude_note', 'terminos_detectados'],
        'safety_plans' => [
            'warning_signs', 'internal_coping', 'distraction_activities', 'social_distractions', 'safe_places',
            'trusted_contacts', 'professional_contacts', 'safe_environment_steps', 'reasons_for_living', 'reasons_to_live',
        ],
        'contactos_emergencia' => ['nombre', 'telefono', 'relacion'],
        'users' => ['crisis_contact_name', 'crisis_contact_phone'],
    ];

    /** Columnas que deben aceptar null aunque antes no lo hicieran. */
    private const OBLIGATORIAS = ['contactos_emergencia' => ['nombre', 'telefono']];

    public function up(): void
    {
        foreach (self::COLUMNAS as $tabla => $columnas) {
            $columnas = array_values(array_filter($columnas, fn ($c) => Schema::hasColumn($tabla, $c)));
            if ($columnas === []) {
                continue;
            }

            // En SQLite los tipos no limitan la longitud (y cambiar una columna
            // reconstruye la tabla: con `users` dispararía los borrados en cascada),
            // así que el cambio a TEXT sólo se hace en MySQL/MariaDB/PostgreSQL.
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table($tabla, function (Blueprint $t) use ($tabla, $columnas) {
                    foreach ($columnas as $c) {
                        $columna = $t->text($c);
                        if (!in_array($c, self::OBLIGATORIAS[$tabla] ?? [], true)) {
                            $columna->nullable();
                        }
                        $columna->change();
                    }
                });
            }

            $this->transformar($tabla, $columnas, function (string $valor): ?string {
                return $this->estaCifrado($valor) ? null : Crypt::encryptString($valor);
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNAS as $tabla => $columnas) {
            $columnas = array_values(array_filter($columnas, fn ($c) => Schema::hasColumn($tabla, $c)));
            $this->transformar($tabla, $columnas, function (string $valor): ?string {
                return $this->estaCifrado($valor) ? Crypt::decryptString($valor) : null;
            });
        }
    }

    /** Aplica $cambio a cada valor no nulo; si devuelve null, lo deja igual. */
    private function transformar(string $tabla, array $columnas, callable $cambio): void
    {
        if ($columnas === []) {
            return;
        }

        DB::table($tabla)->select(array_merge(['id'], $columnas))->orderBy('id')
            ->chunkById(500, function ($filas) use ($tabla, $columnas, $cambio) {
                foreach ($filas as $fila) {
                    $nuevos = [];
                    foreach ($columnas as $c) {
                        if ($fila->{$c} !== null && $fila->{$c} !== '' && ($nuevo = $cambio((string) $fila->{$c})) !== null) {
                            $nuevos[$c] = $nuevo;
                        }
                    }
                    if ($nuevos !== []) {
                        DB::table($tabla)->where('id', $fila->id)->update($nuevos);
                    }
                }
            });
    }

    private function estaCifrado(string $valor): bool
    {
        try {
            Crypt::decryptString($valor);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
