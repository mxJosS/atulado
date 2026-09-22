<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ZonaHorariaTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_runs_on_merida_time(): void
    {
        $this->assertSame('America/Merida', config('app.timezone'));
        $this->assertSame(-6 * 3600, now()->getOffset());
    }

    public function test_migration_converts_stored_utc_times_and_leaves_dates_alone(): void
    {
        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update(['created_at' => '2026-09-20 02:30:00', 'email_verified_at' => null]);
        DB::table('mood_logs')->insert([
            'user_id' => $user->id, 'score' => 4, 'primary_emotion' => 'Calma',
            'logged_date' => '2026-09-20', 'created_at' => '2026-09-20 02:30:00', 'updated_at' => '2026-09-20 02:30:00',
        ]);

        $migracion = require database_path('migrations/2026_09_22_000002_convertir_horas_utc_a_merida.php');
        $migracion->up();

        // 02:30 UTC del 20 son las 20:30 del 19 en Mérida
        $this->assertSame('2026-09-19 20:30:00', DB::table('users')->where('id', $user->id)->value('created_at'));
        $this->assertNull(DB::table('users')->where('id', $user->id)->value('email_verified_at'));
        $this->assertSame('2026-09-19 20:30:00', DB::table('mood_logs')->value('created_at'));
        $this->assertStringStartsWith('2026-09-20', (string) DB::table('mood_logs')->value('logged_date'));

        $migracion->down();
        $this->assertSame('2026-09-20 02:30:00', DB::table('users')->where('id', $user->id)->value('created_at'));
    }
}
