<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // A dónde se manda el resumen clínico y hasta cuándo puede recibirlo.
            $table->string('profesional_email')->nullable()->after('profesional_cedula');
            $table->date('profesional_nda_hasta')->nullable()->after('profesional_email');

            // Mínimo de personas por corte en los reportes agregados de esta institución.
            $table->unsignedSmallInteger('umbral_anonimato')->default(1)->after('redondear_porcentajes');
        });

        // Resúmenes clínicos entregados por enlace. El contenido se guarda tal como
        // se generó: el destinatario ve la foto de ese momento, no datos posteriores.
        Schema::create('entregas_resumen', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('membresia_id')->constrained('membresias')->cascadeOnDelete();
            $table->foreignId('generado_por')->constrained('users')->cascadeOnDelete();
            $table->string('destinatario_nombre');
            $table->string('destinatario_email');
            $table->text('motivo');
            $table->longText('contenido');
            $table->timestamp('vence_en');
            $table->timestamp('abierto_en')->nullable();
            $table->unsignedInteger('aperturas')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas_resumen');

        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropColumn(['profesional_email', 'profesional_nda_hasta', 'umbral_anonimato']);
        });
    }
};
