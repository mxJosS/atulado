<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('safety_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('safety_plans', 'social_distractions')) {
                $table->json('social_distractions')->nullable()->after('internal_coping');
            }
            if (!Schema::hasColumn('safety_plans', 'safe_places')) {
                $table->json('safe_places')->nullable()->after('social_distractions');
            }
            if (!Schema::hasColumn('safety_plans', 'reasons_to_live')) {
                $table->json('reasons_to_live')->nullable()->after('safe_environment_steps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('safety_plans', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('safety_plans', 'social_distractions')) {
                $columnsToDrop[] = 'social_distractions';
            }
            if (Schema::hasColumn('safety_plans', 'safe_places')) {
                $columnsToDrop[] = 'safe_places';
            }
            if (Schema::hasColumn('safety_plans', 'reasons_to_live')) {
                $columnsToDrop[] = 'reasons_to_live';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
