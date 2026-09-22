<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relación persona ↔ institución.
     *
     * Sustituye a users.institution_id / macro_group / department / shift /
     * employee_number / position, que eran columnas sueltas sin historia.
     * Una persona que deja la institución no se borra: pasa a estado 'baja'.
     */
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('carga_id')->nullable(); // FK se añade en la migración de cargas

            // Datos laborales
            $table->string('numero_empleado')->nullable();
            $table->string('puesto')->nullable();
            $table->enum('turno', ['matutino', 'vespertino', 'nocturno', 'mixto'])->nullable();
            $table->string('horario')->nullable(); // ventana para contactar en crisis, ej. 22:00-06:00
            $table->date('fecha_ingreso')->nullable();
            $table->string('tipo_jornada')->nullable();

            // Datos para estadística anónima.
            // rango_edad se deriva del año de nacimiento al importar; el año NO se guarda.
            $table->enum('sexo', ['M', 'F', 'prefiere_no_decir'])->nullable();
            $table->string('rango_edad', 10)->nullable();
            $table->string('escolaridad')->nullable();
            $table->string('lugar_origen')->nullable();
            $table->enum('idioma', ['es', 'myn'])->default('es');

            // Rol y estado
            $table->enum('rol_institucional', ['colaborador', 'rh', 'direccion'])->default('colaborador');
            $table->enum('estado', ['invitado', 'activo', 'suspendido', 'baja'])->default('invitado');
            $table->string('codigo_acceso')->nullable(); // personal sin correo institucional

            $table->timestamp('invitado_en')->nullable(); // marca de idempotencia del envío
            $table->timestamp('activado_en')->nullable();
            $table->timestamp('baja_en')->nullable();
            $table->text('baja_motivo')->nullable();

            $table->timestamps();

            $table->unique(['institucion_id', 'user_id']);
            $table->unique(['institucion_id', 'numero_empleado']);
            $table->index(['institucion_id', 'estado']);
            $table->index(['institucion_id', 'departamento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
