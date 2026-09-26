<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uso de los módulos que no dejan otro rastro (herramientas, recursos,
 * revista, plan de seguridad y botón de crisis). Alimenta «Interacciones por
 * módulo» y «Hora del día» de Analítica. El registro diario y los
 * cuestionarios se cuentan desde sus propias tablas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $table->string('modulo', 20); // herramienta | recurso | revista | plan | crisis
            $table->timestamp('ocurrido_en');

            $table->index(['institucion_id', 'ocurrido_en']);
            $table->index(['user_id', 'ocurrido_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interacciones');
    }
};
