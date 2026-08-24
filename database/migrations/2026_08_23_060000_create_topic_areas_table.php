<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable()->default('fa-brain');
            $table->string('color', 50)->nullable()->default('sage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_areas');
    }
};
