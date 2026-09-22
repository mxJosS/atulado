<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La bitácora de accesos necesita saber POR QUÉ se abrió una ficha,
     * no sólo que se abrió. Sin motivo declarado no se concede el acceso.
     *
     * Todas las columnas son nullable: las filas ya existentes son válidas
     * y no se reescriben (la tabla es de sólo inserción).
     */
    public function up(): void
    {
        Schema::table('auditoria_clinica', function (Blueprint $table) {
            $table->text('motivo')->nullable()->after('accion');
            $table->foreignId('institucion_id')->nullable()->after('usuario_consultado_id')
                ->constrained('instituciones')->nullOnDelete();
            $table->string('ip', 45)->nullable()->after('detalle');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_clinica', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institucion_id');
            $table->dropColumn(['motivo', 'ip']);
        });
    }
};
