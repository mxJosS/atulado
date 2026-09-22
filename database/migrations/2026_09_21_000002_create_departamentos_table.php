<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();

            // Segundo nivel: un departamento puede colgar de otro (macro-grupo → área)
            $table->foreignId('parent_id')->nullable()->constrained('departamentos')->nullOnDelete();

            $table->string('nombre');
            $table->string('clave'); // slug, único dentro de la institución
            $table->enum('turno_predominante', ['matutino', 'vespertino', 'nocturno', 'mixto'])->nullable();
            $table->string('responsable_nombre')->nullable();
            $table->string('responsable_email')->nullable();
            $table->unsignedInteger('personas_esperadas')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['institucion_id', 'clave']);
            $table->index(['institucion_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
