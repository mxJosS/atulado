<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Identificación
            $table->string('razon_social');
            $table->string('nombre_corto', 80);
            $table->string('rfc', 13)->nullable()->unique();
            $table->string('sector')->nullable(); // catálogo abierto: manufactura, hoteleria, educacion...
            $table->enum('tipo', ['empresa', 'educativa_estudiantes', 'educativa_personal', 'mixta'])->default('empresa');
            $table->string('ciudad')->nullable();
            $table->string('estado_republica')->nullable();
            $table->string('zona_horaria')->default('America/Merida');
            $table->unsignedInteger('padron_estimado')->default(0);

            // Contacto administrativo (a quién llamar; no da acceso al sistema)
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_puesto')->nullable();
            $table->string('contacto_email')->nullable();
            $table->string('contacto_telefono')->nullable();

            // Profesional clínico designado: el único que recibe el expediente
            // nominativo cuando hay un protocolo de crisis activo.
            $table->string('profesional_nombre')->nullable();
            $table->string('profesional_cedula', 30)->nullable();

            // Contrato. El plan es texto y no enum: el catálogo vive en
            // config/atulado.php y cambia más seguido que el esquema.
            $table->string('plan', 100)->default('Institucional Anual');
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin')->nullable();
            $table->enum('estado', ['onboarding', 'activa', 'por_renovar', 'suspendida', 'baja'])->default('onboarding');

            // Cómo se llama el nivel organizativo en la interfaz de esta institución
            $table->enum('etiqueta_nivel_1', ['departamento', 'salon', 'carrera', 'sucursal', 'turno'])->default('departamento');
            $table->enum('etiqueta_nivel_2', ['cuadrilla', 'sede'])->nullable();

            // Opciones de contrato
            $table->boolean('aporta_perfil_estadistico')->default(true);
            $table->boolean('guardia_nocturna')->default(false);
            $table->boolean('redondear_porcentajes')->default(true);

            // Presentación
            $table->string('color', 20)->default('#2E5D4B');
            $table->string('iniciales', 4)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};
