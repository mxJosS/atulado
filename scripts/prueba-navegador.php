<?php
/**
 * Prueba en navegador real (Chrome sin ventana) de las pantallas con JavaScript:
 * panel de la institución y sus pestañas, ficha clínica, cola de atención,
 * bloques de preguntas del registro diario, mensaje de apoyo e invitación.
 *
 * Usa una base SQLite desechable (storage/app/navegador.sqlite) y un servidor
 * local propio en el puerto 8123. No toca tu base de datos ni tu .env.
 *
 *   php -d extension=sockets scripts/prueba-navegador.php
 *
 * Requiere Chrome instalado y `composer install` con dependencias de desarrollo.
 */

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

require __DIR__ . '/../vendor/autoload.php';

$raiz = realpath(__DIR__ . '/..');
$puerto = 8123;
$base = "http://127.0.0.1:{$puerto}";
$sqlite = $raiz . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'navegador.sqlite';
$chrome = getenv('CHROME_PATH') ?: (PHP_OS_FAMILY === 'Windows'
    ? 'C:\Program Files\Google\Chrome\Application\chrome.exe'
    : '/usr/bin/google-chrome');

// ── Entorno aislado ──────────────────────────────────────────────
$entorno = [
    'APP_ENV' => 'local',
    'APP_DEBUG' => 'true',
    'APP_URL' => $base,
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $sqlite,
    'MAIL_MAILER' => 'log',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'file',
    'CACHE_STORE' => 'file',
];
foreach ($entorno as $k => $v) {
    putenv("{$k}={$v}");
    $_ENV[$k] = $_SERVER[$k] = $v;
}

@unlink($sqlite);
touch($sqlite);

$php = PHP_BINARY;
$artisan = escapeshellarg($raiz . '/artisan');
passthru("\"{$php}\" {$artisan} migrate:fresh --force --seed --seeder=NavegadorSeeder", $codigo);
if ($codigo !== 0) {
    fwrite(STDERR, "No se pudo preparar la base de prueba.\n");
    exit(1);
}

// ── Servidor local ───────────────────────────────────────────────
$servidor = proc_open(
    [$php, '-S', "127.0.0.1:{$puerto}", '-t', $raiz . '/public', $raiz . '/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'],
    [1 => ['file', $raiz . '/storage/logs/navegador-servidor.log', 'a'], 2 => ['file', $raiz . '/storage/logs/navegador-servidor.log', 'a']],
    $tuberias,
    $raiz . '/public', // server.php de Laravel toma la carpeta pública del directorio actual
    array_merge(getenv(), $entorno)
);
register_shutdown_function(fn () => is_resource($servidor) && proc_terminate($servidor));

for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', $puerto); $i++) {
    usleep(200_000);
}

// ── Utilidades ───────────────────────────────────────────────────
$fallos = [];
$pasos = 0;

function js(Page $p, string $codigo): mixed
{
    // Entre llaves: cada fragmento tiene su propio ámbito para `const`.
    return $p->evaluate("{\n{$codigo}\n}")->getReturnValue();
}

function esperar(Page $p, string $condicion, int $ms = 8000): bool
{
    $fin = microtime(true) + $ms / 1000;
    while (microtime(true) < $fin) {
        try {
            if (js($p, "!!({$condicion})")) {
                return true;
            }
        } catch (Throwable) {
            // la página puede estar navegando
        }
        usleep(150_000);
    }

    return false;
}

function paso(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $pasos;
    $pasos++;
    echo ($ok ? "  ✔ " : "  ✘ ") . $nombre . ($ok || $detalle === '' ? '' : " — {$detalle}") . "\n";
    if (!$ok) {
        $fallos[] = $nombre;
    }
}

function erroresJs(Page $p, string $donde): void
{
    $errores = js($p, 'window.__errores || []');
    paso("Sin errores de JavaScript en {$donde}", $errores === [], implode(' | ', (array) $errores));
}

function abrir(Page $p, string $url): void
{
    $p->navigate($url)->waitForNavigation(Page::LOAD, 15000);
}

function entrar(Page $p, string $base, string $email): void
{
    abrir($p, "{$base}/login");
    js($p, "document.querySelector('#mainLoginForm [name=email]').value = " . json_encode($email) . ";
            document.querySelector('#mainLoginForm [name=password]').value = " . json_encode(Database\Seeders\NavegadorSeeder::PASSWORD) . ";
            document.getElementById('mainLoginForm').submit();");
    esperar($p, "!location.pathname.startsWith('/login')", 10000);
    usleep(500_000);
}

$factory = new BrowserFactory($chrome);
$capturaErrores = "window.__errores = [];
  addEventListener('error', e => window.__errores.push((e.message || 'error') + ' @ ' + (e.filename || '').split('/').pop() + ':' + (e.lineno || '')));
  addEventListener('unhandledrejection', e => window.__errores.push('promesa: ' + ((e.reason && e.reason.message) || e.reason)));";

// ═════════════ 1. Panel: clínico con permisos de administración ═════════════
echo "\n1. Panel de la institución y ficha clínica\n";
$navegador = $factory->createBrowser(['headless' => true, 'windowSize' => [1400, 1000], 'noSandbox' => true]);
$p = $navegador->createPage();
$p->addPreScript($capturaErrores);

entrar($p, $base, 'admin@prueba.local');
abrir($p, "{$base}/admin/instituciones/constructora-maya");
paso('La página de la institución carga', (bool) js($p, "!!document.getElementById('tabs-inst')"));
paso('Tiene las 4 pestañas', js($p, "document.querySelectorAll('#tabs-inst .tab').length") === 4);

js($p, "document.querySelector('#tabs-inst .tab[data-pane=padron]').click()");
paso('Pestaña Padrón se muestra', (bool) js($p, "document.getElementById('tab-padron').classList.contains('on')"));
paso('El ancla de la URL sigue la pestaña', js($p, 'location.hash') === '#tab-padron');

js($p, "document.querySelector('[data-open=m-persona-nueva]').click()");
paso('«Agregar persona» abre su ventana', esperar($p, "document.getElementById('m-persona-nueva').classList.contains('open')", 2000));
js($p, "document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}))");
paso('Escape la cierra', esperar($p, "!document.getElementById('m-persona-nueva').classList.contains('open')", 2000));

js($p, "document.querySelector('#tabs-inst .tab[data-pane=invitaciones]').click()");
js($p, "const c = document.querySelector('.chk-invitar'); c.checked = true; c.dispatchEvent(new Event('change'));");
paso('Invitaciones: contar seleccionadas', js($p, "document.getElementById('n-seleccion').textContent") === '1'
    && js($p, "document.getElementById('btn-invitar-seleccion').disabled") === false);

js($p, "document.querySelector('#tabs-inst .tab[data-pane=clinico]').click()");
paso('Plano clínico muestra el semáforo', (bool) js($p, "document.getElementById('tab-clinico').textContent.includes('Rojo agudo')"));

js($p, "document.querySelector('[data-ficha]').click()");
paso('«Abrir» pide el motivo', esperar($p, "document.getElementById('m-motivo').classList.contains('open')", 2000));
js($p, "document.getElementById('form-motivo').requestSubmit()");
paso('La ficha se abre tras declarar el motivo', esperar($p, "document.getElementById('m-ficha').classList.contains('open') && document.querySelector('#ficha-contenido .modal-head')", 8000));
paso('Gráfica de trayectoria del ánimo', (bool) js($p, "!!document.querySelector('#ch-animo svg')"));
js($p, "document.querySelector('#ficha-contenido .tab[data-pane=who5]').click()");
paso('Pestaña WHO-5 con histórico', (bool) js($p, "document.querySelector('[data-tabpane=ficha][data-pane=who5]').classList.contains('on') && !!document.querySelector('#ch-who5 svg')"));

js($p, "document.querySelector('#ficha-contenido [data-open=m-escalar]').click()");
paso('«Escalar protocolo» abre encima de la ficha', esperar($p, "document.getElementById('m-escalar').classList.contains('open')", 2000));
js($p, "document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}))");
paso('Escape cierra sólo la ventana de arriba', esperar($p, "!document.getElementById('m-escalar').classList.contains('open')", 2000)
    && js($p, "document.getElementById('m-ficha').classList.contains('open')") === true);

js($p, "document.querySelector('#ficha-contenido [data-open=m-entrega]').click()");
paso('Entrega de resumen: profesional con NDA disponible', js($p, "document.querySelector('#m-entrega option[value=profesional]').disabled") === false);
erroresJs($p, 'la institución y la ficha');

abrir($p, "{$base}/admin/cola-atencion");
paso('Cola de atención muestra el caso abierto', (bool) js($p, "document.body.textContent.includes('Luis Manuel Chan Poot') && document.body.textContent.includes('CR-')"));
erroresJs($p, 'la cola de atención');
$navegador->close();

// ═════════════ 2. Persona usuaria: registro diario y preguntas ═════════════
echo "\n2. Registro diario, bloques de preguntas y mensaje de apoyo\n";
$navegador = $factory->createBrowser(['headless' => true, 'windowSize' => [1300, 1000], 'noSandbox' => true]);
$p = $navegador->createPage();
$p->addPreScript($capturaErrores);

entrar($p, $base, 'ana@prueba.local');
abrir($p, "{$base}/dashboard");

js($p, "const t = document.getElementById('journal_entry'); t.value = 'hoy ya no quiero vivir así'; t.dispatchEvent(new Event('input'));");
paso('El mensaje de apoyo aparece (decide el servidor)', esperar($p, "getComputedStyle(document.getElementById('crisisEmpathicAlert')).display !== 'none'", 5000));
js($p, "const t = document.getElementById('journal_entry'); t.value = 'fue un buen día'; t.dispatchEvent(new Event('input'));");
paso('…y se oculta con texto neutro', esperar($p, "getComputedStyle(document.getElementById('crisisEmpathicAlert')).display === 'none'", 5000));

js($p, "const f = document.getElementById('moodCheckinForm');
        f.querySelector('[name=score][value=\"3\"]').checked = true;
        document.getElementById('primaryEmotionInput').value = 'Calma';
        f.requestSubmit();");
paso('Día «Regular» abre el primer bloque', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque1')).display === 'flex'", 8000));
paso('El bloque no dice qué cuestionario es', !js($p, "/WHO|MDI|ASQ|OMS/.test(document.getElementById('preguntasBloque1').textContent)"));

$responder = fn (string $form, string $valor) => js($p, "document.querySelectorAll('#{$form} input[type=radio][value=\"{$valor}\"]').forEach(r => r.checked = true); document.getElementById('{$form}').requestSubmit();");
$responder('bloque1Form', '1');
paso('Respuestas bajas abren el segundo bloque', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque2')).display === 'flex'", 8000));

js($p, "document.querySelectorAll('#bloque2Form input[type=radio][value=\"1\"]').forEach(r => r.checked = true);
        document.querySelector('#bloque2Form [name=i6][value=\"2\"]').checked = true;
        document.getElementById('bloque2Form').requestSubmit();");
paso('Ítem sensible abre el tercer bloque', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque3')).display === 'flex'", 8000));
paso('La pregunta adicional empieza oculta', js($p, "getComputedStyle(document.getElementById('preguntaExtra')).display") === 'none');

js($p, "['p2','p3','p4'].forEach(n => document.querySelector('#bloque3Form [name=' + n + '][value=no]').checked = true);
        document.querySelector('#bloque3Form [name=p1][value=si]').checked = true;
        document.getElementById('bloque3Form').requestSubmit();");
paso('El servidor pide la pregunta adicional', esperar($p, "getComputedStyle(document.getElementById('preguntaExtra')).display === 'block'", 8000));
js($p, "document.querySelector('#bloque3Form [name=p5][value=no]').checked = true; document.getElementById('bloque3Form').requestSubmit();");
paso('Al responder, se muestra la pantalla de apoyo', esperar($p, "getComputedStyle(document.getElementById('apoyoInmediato')).display === 'flex'", 8000));
erroresJs($p, 'el tablero de la persona usuaria');
$navegador->close();

// ═════════════ 3. Revisión mensual repartida (bloque 4) ═════════════
echo "\n3. Revisión mensual en partes cortas\n";
$navegador = $factory->createBrowser(['headless' => true, 'windowSize' => [1300, 1000], 'noSandbox' => true]);
$p = $navegador->createPage();
$p->addPreScript($capturaErrores);

entrar($p, $base, 'carla@prueba.local');
abrir($p, "{$base}/dashboard");
js($p, "const f = document.getElementById('moodCheckinForm');
        f.querySelector('[name=score][value=\"5\"]').checked = true;
        document.getElementById('primaryEmotionInput').value = 'Calma';
        f.requestSubmit();");
paso('Un buen día con el WHO-5 al día ofrece una parte corta', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque4')).display === 'flex'", 8000));
paso('Sólo se muestra la primera parte (5 preguntas)', js($p, "[...document.querySelectorAll('.grupo-bloque4')].filter(g => g.style.display === 'block').map(g => g.dataset.grupo).join()") === 'A'
    && js($p, "document.querySelectorAll('.grupo-bloque4[data-grupo=A] .clinical-question-item').length") === 5);
paso('No dice qué test es', !js($p, "/Puchol|Burns|ansiedad|depresi/i.test(document.getElementById('preguntasBloque4').textContent)"));
js($p, "document.querySelectorAll('.grupo-bloque4[data-grupo=A] input[value=\"1\"]').forEach(r => r.checked = true); document.getElementById('bloque4Form').requestSubmit();");
paso('Al responder se cierra con un agradecimiento', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque4')).display === 'none'", 8000));

abrir($p, "{$base}/plan-de-seguridad");
paso('El plan muestra las partes pendientes', (bool) js($p, "!!document.getElementById('revisionMensual') && document.getElementById('revisionMensual').textContent.includes('Parte 2')")
    && !js($p, "document.getElementById('revisionMensual').textContent.includes('Parte 1 ')"));
js($p, "[...document.querySelectorAll('#revisionMensual button')].find(b => b.textContent.includes('Parte 2')).click()");
paso('Desde el plan se abre la parte elegida', esperar($p, "getComputedStyle(document.getElementById('preguntasBloque4')).display === 'flex' && document.querySelector('.grupo-bloque4[data-grupo=B]').style.display === 'block'", 3000));
js($p, "document.querySelectorAll('.grupo-bloque4[data-grupo=B] input[value=\"2\"]').forEach(r => r.checked = true); document.getElementById('bloque4Form').requestSubmit();");
paso('Al terminar, la lista se actualiza', esperar($p, "document.getElementById('revisionMensual') && !document.getElementById('revisionMensual').textContent.includes('Parte 2 ') && document.getElementById('revisionMensual').textContent.includes('Parte 3')", 10000));
erroresJs($p, 'la revisión mensual');
$navegador->close();

// ═════════════ 4. Invitación por correo ═════════════
echo "\n4. Aceptar la invitación\n";
$app = require $raiz . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$invitada = App\Models\Membresia::whereHas('user', fn ($q) => $q->where('email', 'beto@prueba.local'))->with('user')->firstOrFail();
$enlace = app(App\Services\InvitacionService::class)->url($invitada);

$navegador = $factory->createBrowser(['headless' => true, 'windowSize' => [1200, 900], 'noSandbox' => true]);
$p = $navegador->createPage();
$p->addPreScript($capturaErrores);
abrir($p, $enlace);
paso('El enlace abre el formulario de activación', (bool) js($p, "document.body.textContent.includes('Activa tu cuenta') && !!document.querySelector('[name=password]')"));
js($p, "document.querySelector('[name=password]').value = 'NuevaClave2026';
        document.querySelector('[name=password_confirmation]').value = 'NuevaClave2026';
        document.querySelector('[name=acepto]').checked = true;
        document.querySelector('form').submit();");
paso('Activar la cuenta entra a su espacio', esperar($p, "location.pathname === '/dashboard'", 10000));
abrir($p, $enlace);
paso('El mismo enlace ya no sirve', (bool) js($p, "document.body.textContent.includes('ya no es válida')"));
erroresJs($p, 'la invitación');
$navegador->close();

// ── Resultado ────────────────────────────────────────────────────
echo "\n" . ($fallos === []
    ? "Todo bien: {$pasos} comprobaciones en navegador.\n"
    : count($fallos) . " de {$pasos} comprobaciones fallaron:\n  - " . implode("\n  - ", $fallos) . "\n");
exit($fallos === [] ? 0 : 1);
