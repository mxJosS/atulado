<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Administración de sólo lectura: la cuenta principal la marca al editar
        // un administrador. Por ahora sólo se guarda; aún no bloquea acciones.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('admin_solo_lectura')->default(false)->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_solo_lectura');
        });
    }
};
