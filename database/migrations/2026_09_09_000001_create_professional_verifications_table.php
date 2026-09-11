<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('professional_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Requisitos solicitados
            $table->string('full_name'); // Nombre completo
            $table->string('license_number'); // Número de cédula profesional
            $table->enum('education_level', [
                'licenciatura',
                'especialidad',
                'maestria',
                'doctorado'
            ]); // Grado escolar
            
            // Archivo probatorio digital (opcional / escaneo de cédula o título)
            $table->string('document_path')->nullable();
            
            // Estado y auditoría
            $table->enum('status', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();

            // Índices de búsqueda
            $table->index(['user_id', 'status']);
            $table->index('license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_verifications');
    }
};
