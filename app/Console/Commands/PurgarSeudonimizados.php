<?php

namespace App\Console\Commands;

use App\Models\AuditoriaClinica;
use App\Models\BitacoraAdmin;
use App\Models\EventoCrisis;
use Illuminate\Console\Command;

/**
 * Cumple los plazos del aviso de privacidad: los casos de crisis de cuentas
 * eliminadas (seudonimizados) y las bitácoras se conservan 5 años y luego se
 * borran. Se programa una vez al mes (routes/console.php).
 */
class PurgarSeudonimizados extends Command
{
    protected $signature = 'atulado:purgar-seudonimizados {--anios=5 : Años que se conservan}';

    protected $description = 'Borra casos de crisis seudonimizados y bitácoras con más de 5 años';

    public function handle(): int
    {
        $limite = now()->subYears(max(1, (int) $this->option('anios')));

        $casos = EventoCrisis::whereNull('user_id')->where('disparado_en', '<', $limite)->delete();
        $clinica = AuditoriaClinica::where('created_at', '<', $limite)->delete();
        $admin = BitacoraAdmin::where('created_at', '<', $limite)->delete();

        $this->info("Eliminados: {$casos} casos seudonimizados, {$clinica} registros de bitácora clínica y {$admin} de administración anteriores al {$limite->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
