<?php

namespace App\Console\Commands;

use App\Services\ClinicalEngineService;
use Illuminate\Console\Command;

class DetectarSilencios extends Command
{
    protected $signature = 'atulado:vigilancia-silencio';

    protected $description = 'Regla R4: marca a quien dejó de registrar tras un patrón regular (adelanta su WHO-5 al volver)';

    public function handle(ClinicalEngineService $motor): int
    {
        $marcadas = $motor->detectarSilencios();
        $this->info("R4 · personas marcadas por silencio: {$marcadas}");

        return self::SUCCESS;
    }
}
