<?php

namespace App\Services;

use App\Models\EventoCrisis;
use App\Models\MoodLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Lectura del estado del servidor (Ubuntu) para el monitor en tiempo real.
 *
 * Todo se lee de /proc y /sys sin ejecutar comandos del sistema. Los
 * contadores (CPU, red, disco, procesos, consultas) se entregan crudos: el
 * navegador calcula las tasas con la diferencia entre dos muestras, así el
 * servidor no guarda estado y varias pestañas abiertas no se estorban.
 *
 * Nunca se entregan líneas de comando de los procesos ni variables de
 * entorno: pueden contener contraseñas.
 */
class MonitorServidorService
{
    private const TICKS = 100;      // USER_HZ de Linux
    private const PAGINA = 4096;    // tamaño de página en bytes

    public function muestra(): array
    {
        $cpu = $this->cpu();
        $memoria = $this->memoria();
        $discos = $this->discos();
        $carga = $this->carga();
        $temperaturas = $this->temperaturas();
        $nginx = $this->nginx();
        $fpm = $this->fpm();
        $mysql = $this->mysql();
        $procesos = $this->procesos();
        $app = $this->app($procesos['trabajadores_cola']);

        $datos = [
            't' => microtime(true),
            'disponible' => is_readable('/proc/stat'),
            'host' => $this->host(count($cpu['nucleos'] ?? [])),
            'cpu' => $cpu,
            'carga' => $carga,
            'uptime' => $this->uptime(),
            'memoria' => $memoria,
            'discos' => $discos,
            'io' => $this->io(),
            'red' => $this->red(),
            'conexiones' => $this->conexiones(),
            'temperaturas' => $temperaturas,
            'procesos' => $procesos,
            'nginx' => $nginx,
            'fpm' => $fpm,
            'mysql' => $mysql,
            'app' => $app,
        ];

        $datos['alertas'] = $this->alertas($datos);

        return $datos;
    }

    /* ─────────────────────────── Sistema ─────────────────────────── */

    private function host(int $nucleos): array
    {
        $so = null;
        if ($os = $this->leer('/etc/os-release')) {
            if (preg_match('/^PRETTY_NAME="?([^"\n]+)"?/m', $os, $m)) {
                $so = $m[1];
            }
        }

        $modelo = null;
        $mhz = [];
        foreach (explode("\n", (string) $this->leer('/proc/cpuinfo')) as $linea) {
            if ($modelo === null && str_starts_with($linea, 'model name')) {
                $modelo = trim(explode(':', $linea, 2)[1] ?? '');
            }
            if (str_starts_with($linea, 'cpu MHz')) {
                $mhz[] = (float) trim(explode(':', $linea, 2)[1] ?? '0');
            }
        }

        return [
            'nombre' => gethostname() ?: 'servidor',
            'so' => $so ?? PHP_OS_FAMILY,
            'kernel' => php_uname('r'),
            'arquitectura' => php_uname('m'),
            'cpu_modelo' => $modelo,
            'cpu_mhz' => $mhz ? array_map(fn ($v) => round($v), $mhz) : [],
            'nucleos' => $nucleos,
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'hora' => now()->format('d/m/Y H:i:s'),
            'zona' => config('app.timezone'),
        ];
    }

    /** Contadores de /proc/stat: total y por núcleo. */
    private function cpu(): array
    {
        $total = null;
        $nucleos = [];
        $extra = [];

        foreach (explode("\n", (string) $this->leer('/proc/stat')) as $linea) {
            $partes = preg_split('/\s+/', trim($linea));
            $nombre = $partes[0] ?? '';

            if ($nombre === 'cpu') {
                $total = array_map('intval', array_slice($partes, 1, 8));
            } elseif (preg_match('/^cpu\d+$/', $nombre)) {
                $nucleos[] = array_map('intval', array_slice($partes, 1, 8));
            } elseif (in_array($nombre, ['ctxt', 'processes', 'procs_running', 'procs_blocked', 'intr'], true)) {
                $extra[$nombre] = (int) ($partes[1] ?? 0);
            }
        }

        // user nice system idle iowait irq softirq steal
        return ['total' => $total, 'nucleos' => $nucleos, 'extra' => $extra];
    }

    private function carga(): array
    {
        $p = preg_split('/\s+/', trim((string) $this->leer('/proc/loadavg')));
        [$ejecutando, $total] = array_map('intval', explode('/', $p[3] ?? '0/0') + [0, 0]);

        return [
            'uno' => (float) ($p[0] ?? 0),
            'cinco' => (float) ($p[1] ?? 0),
            'quince' => (float) ($p[2] ?? 0),
            'ejecutando' => $ejecutando,
            'entidades' => $total,
        ];
    }

    private function uptime(): array
    {
        $segundos = (float) explode(' ', trim((string) $this->leer('/proc/uptime')))[0];

        return ['segundos' => (int) $segundos, 'arranque' => $segundos > 0 ? now()->subSeconds((int) $segundos)->format('d/m/Y H:i') : null];
    }

    /** /proc/meminfo en bytes. */
    private function memoria(): array
    {
        $m = [];
        foreach (explode("\n", (string) $this->leer('/proc/meminfo')) as $linea) {
            if (preg_match('/^(\w+(?:\(\w+\))?):\s+(\d+)/', $linea, $x)) {
                $m[$x[1]] = (int) $x[2] * 1024;
            }
        }

        return [
            'total' => $m['MemTotal'] ?? 0,
            'libre' => $m['MemFree'] ?? 0,
            'disponible' => $m['MemAvailable'] ?? 0,
            'buffers' => $m['Buffers'] ?? 0,
            'cache' => ($m['Cached'] ?? 0) + ($m['SReclaimable'] ?? 0),
            'compartida' => $m['Shmem'] ?? 0,
            'swap_total' => $m['SwapTotal'] ?? 0,
            'swap_libre' => $m['SwapFree'] ?? 0,
            'sucia' => $m['Dirty'] ?? 0,
        ];
    }

    /** Sistemas de archivos reales montados (sin tmpfs, proc, snaps…). */
    private function discos(): array
    {
        $vistos = [];
        $discos = [];
        $ignorar = ['tmpfs', 'devtmpfs', 'proc', 'sysfs', 'cgroup', 'cgroup2', 'overlay', 'squashfs', 'devpts', 'mqueue',
            'debugfs', 'tracefs', 'securityfs', 'pstore', 'bpf', 'autofs', 'hugetlbfs', 'fusectl', 'configfs', 'efivarfs', 'binfmt_misc', 'ramfs', 'nsfs', 'fuse.lxcfs'];

        foreach (explode("\n", (string) $this->leer('/proc/mounts')) as $linea) {
            $p = explode(' ', $linea);
            if (count($p) < 3 || in_array($p[2], $ignorar, true) || isset($vistos[$p[0]]) || str_starts_with($p[1], '/snap')) {
                continue;
            }
            $montaje = str_replace('\040', ' ', $p[1]);
            $total = @disk_total_space($montaje);
            $libre = @disk_free_space($montaje);
            if (!$total) {
                continue;
            }
            $vistos[$p[0]] = true;
            $discos[] = ['montaje' => $montaje, 'dispositivo' => $p[0], 'tipo' => $p[2], 'total' => (int) $total, 'libre' => (int) $libre];
        }

        if ($discos === [] && ($total = @disk_total_space('/'))) {
            $discos[] = ['montaje' => '/', 'dispositivo' => '/', 'tipo' => '', 'total' => (int) $total, 'libre' => (int) @disk_free_space('/')];
        }

        return $discos;
    }

    /** /proc/diskstats: sólo discos completos (sin particiones ni loop). */
    private function io(): array
    {
        $io = [];
        foreach (explode("\n", (string) $this->leer('/proc/diskstats')) as $linea) {
            $p = preg_split('/\s+/', trim($linea));
            $nombre = $p[2] ?? '';
            if (!preg_match('/^(sd[a-z]+|vd[a-z]+|xvd[a-z]+|nvme\d+n\d+|mmcblk\d+)$/', $nombre)) {
                continue;
            }
            $io[] = [
                'disco' => $nombre,
                'lecturas' => (int) $p[3],
                'sectores_leidos' => (int) $p[5],
                'escrituras' => (int) $p[7],
                'sectores_escritos' => (int) $p[9],
                'en_curso' => (int) $p[11],
                'ms_ocupado' => (int) $p[12],
            ];
        }

        return $io;
    }

    /** /proc/net/dev: bytes y paquetes por interfaz (sin loopback). */
    private function red(): array
    {
        $red = [];
        foreach (array_slice(explode("\n", (string) $this->leer('/proc/net/dev')), 2) as $linea) {
            if (!str_contains($linea, ':')) {
                continue;
            }
            [$iface, $resto] = array_map('trim', explode(':', $linea, 2));
            if ($iface === 'lo' || str_starts_with($iface, 'veth')) {
                continue;
            }
            $p = preg_split('/\s+/', $resto);
            $red[] = [
                'iface' => $iface,
                'rx' => (int) $p[0], 'rx_paquetes' => (int) $p[1], 'rx_errores' => (int) $p[2], 'rx_descartados' => (int) $p[3],
                'tx' => (int) $p[8], 'tx_paquetes' => (int) $p[9], 'tx_errores' => (int) $p[10], 'tx_descartados' => (int) $p[11],
            ];
        }

        return $red;
    }

    /** Conexiones TCP por estado, y cuántas son web (80/443) y a MySQL (3306). */
    private function conexiones(): array
    {
        $estados = ['01' => 'establecidas', '06' => 'time_wait', '0A' => 'escuchando', '08' => 'close_wait', '02' => 'syn_enviado', '03' => 'syn_recibido'];
        $c = array_fill_keys(array_values($estados), 0) + ['web' => 0, 'mysql' => 0, 'total' => 0];

        foreach (['/proc/net/tcp', '/proc/net/tcp6'] as $archivo) {
            foreach (array_slice(explode("\n", (string) $this->leer($archivo)), 1) as $linea) {
                $p = preg_split('/\s+/', trim($linea));
                if (count($p) < 4) {
                    continue;
                }
                $c['total']++;
                $estado = $estados[$p[3]] ?? null;
                if ($estado) {
                    $c[$estado]++;
                }
                if ($p[3] === '01') {
                    $local = hexdec(substr($p[1], strrpos($p[1], ':') + 1));
                    $remoto = hexdec(substr($p[2], strrpos($p[2], ':') + 1));
                    if (in_array($local, [80, 443], true)) {
                        $c['web']++;
                    }
                    if ($local === 3306 || $remoto === 3306) {
                        $c['mysql']++;
                    }
                }
            }
        }

        return $c;
    }

    /** Sensores de /sys/class/hwmon y thermal_zone, en °C. En un VPS suele no haber. */
    private function temperaturas(): array
    {
        $lecturas = [];

        foreach (glob('/sys/class/hwmon/hwmon*') ?: [] as $dir) {
            $chip = trim((string) $this->leer("{$dir}/name")) ?: basename($dir);
            foreach (glob("{$dir}/temp*_input") ?: [] as $entrada) {
                $valor = (int) $this->leer($entrada);
                if ($valor <= 0) {
                    continue;
                }
                $etiqueta = trim((string) $this->leer(str_replace('_input', '_label', $entrada)));
                $critica = (int) $this->leer(str_replace('_input', '_crit', $entrada));
                $lecturas[] = [
                    'nombre' => $chip . ($etiqueta ? " · {$etiqueta}" : ''),
                    'celsius' => round($valor / 1000, 1),
                    'critica' => $critica > 0 ? round($critica / 1000) : null,
                ];
            }
        }

        if ($lecturas === []) {
            foreach (glob('/sys/class/thermal/thermal_zone*') ?: [] as $zona) {
                $valor = (int) $this->leer("{$zona}/temp");
                if ($valor > 0) {
                    $lecturas[] = ['nombre' => trim((string) $this->leer("{$zona}/type")) ?: basename($zona), 'celsius' => round($valor / 1000, 1), 'critica' => null];
                }
            }
        }

        return $lecturas;
    }

    /**
     * Procesos desde /proc/[pid]/stat: nombre, usuario, estado, tiempo de CPU
     * acumulado (ticks) y memoria residente. Sin líneas de comando.
     */
    private function procesos(): array
    {
        $usuarios = $this->usuariosDelSistema();
        $lista = [];
        $hilos = 0;
        $estados = [];
        $trabajadoresCola = 0;
        $fpm = 0;

        foreach (glob('/proc/[0-9]*', GLOB_ONLYDIR) ?: [] as $dir) {
            $stat = $this->leer("{$dir}/stat");
            if (!$stat || ($cierre = strrpos($stat, ')')) === false) {
                continue;
            }
            $inicio = strpos($stat, '(');
            $nombre = substr($stat, $inicio + 1, $cierre - $inicio - 1);
            $p = explode(' ', trim(substr($stat, $cierre + 2)));
            // Tras «)»: 0 estado · 11 utime · 12 stime · 17 hilos · 21 rss (páginas)
            $estado = $p[0] ?? '?';
            $uid = @fileowner($dir);

            if (str_starts_with($nombre, 'php')) {
                // Sólo para contar: la línea de comando no sale de aquí.
                $cmd = (string) $this->leer("{$dir}/cmdline");
                if (str_contains($cmd, 'queue:work')) {
                    $trabajadoresCola++;
                }
                if (str_starts_with($nombre, 'php-fpm')) {
                    $fpm++;
                }
            }

            $hilos += (int) ($p[17] ?? 1);
            $estados[$estado] = ($estados[$estado] ?? 0) + 1;
            $lista[] = [
                (int) basename($dir),
                $nombre,
                $uid !== false ? ($usuarios[$uid] ?? (string) $uid) : '?',
                $estado,
                (int) ($p[11] ?? 0) + (int) ($p[12] ?? 0),
                (int) ($p[21] ?? 0) * self::PAGINA,
            ];
        }

        return [
            // [pid, nombre, usuario, estado, ticks de CPU, bytes de RAM]
            'lista' => $lista,
            'total' => count($lista),
            'hilos' => $hilos,
            'estados' => $estados,
            'ticks_por_segundo' => self::TICKS,
            'trabajadores_cola' => $trabajadoresCola,
            'procesos_fpm' => $fpm,
        ];
    }

    /** uid → nombre, de /etc/passwd. */
    private function usuariosDelSistema(): array
    {
        $usuarios = [];
        foreach (explode("\n", (string) $this->leer('/etc/passwd')) as $linea) {
            $p = explode(':', $linea);
            if (count($p) > 2) {
                $usuarios[(int) $p[2]] = $p[0];
            }
        }

        return $usuarios;
    }

    /* ─────────────────────────── Servicios ─────────────────────────── */

    /** Nginx stub_status: conexiones activas y peticiones acumuladas. */
    private function nginx(): array
    {
        try {
            $r = Http::timeout(1)->connectTimeout(1)->get(config('atulado.monitor.nginx_status'));
            if (!$r->ok() || !preg_match('/Active connections:\s*(\d+).*?(\d+)\s+(\d+)\s+(\d+).*?Reading:\s*(\d+)\s*Writing:\s*(\d+)\s*Waiting:\s*(\d+)/s', $r->body(), $m)) {
                return ['disponible' => false, 'motivo' => 'La página de estado no respondió (HTTP ' . $r->status() . ').'];
            }

            return [
                'disponible' => true,
                'activas' => (int) $m[1], 'aceptadas' => (int) $m[2], 'atendidas' => (int) $m[3], 'peticiones' => (int) $m[4],
                'leyendo' => (int) $m[5], 'escribiendo' => (int) $m[6], 'esperando' => (int) $m[7],
            ];
        } catch (Throwable) {
            return ['disponible' => false, 'motivo' => 'Sin página de estado. Instala deploy/nginx-monitor.conf.'];
        }
    }

    /** PHP-FPM status en JSON: procesos activos, cola de espera, límite alcanzado. */
    private function fpm(): array
    {
        try {
            $r = Http::timeout(1)->connectTimeout(1)->get(config('atulado.monitor.fpm_status'));
            $j = $r->ok() ? $r->json() : null;
            if (!is_array($j)) {
                return ['disponible' => false, 'motivo' => 'La página de estado no respondió (HTTP ' . $r->status() . ').'];
            }

            return [
                'disponible' => true,
                'pool' => $j['pool'] ?? null,
                'gestor' => $j['process manager'] ?? null,
                'desde' => $j['start since'] ?? null,
                'aceptadas' => $j['accepted conn'] ?? 0,
                'cola' => $j['listen queue'] ?? 0,
                'cola_max' => $j['max listen queue'] ?? 0,
                'inactivos' => $j['idle processes'] ?? 0,
                'activos' => $j['active processes'] ?? 0,
                'total' => $j['total processes'] ?? 0,
                'activos_max' => $j['max active processes'] ?? 0,
                'limite_alcanzado' => $j['max children reached'] ?? 0,
                'lentas' => $j['slow requests'] ?? 0,
            ];
        } catch (Throwable) {
            return ['disponible' => false, 'motivo' => 'Sin página de estado. Activa pm.status_path (deploy/nginx-monitor.conf).'];
        }
    }

    private function mysql(): array
    {
        $driver = DB::connection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return ['disponible' => false, 'motivo' => "La base es {$driver}, no MySQL."];
        }

        try {
            $variables = ['Threads_connected', 'Threads_running', 'Max_used_connections', 'Questions', 'Slow_queries', 'Uptime',
                'Aborted_connects', 'Aborted_clients', 'Connections', 'Innodb_buffer_pool_pages_total', 'Innodb_buffer_pool_pages_free',
                'Innodb_buffer_pool_read_requests', 'Innodb_buffer_pool_reads', 'Innodb_row_lock_waits', 'Innodb_deadlocks',
                'Com_select', 'Com_insert', 'Com_update', 'Com_delete', 'Created_tmp_disk_tables', 'Bytes_received', 'Bytes_sent'];
            $lista = "'" . implode("','", $variables) . "'";

            $estado = collect(DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN ({$lista})"))
                ->mapWithKeys(fn ($f) => [$f->Variable_name => (int) $f->Value])->all();
            $max = (int) (DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 0);
            $version = DB::selectOne('SELECT VERSION() AS v')->v ?? null;
            $tamano = (int) (DB::selectOne('SELECT COALESCE(SUM(data_length + index_length), 0) AS b FROM information_schema.tables WHERE table_schema = DATABASE()')->b ?? 0);

            return ['disponible' => true, 'version' => $version, 'max_conexiones' => $max, 'tamano' => $tamano] + $estado;
        } catch (Throwable $e) {
            return ['disponible' => false, 'motivo' => 'No se pudo consultar: ' . mb_substr($e->getMessage(), 0, 120)];
        }
    }

    /** Lo que importa de la aplicación. */
    private function app(int $trabajadoresCola): array
    {
        $app = ['trabajadores_cola' => $trabajadoresCola];

        $intentar = function (string $clave, callable $consulta) use (&$app) {
            try {
                $app[$clave] = $consulta();
            } catch (Throwable) {
                $app[$clave] = null;
            }
        };

        if (config('session.driver') === 'database') {
            $desde = now()->subMinutes(5)->getTimestamp();
            $intentar('sesiones_activas', fn () => DB::table('sessions')->where('last_activity', '>=', $desde)->count());
            $intentar('usuarios_en_linea', fn () => DB::table('sessions')->where('last_activity', '>=', $desde)->whereNotNull('user_id')->distinct()->count('user_id'));
        }
        $intentar('jobs_pendientes', fn () => DB::table('jobs')->count());
        $intentar('jobs_fallidos', fn () => DB::table('failed_jobs')->count());
        $intentar('casos_abiertos', fn () => EventoCrisis::abiertos()->count());
        $intentar('casos_sin_contacto', fn () => EventoCrisis::abiertos()->whereNull('contactado_en')->count());
        $intentar('registros_hoy', fn () => MoodLog::whereDate('logged_date', today())->count());

        if (function_exists('opcache_get_status') && ($o = @opcache_get_status(false))) {
            $mem = $o['memory_usage'] ?? [];
            $est = $o['opcache_statistics'] ?? [];
            $usada = ($mem['used_memory'] ?? 0);
            $total = $usada + ($mem['free_memory'] ?? 0) + ($mem['wasted_memory'] ?? 0);
            $app['opcache'] = [
                'memoria_pct' => $total > 0 ? round($usada / $total * 100, 1) : null,
                'aciertos_pct' => round((float) ($est['opcache_hit_rate'] ?? 0), 1),
                'scripts' => $est['num_cached_scripts'] ?? 0,
            ];
        }

        return $app;
    }

    /* ─────────────────────────── Alertas ─────────────────────────── */

    /**
     * Alertas que se ven en una sola muestra. Las que dependen del tiempo
     * (CPU sostenida, disco saturado) las calcula el navegador.
     *
     * @return list<array{nivel:string, texto:string}>
     */
    private function alertas(array $d): array
    {
        $a = [];
        $agrega = function (string $nivel, string $texto) use (&$a) {
            $a[] = ['nivel' => $nivel, 'texto' => $texto];
        };

        if (!$d['disponible']) {
            $agrega('aviso', 'PHP no puede leer /proc: revisa open_basedir. Las métricas del sistema no están disponibles.');
        }

        $m = $d['memoria'];
        if ($m['total'] > 0) {
            $ram = 100 - $m['disponible'] / $m['total'] * 100;
            if ($ram >= 90) {
                $agrega('critica', 'RAM al ' . round($ram) . '%: el servidor puede empezar a matar procesos.');
            } elseif ($ram >= 80) {
                $agrega('aviso', 'RAM al ' . round($ram) . '%.');
            }
        }
        if ($m['swap_total'] > 0) {
            $swap = 100 - $m['swap_libre'] / $m['swap_total'] * 100;
            if ($swap >= 50) {
                $agrega('aviso', 'Swap al ' . round($swap) . '%: falta RAM y todo se vuelve lento.');
            }
        }

        foreach ($d['discos'] as $disco) {
            $uso = 100 - $disco['libre'] / $disco['total'] * 100;
            if ($uso >= 90) {
                $agrega('critica', "Disco {$disco['montaje']} al " . round($uso) . '%: si se llena, la base de datos y los registros dejan de escribir.');
            } elseif ($uso >= 80) {
                $agrega('aviso', "Disco {$disco['montaje']} al " . round($uso) . '%.');
            }
        }

        $nucleos = max(1, $d['host']['nucleos']);
        if ($d['carga']['cinco'] > $nucleos * 1.5) {
            $agrega('critica', "Carga de 5 min en {$d['carga']['cinco']} con {$nucleos} " . ($nucleos === 1 ? 'núcleo' : 'núcleos') . ': hay procesos esperando CPU.');
        } elseif ($d['carga']['cinco'] > $nucleos) {
            $agrega('aviso', "Carga de 5 min ({$d['carga']['cinco']}) por encima de los núcleos ({$nucleos}).");
        }

        foreach ($d['temperaturas'] as $t) {
            $limite = $t['critica'] ?? 90;
            if ($t['celsius'] >= $limite - 10) {
                $agrega($t['celsius'] >= $limite ? 'critica' : 'aviso', "Temperatura {$t['nombre']}: {$t['celsius']} °C.");
            }
        }

        if (($d['fpm']['disponible'] ?? false)) {
            if ($d['fpm']['cola'] > 0) {
                $agrega('critica', "PHP-FPM tiene {$d['fpm']['cola']} peticiones esperando: todos los procesos están ocupados.");
            }
            if ($d['fpm']['limite_alcanzado'] > 0) {
                $agrega('aviso', "PHP-FPM llegó a su máximo de procesos {$d['fpm']['limite_alcanzado']} " . ($d['fpm']['limite_alcanzado'] === 1 ? 'vez' : 'veces') . ' desde que arrancó: sube pm.max_children si la RAM alcanza.');
            }
        }

        if (($d['mysql']['disponible'] ?? false) && $d['mysql']['max_conexiones'] > 0) {
            $uso = $d['mysql']['Threads_connected'] / $d['mysql']['max_conexiones'] * 100;
            if ($uso >= 80) {
                $agrega('critica', 'MySQL usa ' . round($uso) . '% de sus conexiones.');
            }
        }

        $app = $d['app'];
        if (($app['casos_sin_contacto'] ?? 0) > 0) {
            $agrega('critica', "{$app['casos_sin_contacto']} " . ($app['casos_sin_contacto'] === 1 ? 'caso de crisis sin contacto humano' : 'casos de crisis sin contacto humano') . ' en la cola de atención.');
        }
        if (($app['jobs_fallidos'] ?? 0) > 0) {
            $agrega('aviso', "{$app['jobs_fallidos']} " . ($app['jobs_fallidos'] === 1 ? 'trabajo fallido' : 'trabajos fallidos') . ' en la cola (correos que no salieron).');
        }
        if (($app['jobs_pendientes'] ?? 0) > 0 && $app['trabajadores_cola'] === 0 && $d['disponible']) {
            $agrega('critica', 'Hay trabajos en la cola y ningún trabajador corriendo: revisa el servicio atulado-cola.');
        } elseif (($app['jobs_pendientes'] ?? 0) > 200) {
            $agrega('aviso', "{$app['jobs_pendientes']} trabajos esperando en la cola.");
        }

        return $a;
    }

    private function leer(string $ruta): ?string
    {
        $contenido = @file_get_contents($ruta);

        return $contenido === false ? null : $contenido;
    }
}
