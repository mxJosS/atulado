<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category'); // tip, ejercicio, reflexion, herramienta, crisis
            $table->string('color_theme')->default('sage'); // sage, sky, lav, terra, amber, dark
            $table->string('estimated_time')->nullable(); // e.g. "2 min", "5 min", "10 min"
            $table->text('summary'); // Short card description
            $table->longText('content'); // Full detailed step-by-step or guide
            $table->text('svg_icon')->nullable(); // Custom SVG or vector icon definition
            $table->boolean('is_featured')->default(false);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
