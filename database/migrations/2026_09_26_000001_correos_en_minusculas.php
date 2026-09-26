<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Los correos dejan de distinguir mayúsculas: se guardan siempre en
 * minúsculas (middleware NormalizarCorreos y mutador de User).
 *
 * Aquí se pasan a minúsculas los que ya existen. Si dos cuentas sólo se
 * distinguen por mayúsculas («Ana@x.com» y «ana@x.com») no se tocan: fusionarlas
 * es decisión de administración, así que quedan anotadas en el log.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usuarios = DB::table('users')->get(['id', 'email']);
        $grupos = $usuarios->groupBy(fn ($u) => mb_strtolower(trim((string) $u->email)));

        foreach ($grupos as $correo => $cuentas) {
            if ($cuentas->count() > 1) {
                Log::warning("Correos duplicados por mayúsculas, sin cambiar: {$correo} (ids " . $cuentas->pluck('id')->implode(', ') . ').');
                continue;
            }

            $cuenta = $cuentas->first();
            if ($cuenta->email !== $correo) {
                DB::table('users')->where('id', $cuenta->id)->update(['email' => $correo]);
            }
        }

        // Los enlaces de recuperación pendientes se guardaron con el correo tal cual.
        DB::table('password_reset_tokens')->delete();

        $this->minusculas('instituciones', ['contacto_email', 'profesional_email']);
        $this->minusculas('departamentos', ['responsable_email']);
    }

    private function minusculas(string $tabla, array $columnas): void
    {
        if (!Schema::hasTable($tabla)) {
            return;
        }

        foreach (DB::table($tabla)->get(array_merge(['id'], $columnas)) as $fila) {
            $cambios = [];
            foreach ($columnas as $columna) {
                $valor = $fila->{$columna};
                if (is_string($valor) && $valor !== mb_strtolower(trim($valor))) {
                    $cambios[$columna] = mb_strtolower(trim($valor));
                }
            }
            if ($cambios !== []) {
                DB::table($tabla)->where('id', $fila->id)->update($cambios);
            }
        }
    }

    public function down(): void
    {
        // Sin reversa: no se conserva cómo estaba escrito cada correo.
    }
};
