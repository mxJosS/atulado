<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Instituciones que atiende un profesional clínico. No es el padrón:
        // el profesional no se vuelve colaborador ni recibe invitaciones o preguntas.
        Schema::create('asignaciones_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'institucion_id']);
        });

        // Lecturas de la revista: una por visitante, por artículo y por día.
        // `visitante` es el id de la cuenta o, sin sesión, una huella anónima.
        Schema::create('lecturas_articulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('visitante', 64);
            $table->date('fecha');
            $table->timestamp('created_at')->nullable();

            $table->unique(['article_id', 'visitante', 'fecha']);
            $table->index('fecha');
        });

        Schema::table('instituciones', function (Blueprint $table) {
            // Meta contractual de cuentas activadas (%).
            $table->unsignedTinyInteger('meta_adopcion')->default(75)->after('padron_estimado');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropColumn('meta_adopcion');
        });
        Schema::dropIfExists('lecturas_articulos');
        Schema::dropIfExists('asignaciones_clinicas');
    }
};
