<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('score'); // 1 to 5 (1=Muy mal, 2=Mal, 3=Regular, 4=Bien, 5=Muy bien)
            $table->string('primary_emotion'); // e.g. Ansiedad, Tristeza, Calma, Gratitud, Estrés, Enojo, Esperanza
            $table->json('tags')->nullable(); // e.g. ["Trabajo", "Sueño", "Familia", "Salud"]
            $table->text('journal_entry')->nullable(); // Daily notes / reflection
            $table->text('gratitude_note')->nullable(); // Gratitude item or positive reflection
            $table->tinyInteger('energy_level')->default(3); // 1-5
            $table->tinyInteger('sleep_hours')->nullable();
            $table->date('logged_date');
            $table->timestamps();

            $table->index(['user_id', 'logged_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_logs');
    }
};
