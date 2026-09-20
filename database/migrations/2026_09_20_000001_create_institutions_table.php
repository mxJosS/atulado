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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('category');
            $table->string('city')->nullable();
            $table->string('contact_name');
            $table->string('contact_position')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('professional_name');
            $table->string('professional_license')->nullable();
            $table->string('professional_contract_status')->default('vigente');
            $table->string('plan')->default('Institucional Anual');
            $table->unsignedInteger('users_count')->default(0);
            $table->unsignedInteger('active_count')->default(0);
            $table->decimal('adoption_rate', 5, 2)->default(0.00);
            $table->string('alert_level')->default('verde');
            $table->unsignedInteger('critical_count')->default(0);
            $table->date('renewal_date')->nullable();
            $table->json('departments_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
