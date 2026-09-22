<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staging de la carga masiva por Excel.
     *
     * El archivo subido NO se conserva: puede traer columnas prohibidas
     * (CURP, RFC, NSS, domicilio). Sólo se guardan, en cargas_padron_filas,
     * las columnas permitidas ya saneadas.
     */
    public function up(): void
    {
        Schema::create('cargas_padron', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // quién la subió

            $table->string('nombre_original');
            $table->json('mapeo')->nullable(); // columna del archivo → campo de A Tu Lado

            $table->unsignedInteger('filas_total')->default(0);
            $table->unsignedInteger('filas_lista')->default(0);
            $table->unsignedInteger('filas_advertencia')->default(0);
            $table->unsignedInteger('filas_error')->default(0);
            $table->unsignedInteger('filas_duplicado')->default(0);

            $table->enum('estado', ['validada', 'ejecutando', 'completada', 'fallida', 'descartada'])
                ->default('validada');
            $table->timestamp('ejecutada_en')->nullable();
            $table->timestamps();

            $table->index(['institucion_id', 'estado']);
        });

        Schema::create('cargas_padron_filas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_id')->constrained('cargas_padron')->cascadeOnDelete();
            $table->unsignedInteger('numero_fila');

            $table->json('datos'); // sólo las 17 columnas permitidas
            $table->enum('estado', ['lista', 'advertencia', 'error', 'duplicado'])->default('lista');
            $table->json('mensajes')->nullable();

            $table->foreignId('membresia_id')->nullable()->constrained('membresias')->nullOnDelete();
            $table->timestamps();

            $table->index(['carga_id', 'estado']);
        });

        // La FK de membresias.carga_id se cierra aquí, una vez que existe cargas_padron
        Schema::table('membresias', function (Blueprint $table) {
            $table->foreign('carga_id')->references('id')->on('cargas_padron')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropForeign(['carga_id']);
        });

        Schema::dropIfExists('cargas_padron_filas');
        Schema::dropIfExists('cargas_padron');
    }
};
