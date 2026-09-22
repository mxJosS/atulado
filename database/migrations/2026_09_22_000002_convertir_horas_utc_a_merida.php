<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La app pasó de UTC a America/Merida (config/app.php).
     *
     * Laravel guarda las horas sin zona: lo escrito antes de este cambio está
     * en UTC y, sin convertirlo, se leería seis horas adelantado. Yucatán está
     * en UTC−6 todo el año (México quitó el horario de verano en 2022), así
     * que basta con restar seis horas a cada columna de fecha y hora.
     *
     * Las columnas de sólo fecha (logged_date, fecha…) no se tocan: no hay
     * forma segura de saber a qué día local correspondían.
     */
    private const HORAS = 6;

    private const EXCLUIDAS = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'];

    public function up(): void
    {
        $this->desplazar(-self::HORAS);
    }

    public function down(): void
    {
        $this->desplazar(self::HORAS);
    }

    private function desplazar(int $horas): void
    {
        $motor = DB::getDriverName();

        foreach (Schema::getTables() as $tabla) {
            $nombre = $tabla['name'];
            if (in_array($nombre, self::EXCLUIDAS, true)) {
                continue;
            }

            $columnas = array_filter(
                Schema::getColumns($nombre),
                fn ($c) => preg_match('/^(datetime|timestamp)/i', (string) $c['type_name'])
            );

            foreach ($columnas as $columna) {
                $c = $columna['name'];
                $expresion = match ($motor) {
                    'mysql', 'mariadb' => "DATE_ADD(`{$c}`, INTERVAL {$horas} HOUR)",
                    'sqlite' => "datetime(\"{$c}\", '" . ($horas >= 0 ? '+' : '') . "{$horas} hours')",
                    'pgsql' => "\"{$c}\" + interval '{$horas} hours'",
                    default => throw new RuntimeException("Motor de base de datos no soportado: {$motor}"),
                };

                // Consulta directa: la bitácora clínica no admite cambios por Eloquent,
                // y esto no altera su contenido, sólo la zona en que se expresa la hora.
                DB::table($nombre)->whereNotNull($c)->update([$c => DB::raw($expresion)]);
            }
        }
    }
};
