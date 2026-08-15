<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->json('warning_signs')->nullable(); // Señales de alerta
            $table->json('internal_coping')->nullable(); // Estrategias de afrontamiento internas
            $table->json('distraction_activities')->nullable(); // Lugares o actividades que distraen
            $table->json('trusted_contacts')->nullable(); // Personas de apoyo a quienes acudir [{name, phone, relationship}]
            $table->json('professional_contacts')->nullable(); // Profesionales / terapeutas / clínicas [{name, phone, note}]
            $table->text('safe_environment_steps')->nullable(); // Medidas para hacer el entorno seguro
            $table->text('reasons_for_living')->nullable(); // Razones para vivir / anclas emocionales
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_plans');
    }
};
