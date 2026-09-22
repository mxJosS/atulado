<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El resumen clínico se emite con un alcance elegido (qué secciones lleva) y
 * se guarda cuántas páginas tiene, para mostrarlo en el visor y en la bitácora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas_resumen', function (Blueprint $table) {
            $table->json('alcance')->nullable()->after('motivo');
            $table->unsignedSmallInteger('paginas')->nullable()->after('alcance');
        });
    }

    public function down(): void
    {
        Schema::table('entregas_resumen', function (Blueprint $table) {
            $table->dropColumn(['alcance', 'paginas']);
        });
    }
};
