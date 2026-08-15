<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crisis_lines', function (Blueprint $table) {
            $table->id();
            $table->string('country'); // México, Argentina, España, Colombia, Chile, Estados Unidos, etc.
            $table->string('country_code', 4)->default('MX');
            $table->string('phone_number');
            $table->string('service_name');
            $table->string('hours')->default('24 horas / 7 días');
            $table->string('cost_type')->default('Gratuita y confidencial');
            $table->text('description');
            $table->string('whatsapp_or_chat_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crisis_lines');
    }
};
