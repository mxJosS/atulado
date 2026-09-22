<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contexto del caso para la cola de atención.
     *
     * `nivel` evita ir a clasificaciones sólo para poder ordenar la cola.
     * `institucion_id` congela dónde ocurrió el caso: si la persona cambia
     * de institución después, el histórico no debe moverse con ella.
     */
    public function up(): void
    {
        Schema::table('eventos_crisis', function (Blueprint $table) {
            $table->foreignId('institucion_id')->nullable()->after('user_id')
                ->constrained('instituciones')->nullOnDelete();
            $table->string('nivel')->nullable()->after('institucion_id'); // ROJO | ROJO_AGUDO
            $table->string('origen')->nullable()->after('nivel');         // asq | mdi | manual
            $table->foreignId('primer_contacto_por')->nullable()->after('contactado_en')
                ->constrained('users')->nullOnDelete();

            $table->index(['institucion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('eventos_crisis', function (Blueprint $table) {
            $table->dropIndex(['institucion_id', 'estado']);
            $table->dropConstrainedForeignId('institucion_id');
            $table->dropConstrainedForeignId('primer_contacto_por');
            $table->dropColumn(['nivel', 'origen']);
        });
    }
};
