<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «Eliminar» una institución primero la archiva: sale de la operación y del
 * tablero, pero toda su información se sigue viendo y se puede restaurar.
 * Sólo al eliminarla desde Archivadas se borra para siempre.
 *
 * No se usa el deleted_at de SoftDeletes: con él las relaciones dejarían de
 * encontrar la institución (sus casos aparecerían como «Independiente»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->timestamp('archivada_en')->nullable()->after('estado');
            $table->foreignId('archivada_por')->nullable()->after('archivada_en')
                ->constrained('users')->nullOnDelete();
            $table->index('archivada_en');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropIndex(['archivada_en']);
            $table->dropConstrainedForeignId('archivada_por');
            $table->dropColumn('archivada_en');
        });
    }
};
