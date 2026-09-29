<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // «Invitar por link» por institución: la administración decide en cuáles
        // de las instituciones que atiende un profesional clínico puede compartir el link.
        Schema::table('asignaciones_clinicas', function (Blueprint $table) {
            $table->boolean('puede_invitar_por_link')->default(false)->after('institucion_id');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_clinicas', function (Blueprint $table) {
            $table->dropColumn('puede_invitar_por_link');
        });
    }
};
