<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes de la plataforma que la administración cambia en caliente desde el
 * panel (sin tocar el .env ni desplegar). El primero: activar o desactivar el
 * acceso con Google mientras se registra un salón con correo institucional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
