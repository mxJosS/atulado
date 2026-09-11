<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'author_name')) {
            // Unlink seeded editorial articles from any user account so their author avatar doesn't inherit the demo/logged-in user's photo
            DB::table('articles')
                ->whereIn('author_name', [
                    'Dra. Elena Vázquez',
                    'Lic. Roberto Valdés',
                    'Equipo A tu lado',
                    'Mtra. Sofía Camargo',
                ])
                ->update(['user_id' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for seeded editorial articles
    }
};
