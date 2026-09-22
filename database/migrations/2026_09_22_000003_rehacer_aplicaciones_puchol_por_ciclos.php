<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El Puchol se aplica como test breve del estado de ánimo de 22 ítems en
     * cuatro secciones (ansiedad, ansiedad física, depresión, impulsos
     * suicidas), repartido en bloques cortos a lo largo de un ciclo de 30 días.
     *
     * Cada fila es un ciclo. Las columnas anteriores (fisio, cogn, motora,
     * emoc) correspondían a otro instrumento y ningún código las escribía.
     */
    public function up(): void
    {
        // Si alguien llegó a escribir algo en el esquema anterior, no se pierde en silencio.
        if (DB::table('aplicaciones_puchol')->exists()) {
            throw new RuntimeException('aplicaciones_puchol tiene datos del esquema anterior: respáldalos antes de migrar.');
        }

        Schema::table('aplicaciones_puchol', function (Blueprint $table) {
            $table->dropColumn(['fisio', 'cogn', 'motora', 'emoc', 'alteradas']);
        });

        Schema::table('aplicaciones_puchol', function (Blueprint $table) {
            // `fecha` (ya existente) = inicio del ciclo
            $table->string('estado', 12)->default('en_curso')->after('fecha');   // en_curso · completado · abandonado
            $table->string('origen', 12)->default('periodico')->after('estado'); // periodico · adelantado
            $table->date('disponible_desde')->nullable()->after('origen');

            $table->json('respuestas')->nullable();  // a1..a5, f1..f10, d1..d5, s1..s2 (0 a 4)
            $table->json('bloques')->nullable();     // bloques ya respondidos: A, B, C, D

            $table->unsignedTinyInteger('ansiedad')->nullable();  // 0–20
            $table->unsignedTinyInteger('fisica')->nullable();    // 0–40
            $table->unsignedTinyInteger('depresion')->nullable(); // 0–20
            $table->unsignedTinyInteger('suicidas')->nullable();  // 0–8

            // Control de la carga: un bloque al día como máximo y hasta 3 ofrecimientos sin respuesta.
            $table->string('ofrecido_pendiente', 1)->nullable();
            $table->date('ofrecido_en')->nullable();
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->date('ultima_respuesta')->nullable();
            $table->timestamp('completado_en')->nullable();

            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('aplicaciones_puchol', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'estado']);
            $table->dropColumn([
                'estado', 'origen', 'disponible_desde', 'respuestas', 'bloques',
                'ansiedad', 'fisica', 'depresion', 'suicidas',
                'ofrecido_pendiente', 'ofrecido_en', 'intentos', 'ultima_respuesta', 'completado_en',
            ]);
        });

        Schema::table('aplicaciones_puchol', function (Blueprint $table) {
            $table->integer('fisio')->default(0);
            $table->integer('cogn')->default(0);
            $table->integer('motora')->default(0);
            $table->integer('emoc')->default(0);
            $table->json('alteradas')->nullable();
        });
    }
};
