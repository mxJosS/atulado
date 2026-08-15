<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author_name')->default('Equipo A tu lado');
            $table->string('author_role')->default('Psicología y Bienestar');
            $table->string('category'); // e.g. Ansiedad, Hábitos, DBT, Duelo, Sueño, Relaciones
            $table->string('read_time')->default('4 min');
            $table->string('color_tag')->default('sage'); // sage, terra, lav, sky, amber
            $table->text('excerpt');
            $table->longText('content');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
