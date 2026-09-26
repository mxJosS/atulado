<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «Invitar por link»: cada institución puede tener un enlace para compartir.
 * Quien lo abre crea su cuenta (con Google o con correo) y queda en el padrón
 * de esa institución. Regenerarlo invalida el anterior.
 *
 * membresias.origen distingue cómo llegó cada persona: padrón, enlace o admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('enlace_token', 64)->nullable()->unique();
            $table->boolean('enlace_activo')->default(false);
            $table->foreignId('enlace_departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->timestamp('enlace_generado_en')->nullable();
        });

        Schema::table('membresias', function (Blueprint $table) {
            $table->string('origen', 20)->nullable()->after('rol_institucional');
        });
    }

    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropColumn('origen');
        });

        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enlace_departamento_id');
            $table->dropUnique(['enlace_token']);
            $table->dropColumn(['enlace_token', 'enlace_activo', 'enlace_generado_en']);
        });
    }
};
