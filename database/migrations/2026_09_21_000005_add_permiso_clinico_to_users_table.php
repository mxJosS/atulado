<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separa el permiso clínico del permiso administrativo.
     *
     * Un superadministrador opera la plataforma pero no ve identidades ni
     * respuestas. Ver información individual exige esta acreditación aparte,
     * y cada apertura de ficha queda sellada en auditoria_clinica.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_clinico_atulado')->default(false)->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_clinico_atulado');
        });
    }
};
