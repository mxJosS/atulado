<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El aviso de privacidad dice que los casos de crisis y la bitácora de accesos
 * se conservan 5 años, seudonimizados. Antes, borrar una cuenta los borraba
 * en cascada. Ahora pierden el vínculo con la persona (user_id = null) y se
 * quedan; la tarea atulado:purgar-seudonimizados los elimina a los 5 años.
 */
return new class extends Migration
{
    private const COLUMNAS = [
        'eventos_crisis' => ['user_id'],
        'auditoria_clinica' => ['profesional_id', 'usuario_consultado_id'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNAS as $tabla => $columnas) {
            Schema::table($tabla, function (Blueprint $t) use ($columnas) {
                foreach ($columnas as $columna) {
                    $t->dropForeign([$columna]);
                }
            });
            Schema::table($tabla, function (Blueprint $t) use ($columnas) {
                foreach ($columnas as $columna) {
                    $t->unsignedBigInteger($columna)->nullable()->change();
                    $t->foreign($columna)->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNAS as $tabla => $columnas) {
            Schema::table($tabla, function (Blueprint $t) use ($columnas) {
                foreach ($columnas as $columna) {
                    $t->dropForeign([$columna]);
                }
            });
            Schema::table($tabla, function (Blueprint $t) use ($columnas) {
                foreach ($columnas as $columna) {
                    $t->foreign($columna)->references('id')->on('users')->cascadeOnDelete();
                }
            });
        }
    }
};
