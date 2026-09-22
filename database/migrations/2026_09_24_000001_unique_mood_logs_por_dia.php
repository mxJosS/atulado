<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un registro diario por persona y día.
 *
 * Con un doble clic o dos pestañas se guardaban dos filas del mismo día y la
 * serie de vigilancia contaba ese día dos veces (una R2 falsa con dos días
 * reales). Se conserva la fila más reciente de cada día.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicados = DB::table('mood_logs')
            ->select('user_id', 'logged_date', DB::raw('max(id) as conservar'))
            ->groupBy('user_id', 'logged_date')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($duplicados as $d) {
            DB::table('mood_logs')
                ->where('user_id', $d->user_id)
                ->where('logged_date', $d->logged_date)
                ->where('id', '!=', $d->conservar)
                ->delete();
        }

        // Primero el único y luego se quita el índice simple: en MySQL la llave
        // foránea de user_id necesita siempre un índice que empiece por user_id.
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->unique(['user_id', 'logged_date'], 'mood_logs_user_dia_unique');
        });

        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'logged_date']);
        });
    }

    public function down(): void
    {
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->index(['user_id', 'logged_date']);
        });

        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropUnique('mood_logs_user_dia_unique');
        });
    }
};
