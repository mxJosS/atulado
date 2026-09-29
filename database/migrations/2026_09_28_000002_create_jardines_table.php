<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El jardín del juego del árbol (niveles de cada planta, soles, tareas del día
 * e inventario). Antes vivía sólo en el navegador y se perdía al cambiar de
 * dispositivo; ahora sigue a la cuenta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jardines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('estado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jardines');
    }
};
