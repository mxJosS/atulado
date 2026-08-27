<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modificar mood_logs con valor invertido y banderas léxicas
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->tinyInteger('valor_invertido')->nullable()->after('score'); // 0=Excelente, 1=Bien, 2=Regular, 3=Mal, 4=Terrible
            $table->boolean('bandera_lexica')->default(false)->after('journal_entry');
            $table->json('terminos_detectados')->nullable()->after('bandera_lexica');
        });

        // 2. series_vigilancia
        Schema::create('series_vigilancia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->float('base30')->nullable();
            $table->float('movil7')->nullable();
            $table->unsignedSmallInteger('dias_silencio')->default(0);
            $table->string('ultima_senal')->nullable(); // 'R1_DESVIACION', 'R2_PERSISTENCIA', 'R3_CAIDA', 'R4_SILENCIO'
            $table->date('fecha');
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 3. aplicaciones_who5
        Schema::create('aplicaciones_who5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->tinyInteger('i1');
            $table->tinyInteger('i2');
            $table->tinyInteger('i3');
            $table->tinyInteger('i4');
            $table->tinyInteger('i5');
            $table->tinyInteger('crudo'); // Suma de 5 ítems: 0 a 25
            $table->tinyInteger('escala'); // crudo * 4: 0 a 100
            $table->string('origen')->default('programada'); // 'programada', 'adelantada', 'ruta_b'
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 4. aplicaciones_mdi
        Schema::create('aplicaciones_mdi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->tinyInteger('i1');
            $table->tinyInteger('i2');
            $table->tinyInteger('i3');
            $table->tinyInteger('i4');
            $table->tinyInteger('i5');
            $table->tinyInteger('i6'); // Ideación
            $table->tinyInteger('i7');
            $table->tinyInteger('i8a');
            $table->tinyInteger('i8b');
            $table->tinyInteger('i9');
            $table->tinyInteger('i10a');
            $table->tinyInteger('i10b');
            $table->tinyInteger('total'); // Suma de 10 ítems (max en 8 y 10): 0 a 50
            $table->string('nivel'); // 'AMARILLO', 'NARANJA', 'ROJO'
            $table->string('origen')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 5. aplicaciones_asq
        Schema::create('aplicaciones_asq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->string('p1'); // 'si', 'no', 'prefiero_no_contestar'
            $table->string('p2');
            $table->string('p3');
            $table->string('p4');
            $table->string('p5')->nullable(); // Solo formulada si p1..p4 alguna es 'si' o 'prefiero_no_contestar'
            $table->text('metodo')->nullable();
            $table->string('fecha_intento')->nullable();
            $table->string('resultado'); // 'NEGATIVA', 'POSITIVA_NO_AGUDA', 'POSITIVA_AGUDA'
            $table->string('nivel')->nullable(); // 'ROJO', 'ROJO_AGUDO', null
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 6. aplicaciones_puchol
        Schema::create('aplicaciones_puchol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->integer('fisio')->default(0);
            $table->integer('cogn')->default(0);
            $table->integer('motora')->default(0);
            $table->integer('emoc')->default(0);
            $table->json('alteradas')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 7. clasificaciones
        Schema::create('clasificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->string('nivel'); // 'VERDE', 'AMARILLO', 'NARANJA', 'ROJO', 'ROJO_AGUDO'
            $table->string('origen'); // 'who5', 'mdi', 'asq', 'manual_clinico', 'diario'
            $table->json('banderas')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });

        // 8. eventos_crisis
        Schema::create('eventos_crisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('disparado_en');
            $table->timestamp('notificado_en')->nullable();
            $table->timestamp('contactado_en')->nullable();
            $table->boolean('salida_sin_contacto')->default(false);
            $table->boolean('estoy_con_alguien')->default(false);
            $table->foreignId('cierre_verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notas_cierre')->nullable();
            $table->string('estado')->default('abierto'); // 'abierto', 'en_atencion', 'cerrado'
            $table->timestamps();

            $table->index(['user_id', 'estado']);
        });

        // 9. contactos_emergencia
        Schema::create('contactos_emergencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('telefono');
            $table->string('relacion')->nullable();
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
        });

        // 10. auditoria_clinica
        Schema::create('auditoria_clinica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('usuario_consultado_id')->constrained('users')->onDelete('cascade');
            $table->string('accion'); // 'consulta_detalle', 'elevacion_nivel', 'cierre_crisis', 'verificacion_asq'
            $table->text('detalle')->nullable();
            $table->timestamps();

            $table->index(['profesional_id', 'usuario_consultado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_clinica');
        Schema::dropIfExists('contactos_emergencia');
        Schema::dropIfExists('eventos_crisis');
        Schema::dropIfExists('clasificaciones');
        Schema::dropIfExists('aplicaciones_puchol');
        Schema::dropIfExists('aplicaciones_asq');
        Schema::dropIfExists('aplicaciones_mdi');
        Schema::dropIfExists('aplicaciones_who5');
        Schema::dropIfExists('series_vigilancia');

        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropColumn(['valor_invertido', 'bandera_lexica', 'terminos_detectados']);
        });
    }
};
