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
        Schema::table('professional_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('professional_verifications', 'specialty')) {
                $table->string('specialty', 150)->nullable()->after('education_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('professional_verifications', 'specialty')) {
                $table->dropColumn('specialty');
            }
        });
    }
};
