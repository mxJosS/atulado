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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('usuario')->after('is_admin'); // usuario, profesional, admin
            $table->string('professional_title')->nullable()->after('role'); // e.g. Lic. en Psicología Clínica / Terapeuta DBT
            $table->string('license_number')->nullable()->after('professional_title'); // Cédula o Registro profesional
            $table->string('institution')->nullable()->after('license_number');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->text('references_list')->nullable()->after('content');
            $table->boolean('is_peer_reviewed')->default(true)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['references_list', 'is_peer_reviewed']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'professional_title', 'license_number', 'institution']);
        });
    }
};
