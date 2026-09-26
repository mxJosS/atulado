<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de administración: quién cambió permisos, eliminó cuentas o
 * instituciones, exportó el padrón, aprobó acreditaciones o reinició el
 * servidor. No guarda datos clínicos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_email')->nullable(); // queda aunque se borre la cuenta
            $table->string('accion', 60);
            $table->string('objeto_tipo', 40)->nullable();
            $table->unsignedBigInteger('objeto_id')->nullable();
            $table->text('detalle')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['accion', 'created_at']);
            $table->index(['objeto_tipo', 'objeto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_admin');
    }
};
