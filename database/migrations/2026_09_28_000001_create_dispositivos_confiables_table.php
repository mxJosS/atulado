<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dispositivos donde administración o el equipo clínico ya confirmaron el
 * código por correo. Desde uno de ellos (misma cookie, mismo navegador y misma
 * IP) se entra sin volver a pedir el código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivos_confiables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique(); // sha256 del token de la cookie
            $table->string('navegador', 80);             // legible: «Chrome · Windows»
            $table->string('firma_hash', 64);            // sha256 de navegador + sistema
            $table->string('ip', 45);
            $table->timestamp('ultimo_uso_en');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'ultimo_uso_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivos_confiables');
    }
};
