<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <meta name="referrer" content="no-referrer">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Monitor del servidor</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --fondo: #0B110E; --tarjeta: #131B17; --tarjeta2: #18221D; --linea: #24312A; --texto: #E6ECE8; --suave: #8EA59A;
      --verde: #5AB56E; --azul: #4AA3DF; --ambar: #E0A33A; --rojo: #E05A4A; --morado: #A78BDA; --cian: #3FC1C9;
    }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--fondo); color: var(--texto); font: 13px/1.45 'Manrope', system-ui, sans-serif; }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .muted { color: var(--suave); }
    header { position: sticky; top: 0; z-index: 5; background: rgba(11, 17, 14, .94); backdrop-filter: blur(6px); border-bottom: 1px solid var(--linea); }
    .cab { max-width: 1480px; margin: 0 auto; padding: 12px 16px; display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .cab h1 { font-size: 17px; margin: 0; display: flex; align-items: center; gap: 8px; }
    .punto { width: 9px; height: 9px; border-radius: 50%; background: var(--verde); box-shadow: 0 0 0 0 rgba(90,181,110,.6); animation: latido 2s infinite; }
    .punto.caido { background: var(--rojo); animation: none; }
    @keyframes latido { 0% { box-shadow: 0 0 0 0 rgba(90,181,110,.6); } 70% { box-shadow: 0 0 0 8px rgba(90,181,110,0); } 100% { box-shadow: 0 0 0 0 rgba(90,181,110,0); } }
    .info-host { display: flex; gap: 14px; flex-wrap: wrap; font-size: 12px; color: var(--suave); }
    .info-host b { color: var(--texto); font-weight: 600; }
    .controles { display: flex; gap: 8px; align-items: center; }
    .controles select, .controles button { background: var(--tarjeta2); color: var(--texto); border: 1px solid var(--linea); border-radius: 8px; padding: 6px 10px; font: inherit; cursor: pointer; }
    main { max-width: 1480px; margin: 0 auto; padding: 16px; display: grid; gap: 14px; }
    .fila { display: grid; gap: 14px; }
    .kpis { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
    .dos { grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); }
    .tres { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
    .tarjeta { background: var(--tarjeta); border: 1px solid var(--linea); border-radius: 12px; padding: 14px; min-width: 0; }
    .tarjeta h2 { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--suave); margin: 0 0 10px; display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
    .tarjeta h2 span { text-transform: none; letter-spacing: 0; font-weight: 600; color: var(--texto); font-family: 'IBM Plex Mono', monospace; font-size: 12px; }
    .kpi .valor { font: 700 24px 'IBM Plex Mono', monospace; margin: 2px 0; }
    .kpi .etiqueta { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--suave); font-weight: 700; }
    .kpi .pie { font-size: 11px; color: var(--suave); }
    .barra { height: 6px; background: var(--tarjeta2); border-radius: 4px; overflow: hidden; margin-top: 6px; }
    .barra i { display: block; height: 100%; width: 0; background: var(--verde); transition: width .4s; }
    .grafica { position: relative; height: 180px; }
    .grafica.baja { height: 130px; }
    .nucleos { display: grid; grid-template-columns: repeat(auto-fill, minmax(58px, 1fr)); gap: 6px; margin-top: 10px; }
    .nucleo { background: var(--tarjeta2); border-radius: 6px; padding: 5px 6px; font-size: 10.5px; }
    .nucleo .n { color: var(--suave); display: flex; justify-content: space-between; }
    .nucleo .col { height: 34px; background: #0F1613; border-radius: 3px; margin-top: 4px; position: relative; overflow: hidden; }
    .nucleo .col i { position: absolute; left: 0; right: 0; bottom: 0; background: var(--verde); transition: height .4s; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th { text-align: left; color: var(--suave); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; padding: 5px 6px; border-bottom: 1px solid var(--linea); position: sticky; top: 0; background: var(--tarjeta); }
    td { padding: 4px 6px; border-bottom: 1px solid #19231E; white-space: nowrap; }
    td.num, th.num { text-align: right; font-family: 'IBM Plex Mono', monospace; }
    .tabla-procesos { max-height: 420px; overflow: auto; }
    .kv { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; font-size: 12.5px; }
    .kv dt { color: var(--suave); }
    .kv dd { margin: 0; text-align: right; font-family: 'IBM Plex Mono', monospace; }
    .alerta { display: flex; gap: 10px; align-items: flex-start; padding: 8px 10px; border-radius: 8px; margin-bottom: 6px; font-size: 12.5px; }
    .alerta.critica { background: rgba(224, 90, 74, .12); border: 1px solid rgba(224, 90, 74, .45); }
    .alerta.aviso { background: rgba(224, 163, 58, .1); border: 1px solid rgba(224, 163, 58, .4); }
    .alerta .ico { font-weight: 700; font-family: 'IBM Plex Mono', monospace; }
    .alerta.critica .ico { color: var(--rojo); }
    .alerta.aviso .ico { color: var(--ambar); }
    .ok { color: var(--verde); }
    .chip { display: inline-block; padding: 1px 7px; border-radius: 999px; font-size: 10.5px; font-weight: 700; background: var(--tarjeta2); color: var(--suave); }
    .chip.rojo { background: rgba(224,90,74,.18); color: var(--rojo); }
    .chip.verde { background: rgba(90,181,110,.16); color: var(--verde); }
    .filtros { display: flex; gap: 6px; margin-bottom: 8px; flex-wrap: wrap; }
    .filtros input, .filtros button { background: var(--tarjeta2); color: var(--texto); border: 1px solid var(--linea); border-radius: 7px; padding: 5px 9px; font: inherit; }
    .filtros button { cursor: pointer; }
    .filtros button.on { border-color: var(--verde); color: var(--verde); }
    .no-disp { color: var(--suave); font-size: 12px; padding: 6px 0; }
    .leyenda { display: flex; gap: 12px; font-size: 11px; color: var(--suave); margin-top: 6px; flex-wrap: wrap; }
    .leyenda i { display: inline-block; width: 10px; height: 3px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }
    .controles button.reinicio-servicios { border-color: rgba(224,163,58,.55); color: var(--ambar); }
    .controles button.reinicio-maquina { border-color: rgba(224,90,74,.55); color: var(--rojo); }
    .velo { position: fixed; inset: 0; z-index: 20; background: rgba(0,0,0,.62); display: none; align-items: center; justify-content: center; padding: 16px; }
    .velo.abierto { display: flex; }
    .dialogo { background: var(--tarjeta); border: 1px solid var(--linea); border-radius: 14px; width: 100%; max-width: 460px; padding: 18px; }
    .dialogo h3 { margin: 0 0 6px; font-size: 16px; }
    .dialogo p { margin: 0 0 12px; color: var(--suave); font-size: 12.5px; }
    .dialogo label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--suave); margin: 10px 0 4px; }
    .dialogo input { width: 100%; background: var(--tarjeta2); color: var(--texto); border: 1px solid var(--linea); border-radius: 8px; padding: 8px 10px; font: inherit; }
    .dialogo .acciones { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
    .dialogo .acciones button { background: var(--tarjeta2); color: var(--texto); border: 1px solid var(--linea); border-radius: 8px; padding: 7px 14px; font: inherit; cursor: pointer; }
    .dialogo .acciones button.peligro { background: var(--rojo); border-color: var(--rojo); color: #fff; font-weight: 700; }
    .dialogo .acciones button:disabled { opacity: .5; cursor: default; }
    .dialogo .aviso { font-size: 12.5px; margin-top: 10px; min-height: 1em; }
    .dialogo .aviso.error { color: var(--rojo); }
    .dialogo .aviso.ok { color: var(--verde); }
    @media (max-width: 640px) {
      main { padding: 12px; }
      .dos, .tres { grid-template-columns: 1fr; }
      .kpi .valor { font-size: 20px; }
    }
  </style>
</head>
<body>
<header>
  <div class="cab">
    <h1><span class="punto" id="punto"></span> Monitor del servidor <span class="muted mono" id="host" style="font-size: 13px;"></span></h1>
    <div class="info-host" id="infoHost"></div>
    <div class="controles">
      <span class="muted mono" id="actualizado">conectando…</span>
      <select id="intervalo" aria-label="Frecuencia de actualización">
        <option value="1000">1 s</option>
        <option value="2000" selected>2 s</option>
        <option value="5000">5 s</option>
        <option value="10000">10 s</option>
      </select>
      <button type="button" id="pausa">Pausar</button>
      <button type="button" class="reinicio-servicios" data-reinicio="servicios" title="Reinicia MySQL, PHP-FPM, Nginx y la cola">Reiniciar servicios</button>
      <button type="button" class="reinicio-maquina" data-reinicio="maquina" title="Reinicia la máquina completa">Reiniciar servidor</button>
    </div>
  </div>
</header>

<main>
  {{-- Alertas --}}
  <section class="tarjeta" id="tarjetaAlertas">
    <h2>Alertas <span id="conteoAlertas">—</span></h2>
    <div id="alertas"><div class="no-disp">Esperando la primera lectura…</div></div>
  </section>

  {{-- Indicadores rápidos --}}
  <section class="fila kpis">
    <div class="tarjeta kpi"><div class="etiqueta">CPU</div><div class="valor" id="kCpu">—</div><div class="pie" id="kCpuPie">&nbsp;</div><div class="barra"><i id="bCpu"></i></div></div>
    <div class="tarjeta kpi"><div class="etiqueta">RAM</div><div class="valor" id="kRam">—</div><div class="pie" id="kRamPie">&nbsp;</div><div class="barra"><i id="bRam"></i></div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Swap</div><div class="valor" id="kSwap">—</div><div class="pie" id="kSwapPie">&nbsp;</div><div class="barra"><i id="bSwap"></i></div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Disco /</div><div class="valor" id="kDisco">—</div><div class="pie" id="kDiscoPie">&nbsp;</div><div class="barra"><i id="bDisco"></i></div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Carga (1 min)</div><div class="valor" id="kCarga">—</div><div class="pie" id="kCargaPie">&nbsp;</div><div class="barra"><i id="bCarga"></i></div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Red ↓ / ↑</div><div class="valor" id="kRed" style="font-size: 18px;">—</div><div class="pie" id="kRedPie">&nbsp;</div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Temperatura</div><div class="valor" id="kTemp">—</div><div class="pie" id="kTempPie">&nbsp;</div></div>
    <div class="tarjeta kpi"><div class="etiqueta">Encendido</div><div class="valor" id="kUptime" style="font-size: 18px;">—</div><div class="pie" id="kUptimePie">&nbsp;</div></div>
    <div class="tarjeta kpi"><div class="etiqueta">En línea</div><div class="valor" id="kLinea">—</div><div class="pie" id="kLineaPie">&nbsp;</div></div>
  </section>

  {{-- CPU y memoria --}}
  <section class="fila dos">
    <div class="tarjeta">
      <h2>CPU <span id="cpuModelo"></span></h2>
      <div class="grafica"><canvas id="gCpu"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>Uso total</span><span><i style="background: var(--ambar);"></i>Espera de disco (iowait)</span><span><i style="background: var(--rojo);"></i>Robado (steal)</span></div>
      <div class="nucleos" id="nucleos"></div>
    </div>
    <div class="tarjeta">
      <h2>Memoria <span id="memTotal"></span></h2>
      <div class="grafica"><canvas id="gMem"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--azul);"></i>RAM en uso</span><span><i style="background: var(--morado);"></i>Caché y búferes</span><span><i style="background: var(--rojo);"></i>Swap</span></div>
      <dl class="kv" id="memDetalle" style="margin-top: 10px;"></dl>
    </div>
  </section>

  {{-- Red, disco y carga --}}
  <section class="fila tres">
    <div class="tarjeta">
      <h2>Tráfico de red <span id="redTotal"></span></h2>
      <div class="grafica baja"><canvas id="gRed"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--cian);"></i>Entrada</span><span><i style="background: var(--morado);"></i>Salida</span></div>
      <table style="margin-top: 8px;"><thead><tr><th>Interfaz</th><th class="num">↓ /s</th><th class="num">↑ /s</th><th class="num">Total ↓</th><th class="num">Total ↑</th><th class="num">Errores</th></tr></thead><tbody id="redTabla"></tbody></table>
    </div>
    <div class="tarjeta">
      <h2>Disco · lectura y escritura <span id="ioTotal"></span></h2>
      <div class="grafica baja"><canvas id="gIo"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>Lectura</span><span><i style="background: var(--ambar);"></i>Escritura</span></div>
      <table style="margin-top: 8px;"><thead><tr><th>Disco</th><th class="num">Lee /s</th><th class="num">Escribe /s</th><th class="num">Ocupado</th><th class="num">En curso</th></tr></thead><tbody id="ioTabla"></tbody></table>
    </div>
    <div class="tarjeta">
      <h2>Carga del sistema <span id="cargaTexto"></span></h2>
      <div class="grafica baja"><canvas id="gCarga"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>1 min</span><span><i style="background: var(--azul);"></i>5 min</span><span><i style="background: var(--morado);"></i>15 min</span><span><i style="background: var(--rojo);"></i>Núcleos (límite)</span></div>
      <dl class="kv" id="cargaDetalle" style="margin-top: 10px;"></dl>
    </div>
  </section>

  {{-- Almacenamiento y conexiones --}}
  <section class="fila dos">
    <div class="tarjeta">
      <h2>Almacenamiento</h2>
      <table><thead><tr><th>Montaje</th><th>Dispositivo</th><th>Tipo</th><th class="num">Usado</th><th class="num">Libre</th><th class="num">Total</th><th style="width: 30%;">Uso</th></tr></thead><tbody id="discosTabla"></tbody></table>
    </div>
    <div class="tarjeta">
      <h2>Conexiones TCP <span id="conTotal"></span></h2>
      <div class="grafica baja"><canvas id="gCon"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>Establecidas</span><span><i style="background: var(--azul);"></i>Web (80/443)</span><span><i style="background: var(--ambar);"></i>TIME_WAIT</span><span><i style="background: var(--morado);"></i>MySQL</span></div>
      <dl class="kv" id="conDetalle" style="margin-top: 10px;"></dl>
    </div>
  </section>

  {{-- Servicios --}}
  <section class="fila tres">
    <div class="tarjeta">
      <h2>Nginx <span id="nginxEstado"></span></h2>
      <div class="grafica baja"><canvas id="gNginx"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>Peticiones /s</span><span><i style="background: var(--azul);"></i>Conexiones activas</span></div>
      <dl class="kv" id="nginxDetalle" style="margin-top: 10px;"></dl>
    </div>
    <div class="tarjeta">
      <h2>PHP-FPM <span id="fpmEstado"></span></h2>
      <div class="grafica baja"><canvas id="gFpm"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--ambar);"></i>Procesos ocupados</span><span><i style="background: var(--verde);"></i>Libres</span><span><i style="background: var(--rojo);"></i>En cola</span></div>
      <dl class="kv" id="fpmDetalle" style="margin-top: 10px;"></dl>
    </div>
    <div class="tarjeta">
      <h2>MySQL <span id="mysqlEstado"></span></h2>
      <div class="grafica baja"><canvas id="gMysql"></canvas></div>
      <div class="leyenda"><span><i style="background: var(--verde);"></i>Consultas /s</span><span><i style="background: var(--azul);"></i>Conexiones</span><span><i style="background: var(--ambar);"></i>Ejecutando</span></div>
      <dl class="kv" id="mysqlDetalle" style="margin-top: 10px;"></dl>
    </div>
  </section>

  {{-- Aplicación y temperaturas --}}
  <section class="fila dos">
    <div class="tarjeta">
      <h2>A Tu Lado · aplicación</h2>
      <dl class="kv" id="appDetalle"></dl>
    </div>
    <div class="tarjeta">
      <h2>Temperatura</h2>
      <div id="temperaturas"></div>
    </div>
  </section>

  {{-- Procesos --}}
  <section class="tarjeta">
    <h2>Procesos <span id="procTotal"></span></h2>
    <div class="filtros">
      <input type="search" id="procFiltro" placeholder="Filtrar por nombre o usuario…" aria-label="Filtrar procesos">
      <button type="button" data-orden="cpu" class="on">Ordenar por CPU</button>
      <button type="button" data-orden="ram">Ordenar por RAM</button>
      <span class="muted" id="procEstados" style="align-self: center; font-size: 12px;"></span>
    </div>
    <div class="tabla-procesos">
      <table>
        <thead><tr><th class="num">PID</th><th>Nombre</th><th>Usuario</th><th>Estado</th><th class="num">CPU</th><th class="num">RAM</th><th class="num">% RAM</th></tr></thead>
        <tbody id="procTabla"></tbody>
      </table>
    </div>
  </section>
</main>

{{-- Confirmación de reinicio --}}
<div class="velo" id="veloReinicio" role="dialog" aria-modal="true" aria-labelledby="tituloReinicio">
  <form class="dialogo" id="formReinicio" autocomplete="off">
    <h3 id="tituloReinicio"></h3>
    <p id="textoReinicio"></p>
    <label for="passReinicio">Tu contraseña</label>
    <input type="password" id="passReinicio" autocomplete="current-password" required>
    <label for="confReinicio">Escribe REINICIAR para confirmar</label>
    <input type="text" id="confReinicio" autocapitalize="characters" spellcheck="false" required>
    <div class="aviso" id="avisoReinicio" aria-live="polite"></div>
    <div class="acciones">
      <button type="button" id="cancelarReinicio">Cancelar</button>
      <button type="submit" class="peligro" id="enviarReinicio" disabled>Reiniciar</button>
    </div>
  </form>
</div>

<script>
(() => {
  const RUTA = @json($rutaReinicio);
  const TEXTOS = {
    servicios: ['Reiniciar servicios', 'Se reinician MySQL, PHP-FPM, Nginx y la cola de correos. La máquina sigue encendida; la página deja de responder unos segundos y las peticiones en curso se pierden.'],
    maquina: ['Reiniciar el servidor completo', 'Se reinicia la máquina. A Tu Lado queda fuera de línea uno o dos minutos para todas las personas, incluido quien esté registrando o en el botón de crisis. Úsalo sólo si reiniciar los servicios no bastó.'],
  };
  const $ = (id) => document.getElementById(id);
  const velo = $('veloReinicio');
  let tipo = null;

  const cerrar = () => { velo.classList.remove('abierto'); $('passReinicio').value = ''; $('confReinicio').value = ''; };
  const validar = () => { $('enviarReinicio').disabled = !$('passReinicio').value || $('confReinicio').value.trim() !== 'REINICIAR'; };
  const aviso = (texto, clase) => { const a = $('avisoReinicio'); a.textContent = texto; a.className = 'aviso ' + (clase || ''); };

  document.querySelectorAll('[data-reinicio]').forEach((b) => b.addEventListener('click', () => {
    tipo = b.dataset.reinicio;
    [$('tituloReinicio').textContent, $('textoReinicio').textContent] = TEXTOS[tipo];
    $('enviarReinicio').textContent = TEXTOS[tipo][0];
    aviso('');
    validar();
    velo.classList.add('abierto');
    $('passReinicio').focus();
  }));
  $('cancelarReinicio').addEventListener('click', cerrar);
  velo.addEventListener('click', (e) => { if (e.target === velo) cerrar(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && velo.classList.contains('abierto')) cerrar(); });
  ['passReinicio', 'confReinicio'].forEach((id) => $(id).addEventListener('input', validar));

  $('formReinicio').addEventListener('submit', async (e) => {
    e.preventDefault();
    $('enviarReinicio').disabled = true;
    aviso('Enviando…');
    try {
      const r = await fetch(RUTA, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ tipo, password: $('passReinicio').value, confirmacion: $('confReinicio').value.trim() }),
      });
      const d = await r.json().catch(() => ({}));
      if (r.status === 429) throw new Error('Demasiados intentos. Espera unos minutos.');
      if (!r.ok || !d.ok) throw new Error(d.mensaje || ('Error ' + r.status));
      aviso(d.mensaje, 'ok');
      $('actualizado').textContent = tipo === 'maquina' ? 'reiniciando el servidor…' : 'reiniciando servicios…';
      setTimeout(cerrar, 3500);
    } catch (err) {
      aviso(err.message, 'error');
      validar();
    }
  });
})();
</script>

<script>
(() => {
  const RUTA = @json($rutaDatos);
  const PUNTOS = 90;
  const ESTADO_PROC = { R: 'Ejecutando', S: 'Dormido', D: 'Esperando E/S', Z: 'Zombi', T: 'Detenido', I: 'Inactivo', t: 'Rastreado', X: 'Muerto' };

  let intervalo = 2000, pausado = false, temporizador = null, previo = null, ordenProc = 'cpu';
  const historia = { etiquetas: [] };
  const sostenido = { cpu: 0, iowait: 0, steal: 0, io: {} };

  // ─── Utilidades ───
  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const bytes = (b, dec = 1) => {
    if (b == null || isNaN(b)) return '—';
    const u = ['B', 'KB', 'MB', 'GB', 'TB']; let i = 0; let v = Math.abs(b);
    while (v >= 1024 && i < u.length - 1) { v /= 1024; i++; }
    return (b < 0 ? '-' : '') + v.toFixed(i === 0 ? 0 : dec) + ' ' + u[i];
  };
  const pct = (v, dec = 1) => (v == null || isNaN(v)) ? '—' : v.toFixed(dec) + '%';
  const num = (v) => (v == null) ? '—' : Number(v).toLocaleString('es-MX');
  const duracion = (s) => {
    const d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
    return (d ? d + ' d ' : '') + (h || d ? h + ' h ' : '') + m + ' min';
  };
  const color = (v, aviso = 70, critico = 90) => v >= critico ? 'var(--rojo)' : v >= aviso ? 'var(--ambar)' : 'var(--verde)';
  const barra = (id, v, aviso, critico) => { const el = $(id); if (!el) return; el.style.width = Math.max(0, Math.min(100, v || 0)) + '%'; el.style.background = color(v, aviso, critico); };
  const kv = (filas) => filas.map(([k, v]) => `<dt>${esc(k)}</dt><dd>${v}</dd>`).join('');
  const cpuUso = (a, b) => {
    if (!a || !b) return null;
    const suma = (x) => x.reduce((s, v) => s + v, 0);
    const dt = suma(b) - suma(a); if (dt <= 0) return null;
    const di = (b[3] + b[4]) - (a[3] + a[4]);
    return { uso: 100 * (1 - di / dt), iowait: 100 * (b[4] - a[4]) / dt, steal: 100 * ((b[7] || 0) - (a[7] || 0)) / dt };
  };

  // ─── Gráficas ───
  Chart.defaults.color = '#8EA59A';
  Chart.defaults.font.family = "'IBM Plex Mono', monospace";
  Chart.defaults.font.size = 10;
  const graficas = {};
  const crear = (id, series, opciones = {}) => {
    const ctx = $(id).getContext('2d');
    graficas[id] = new Chart(ctx, {
      type: 'line',
      data: { labels: historia.etiquetas, datasets: series.map((s) => ({ label: s.nombre, data: [], borderColor: s.color, backgroundColor: s.relleno || 'transparent', fill: !!s.relleno, borderWidth: s.grosor || 1.6, pointRadius: 0, tension: .25, borderDash: s.guiones || [] })) },
      options: {
        animation: false, responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${opciones.formato ? opciones.formato(c.parsed.y) : c.parsed.y}` } } },
        scales: {
          x: { display: false },
          y: { beginAtZero: true, max: opciones.max, grid: { color: '#1C2822' }, ticks: { maxTicksLimit: 5, callback: (v) => opciones.formato ? opciones.formato(v) : v } },
        },
      },
    });
  };
  const empujar = (id, valores) => {
    const g = graficas[id]; if (!g) return;
    valores.forEach((v, i) => { const d = g.data.datasets[i].data; d.push(v); if (d.length > PUNTOS) d.shift(); });
  };

  const porSeg = (v) => bytes(v, 0) + '/s';
  crear('gCpu', [{ nombre: 'Uso', color: '#5AB56E', relleno: 'rgba(90,181,110,.12)', grosor: 2 }, { nombre: 'iowait', color: '#E0A33A' }, { nombre: 'steal', color: '#E05A4A' }], { max: 100, formato: (v) => Math.round(v) + '%' });
  crear('gMem', [{ nombre: 'RAM en uso', color: '#4AA3DF', relleno: 'rgba(74,163,223,.12)', grosor: 2 }, { nombre: 'Caché', color: '#A78BDA' }, { nombre: 'Swap', color: '#E05A4A' }], { max: 100, formato: (v) => Math.round(v) + '%' });
  crear('gRed', [{ nombre: 'Entrada', color: '#3FC1C9', relleno: 'rgba(63,193,201,.1)' }, { nombre: 'Salida', color: '#A78BDA' }], { formato: porSeg });
  crear('gIo', [{ nombre: 'Lectura', color: '#5AB56E', relleno: 'rgba(90,181,110,.1)' }, { nombre: 'Escritura', color: '#E0A33A' }], { formato: porSeg });
  crear('gCarga', [{ nombre: '1 min', color: '#5AB56E', grosor: 2 }, { nombre: '5 min', color: '#4AA3DF' }, { nombre: '15 min', color: '#A78BDA' }, { nombre: 'Núcleos', color: '#E05A4A', guiones: [5, 4], grosor: 1 }], { formato: (v) => Number(v).toFixed(1) });
  crear('gCon', [{ nombre: 'Establecidas', color: '#5AB56E', grosor: 2 }, { nombre: 'Web', color: '#4AA3DF' }, { nombre: 'TIME_WAIT', color: '#E0A33A' }, { nombre: 'MySQL', color: '#A78BDA' }]);
  crear('gNginx', [{ nombre: 'Peticiones/s', color: '#5AB56E', relleno: 'rgba(90,181,110,.1)', grosor: 2 }, { nombre: 'Activas', color: '#4AA3DF' }], { formato: (v) => Number(v).toFixed(0) });
  crear('gFpm', [{ nombre: 'Ocupados', color: '#E0A33A', relleno: 'rgba(224,163,58,.12)', grosor: 2 }, { nombre: 'Libres', color: '#5AB56E' }, { nombre: 'En cola', color: '#E05A4A' }], { formato: (v) => Number(v).toFixed(0) });
  crear('gMysql', [{ nombre: 'Consultas/s', color: '#5AB56E', relleno: 'rgba(90,181,110,.1)', grosor: 2 }, { nombre: 'Conexiones', color: '#4AA3DF' }, { nombre: 'Ejecutando', color: '#E0A33A' }], { formato: (v) => Number(v).toFixed(0) });

  // ─── Pintar una muestra ───
  function pintar(d) {
    const dt = previo ? Math.max(0.2, d.t - previo.t) : null;
    const h = d.host;
    const nucleos = Math.max(1, h.nucleos || 1);

    $('host').textContent = h.nombre;
    $('infoHost').innerHTML = [
      ['SO', h.so], ['Kernel', h.kernel], ['PHP', h.php], ['Laravel', h.laravel], ['Hora', h.hora],
    ].map(([k, v]) => `<span>${k} <b>${esc(v)}</b></span>`).join('');
    $('cpuModelo').textContent = `${h.cpu_modelo ? h.cpu_modelo + ' · ' : ''}${nucleos} ${nucleos === 1 ? 'núcleo' : 'núcleos'}`;

    historia.etiquetas.push(new Date().toLocaleTimeString('es-MX'));
    if (historia.etiquetas.length > PUNTOS) historia.etiquetas.shift();

    const alertasCliente = [];

    // CPU
    const uso = previo ? cpuUso(previo.cpu.total, d.cpu.total) : null;
    if (uso) {
      $('kCpu').textContent = pct(uso.uso); $('kCpu').style.color = color(uso.uso);
      $('kCpuPie').textContent = `iowait ${pct(uso.iowait)} · steal ${pct(uso.steal)}`;
      barra('bCpu', uso.uso);
      empujar('gCpu', [uso.uso, uso.iowait, uso.steal]);
      sostenido.cpu = uso.uso >= 90 ? sostenido.cpu + dt : 0;
      sostenido.iowait = uso.iowait >= 20 ? sostenido.iowait + dt : 0;
      sostenido.steal = uso.steal >= 10 ? sostenido.steal + dt : 0;
      if (sostenido.cpu >= 30) alertasCliente.push({ nivel: 'critica', texto: `CPU arriba de 90% durante ${Math.round(sostenido.cpu)} s.` });
      if (sostenido.iowait >= 30) alertasCliente.push({ nivel: 'aviso', texto: `La CPU espera al disco (iowait ${pct(uso.iowait)}) desde hace ${Math.round(sostenido.iowait)} s: el disco es el cuello de botella.` });
      if (sostenido.steal >= 30) alertasCliente.push({ nivel: 'aviso', texto: `El proveedor está quitando CPU a la máquina virtual (steal ${pct(uso.steal)}).` });
    }
    $('nucleos').innerHTML = d.cpu.nucleos.map((n, i) => {
      const u = previo && previo.cpu.nucleos[i] ? cpuUso(previo.cpu.nucleos[i], n) : null;
      const v = u ? u.uso : 0;
      const mhz = h.cpu_mhz[i] ? `${h.cpu_mhz[i]} MHz` : '';
      return `<div class="nucleo" title="${mhz}"><div class="n"><span>CPU ${i}</span><b>${u ? Math.round(v) + '%' : '—'}</b></div><div class="col"><i style="height:${v}%; background:${color(v)}"></i></div></div>`;
    }).join('');

    // Memoria
    const m = d.memoria;
    if (m.total) {
      const usada = m.total - m.disponible;
      const ram = usada / m.total * 100;
      const cache = (m.cache + m.buffers) / m.total * 100;
      const swapUsada = m.swap_total - m.swap_libre;
      const swap = m.swap_total ? swapUsada / m.swap_total * 100 : 0;
      $('kRam').textContent = pct(ram); $('kRam').style.color = color(ram, 80, 90);
      $('kRamPie').textContent = `${bytes(usada)} de ${bytes(m.total)}`;
      barra('bRam', ram, 80, 90);
      $('kSwap').textContent = m.swap_total ? pct(swap) : 'Sin swap';
      $('kSwapPie').textContent = m.swap_total ? `${bytes(swapUsada)} de ${bytes(m.swap_total)}` : 'No hay partición de intercambio';
      barra('bSwap', swap, 30, 60);
      $('memTotal').textContent = bytes(m.total);
      empujar('gMem', [ram, cache, swap]);
      $('memDetalle').innerHTML = kv([
        ['En uso (aplicaciones)', bytes(usada)], ['Disponible', bytes(m.disponible)], ['Libre sin usar', bytes(m.libre)],
        ['Caché de archivos', bytes(m.cache)], ['Búferes', bytes(m.buffers)], ['Compartida', bytes(m.compartida)],
        ['Pendiente de escribir', bytes(m.sucia)], ['Swap usada', bytes(swapUsada) + ' / ' + bytes(m.swap_total)],
      ]);
    }

    // Discos (uso)
    const raiz = d.discos.find((x) => x.montaje === '/') || d.discos[0];
    if (raiz) {
      const u = 100 - raiz.libre / raiz.total * 100;
      $('kDisco').textContent = pct(u); $('kDisco').style.color = color(u, 80, 90);
      $('kDiscoPie').textContent = `${bytes(raiz.libre)} libres de ${bytes(raiz.total)}`;
      barra('bDisco', u, 80, 90);
    }
    $('discosTabla').innerHTML = d.discos.map((x) => {
      const u = 100 - x.libre / x.total * 100;
      return `<tr><td class="mono">${esc(x.montaje)}</td><td class="mono muted">${esc(x.dispositivo)}</td><td class="muted">${esc(x.tipo)}</td>
        <td class="num">${bytes(x.total - x.libre)}</td><td class="num">${bytes(x.libre)}</td><td class="num">${bytes(x.total)}</td>
        <td><div class="barra" style="margin: 0;"><i style="width:${u}%; background:${color(u, 80, 90)}"></i></div><span class="mono muted" style="font-size: 10.5px;">${pct(u)}</span></td></tr>`;
    }).join('') || '<tr><td colspan="7" class="no-disp">Sin datos de almacenamiento.</td></tr>';

    // Disco (E/S)
    let leeT = 0, escT = 0;
    $('ioTabla').innerHTML = d.io.map((x) => {
      const a = previo && previo.io.find((y) => y.disco === x.disco);
      const lee = a && dt ? (x.sectores_leidos - a.sectores_leidos) * 512 / dt : null;
      const esc_ = a && dt ? (x.sectores_escritos - a.sectores_escritos) * 512 / dt : null;
      const ocupado = a && dt ? Math.min(100, (x.ms_ocupado - a.ms_ocupado) / (dt * 1000) * 100) : null;
      leeT += lee || 0; escT += esc_ || 0;
      sostenido.io[x.disco] = ocupado >= 90 ? (sostenido.io[x.disco] || 0) + dt : 0;
      if (sostenido.io[x.disco] >= 30) alertasCliente.push({ nivel: 'aviso', texto: `El disco ${x.disco} está ocupado al ${Math.round(ocupado)}% desde hace ${Math.round(sostenido.io[x.disco])} s.` });
      return `<tr><td class="mono">${esc(x.disco)}</td><td class="num">${lee == null ? '—' : porSeg(lee)}</td><td class="num">${esc_ == null ? '—' : porSeg(esc_)}</td>
        <td class="num" style="color:${color(ocupado || 0)}">${ocupado == null ? '—' : pct(ocupado, 0)}</td><td class="num">${x.en_curso}</td></tr>`;
    }).join('') || '<tr><td colspan="5" class="no-disp">Sin discos detectados.</td></tr>';
    if (previo) { empujar('gIo', [leeT, escT]); $('ioTotal').textContent = `↓ ${porSeg(leeT)} · ↑ ${porSeg(escT)}`; }

    // Red
    let rxT = 0, txT = 0;
    $('redTabla').innerHTML = d.red.map((x) => {
      const a = previo && previo.red.find((y) => y.iface === x.iface);
      const rx = a && dt ? (x.rx - a.rx) / dt : null;
      const tx = a && dt ? (x.tx - a.tx) / dt : null;
      rxT += rx || 0; txT += tx || 0;
      const errores = x.rx_errores + x.tx_errores + x.rx_descartados + x.tx_descartados;
      return `<tr><td class="mono">${esc(x.iface)}</td><td class="num">${rx == null ? '—' : porSeg(rx)}</td><td class="num">${tx == null ? '—' : porSeg(tx)}</td>
        <td class="num muted">${bytes(x.rx)}</td><td class="num muted">${bytes(x.tx)}</td><td class="num" style="color:${errores ? 'var(--ambar)' : 'inherit'}">${num(errores)}</td></tr>`;
    }).join('') || '<tr><td colspan="6" class="no-disp">Sin interfaces.</td></tr>';
    if (previo) {
      empujar('gRed', [rxT, txT]);
      $('kRed').textContent = `${porSeg(rxT)} / ${porSeg(txT)}`;
      $('redTotal').textContent = `↓ ${porSeg(rxT)} · ↑ ${porSeg(txT)}`;
    }
    $('kRedPie').textContent = `Total: ↓ ${bytes(d.red.reduce((s, x) => s + x.rx, 0))} · ↑ ${bytes(d.red.reduce((s, x) => s + x.tx, 0))}`;

    // Carga
    const c = d.carga;
    $('kCarga').textContent = c.uno.toFixed(2); $('kCarga').style.color = color(c.uno / nucleos * 100, 70, 100);
    $('kCargaPie').textContent = `5 min ${c.cinco.toFixed(2)} · 15 min ${c.quince.toFixed(2)}`;
    barra('bCarga', c.uno / nucleos * 100, 70, 100);
    $('cargaTexto').textContent = `${c.uno.toFixed(2)} · ${c.cinco.toFixed(2)} · ${c.quince.toFixed(2)}`;
    empujar('gCarga', [c.uno, c.cinco, c.quince, nucleos]);
    const ex = d.cpu.extra || {};
    const cambios = previo && dt ? ((ex.ctxt || 0) - (previo.cpu.extra?.ctxt || 0)) / dt : null;
    $('cargaDetalle').innerHTML = kv([
      ['Núcleos', nucleos], ['Procesos ejecutándose', num(ex.procs_running ?? c.ejecutando)], ['Procesos bloqueados (E/S)', num(ex.procs_blocked)],
      ['Cambios de contexto /s', cambios == null ? '—' : num(Math.round(cambios))], ['Procesos creados desde el arranque', num(ex.processes)],
    ]);

    // Encendido
    $('kUptime').textContent = duracion(d.uptime.segundos);
    $('kUptimePie').textContent = d.uptime.arranque ? `Desde ${d.uptime.arranque}` : '';

    // Temperatura
    if (d.temperaturas.length) {
      const max = d.temperaturas.reduce((a, t) => t.celsius > a.celsius ? t : a);
      $('kTemp').textContent = max.celsius.toFixed(0) + ' °C'; $('kTemp').style.color = color(max.celsius, 70, 85);
      $('kTempPie').textContent = max.nombre;
      $('temperaturas').innerHTML = `<table><thead><tr><th>Sensor</th><th class="num">Actual</th><th class="num">Crítica</th><th style="width: 35%;"></th></tr></thead><tbody>` +
        d.temperaturas.map((t) => `<tr><td>${esc(t.nombre)}</td><td class="num" style="color:${color(t.celsius, 70, 85)}">${t.celsius.toFixed(1)} °C</td><td class="num muted">${t.critica ? t.critica + ' °C' : '—'}</td>
          <td><div class="barra" style="margin: 0;"><i style="width:${Math.min(100, t.celsius / (t.critica || 100) * 100)}%; background:${color(t.celsius, 70, 85)}"></i></div></td></tr>`).join('') + '</tbody></table>';
    } else {
      $('kTemp').textContent = 'N/D'; $('kTempPie').textContent = 'Sin sensores';
      $('temperaturas').innerHTML = '<div class="no-disp">El servidor no expone sensores de temperatura. Es lo normal en un servidor virtual (VPS): la temperatura del hardware la controla el proveedor.</div>';
    }

    // Conexiones
    const cn = d.conexiones;
    empujar('gCon', [cn.establecidas, cn.web, cn.time_wait, cn.mysql]);
    $('conTotal').textContent = `${num(cn.total)} sockets`;
    $('conDetalle').innerHTML = kv([
      ['Establecidas', num(cn.establecidas)], ['Web (80/443)', num(cn.web)], ['Hacia/desde MySQL', num(cn.mysql)],
      ['Escuchando (puertos abiertos)', num(cn.escuchando)], ['TIME_WAIT', num(cn.time_wait)], ['CLOSE_WAIT', num(cn.close_wait)],
      ['Abriéndose (SYN)', num(cn.syn_enviado + cn.syn_recibido)],
    ]);

    // Nginx
    const n = d.nginx;
    if (n.disponible) {
      const rps = previo?.nginx?.disponible && dt ? (n.peticiones - previo.nginx.peticiones) / dt : null;
      $('nginxEstado').innerHTML = '<span class="chip verde">en línea</span>';
      if (rps != null) empujar('gNginx', [rps, n.activas]);
      $('nginxDetalle').innerHTML = kv([
        ['Peticiones /s', rps == null ? '—' : rps.toFixed(1)], ['Conexiones activas', num(n.activas)], ['Leyendo', num(n.leyendo)],
        ['Escribiendo', num(n.escribiendo)], ['En espera (keep-alive)', num(n.esperando)], ['Peticiones desde el arranque', num(n.peticiones)],
        ['Conexiones rechazadas', num(n.aceptadas - n.atendidas)],
      ]);
    } else {
      $('nginxEstado').innerHTML = '<span class="chip">sin datos</span>';
      $('nginxDetalle').innerHTML = `<div class="no-disp" style="grid-column: 1 / -1;">${esc(n.motivo)}</div>`;
    }

    // PHP-FPM
    const f = d.fpm;
    if (f.disponible) {
      $('fpmEstado').innerHTML = `<span class="chip ${f.cola > 0 ? 'rojo' : 'verde'}">${esc(f.pool || 'pool')}</span>`;
      empujar('gFpm', [f.activos, f.inactivos, f.cola]);
      const rps = previo?.fpm?.disponible && dt ? (f.aceptadas - previo.fpm.aceptadas) / dt : null;
      $('fpmDetalle').innerHTML = kv([
        ['Ocupados / total', `${f.activos} / ${f.total}`], ['Libres', num(f.inactivos)], ['Peticiones /s', rps == null ? '—' : rps.toFixed(1)],
        ['En cola ahora', `<span style="color:${f.cola ? 'var(--rojo)' : 'inherit'}">${num(f.cola)}</span>`], ['Cola máxima registrada', num(f.cola_max)],
        ['Máximo de ocupados', num(f.activos_max)], ['Veces que llegó al límite', `<span style="color:${f.limite_alcanzado ? 'var(--ambar)' : 'inherit'}">${num(f.limite_alcanzado)}</span>`],
        ['Peticiones lentas', num(f.lentas)], ['Gestor', esc(f.gestor)],
      ]);
    } else {
      $('fpmEstado').innerHTML = `<span class="chip">${num(d.procesos.procesos_fpm)} procesos</span>`;
      $('fpmDetalle').innerHTML = `<div class="no-disp" style="grid-column: 1 / -1;">${esc(f.motivo)} Mientras tanto se cuentan ${num(d.procesos.procesos_fpm)} procesos php-fpm.</div>`;
    }

    // MySQL
    const q = d.mysql;
    if (q.disponible) {
      const qps = previo?.mysql?.disponible && dt ? (q.Questions - previo.mysql.Questions) / dt : null;
      const lentas = previo?.mysql?.disponible ? q.Slow_queries - previo.mysql.Slow_queries : 0;
      const pool = q.Innodb_buffer_pool_pages_total ? (1 - q.Innodb_buffer_pool_pages_free / q.Innodb_buffer_pool_pages_total) * 100 : null;
      const aciertos = q.Innodb_buffer_pool_read_requests ? (1 - q.Innodb_buffer_pool_reads / q.Innodb_buffer_pool_read_requests) * 100 : null;
      const usoCon = q.max_conexiones ? q.Threads_connected / q.max_conexiones * 100 : 0;
      $('mysqlEstado').innerHTML = `<span class="chip verde">${esc(q.version)}</span>`;
      if (qps != null) empujar('gMysql', [qps, q.Threads_connected, q.Threads_running]);
      $('mysqlDetalle').innerHTML = kv([
        ['Consultas /s', qps == null ? '—' : qps.toFixed(1)],
        ['Conexiones', `<span style="color:${color(usoCon, 60, 80)}">${q.Threads_connected} / ${q.max_conexiones}</span>`],
        ['Ejecutando ahora', num(q.Threads_running)], ['Máximo de conexiones usado', num(q.Max_used_connections)],
        ['Consultas lentas (total)', `${num(q.Slow_queries)}${lentas > 0 ? ` <span style="color:var(--ambar)">+${lentas}</span>` : ''}`],
        ['Buffer pool ocupado', pct(pool)], ['Lecturas desde memoria', pct(aciertos, 2)],
        ['Bloqueos de filas / deadlocks', `${num(q.Innodb_row_lock_waits)} / ${num(q.Innodb_deadlocks)}`],
        ['Tablas temporales en disco', num(q.Created_tmp_disk_tables)], ['Conexiones fallidas', num(q.Aborted_connects)],
        ['Tamaño de la base', bytes(q.tamano)], ['Encendido', duracion(q.Uptime)],
      ]);
    } else {
      $('mysqlEstado').innerHTML = '<span class="chip">sin datos</span>';
      $('mysqlDetalle').innerHTML = `<div class="no-disp" style="grid-column: 1 / -1;">${esc(q.motivo)}</div>`;
    }

    // Aplicación
    const a = d.app;
    $('kLinea').textContent = a.usuarios_en_linea ?? '—';
    $('kLineaPie').textContent = a.sesiones_activas != null ? `${num(a.sesiones_activas)} sesiones · últimos 5 min` : 'Sesiones fuera de la base';
    $('appDetalle').innerHTML = kv([
      ['Usuarios en línea (5 min)', num(a.usuarios_en_linea)], ['Sesiones activas (5 min)', num(a.sesiones_activas)],
      ['Casos de crisis abiertos', `<span style="color:${a.casos_abiertos ? 'var(--rojo)' : 'inherit'}">${num(a.casos_abiertos)}</span>`],
      ['Casos sin contacto humano', `<span style="color:${a.casos_sin_contacto ? 'var(--rojo)' : 'inherit'}">${num(a.casos_sin_contacto)}</span>`],
      ['Registros diarios de hoy', num(a.registros_hoy)],
      ['Trabajos en cola / fallidos', `${num(a.jobs_pendientes)} / <span style="color:${a.jobs_fallidos ? 'var(--ambar)' : 'inherit'}">${num(a.jobs_fallidos)}</span>`],
      ['Trabajadores de la cola', `<span style="color:${a.trabajadores_cola ? 'inherit' : 'var(--rojo)'}">${num(a.trabajadores_cola)}</span>`],
      ['OPcache (memoria / aciertos)', a.opcache ? `${pct(a.opcache.memoria_pct)} / ${pct(a.opcache.aciertos_pct)}` : 'Desactivado'],
    ]);

    // Procesos
    pintarProcesos(d, dt, m.total);

    // Alertas
    const todas = [...(d.alertas || []), ...alertasCliente].sort((x, y) => (x.nivel === 'critica' ? 0 : 1) - (y.nivel === 'critica' ? 0 : 1));
    const criticas = todas.filter((x) => x.nivel === 'critica').length;
    $('conteoAlertas').innerHTML = todas.length ? `${criticas ? `<span class="chip rojo">${criticas} críticas</span> ` : ''}<span class="chip">${todas.length} en total</span>` : '<span class="chip verde">todo en orden</span>';
    $('alertas').innerHTML = todas.length
      ? todas.map((x) => `<div class="alerta ${x.nivel}"><span class="ico">${x.nivel === 'critica' ? '●' : '▲'}</span><span>${esc(x.texto)}</span></div>`).join('')
      : '<div class="no-disp ok">Sin alertas: el servidor está trabajando dentro de lo normal.</div>';

    Object.values(graficas).forEach((g) => g.update('none'));
    previo = d;
  }

  // Última lista calculada: filtrar u ordenar la reutiliza sin perder el % de CPU.
  let procesosCalculados = { filas: [], nucleos: 1, ramTotal: 0 };

  function pintarProcesos(d, dt, ramTotal) {
    const p = d.procesos;
    const antes = new Map((previo?.procesos?.lista || []).map((x) => [x[0], x[4]]));
    procesosCalculados = {
      nucleos: Math.max(1, d.host.nucleos || 1),
      ramTotal,
      filas: p.lista.map((x) => ({
        pid: x[0], nombre: x[1], usuario: x[2], estado: x[3], ram: x[5],
        cpu: dt && antes.has(x[0]) ? Math.max(0, (x[4] - antes.get(x[0])) / p.ticks_por_segundo / dt * 100) : null,
      })),
    };

    $('procTotal').textContent = `${num(p.total)} procesos · ${num(p.hilos)} hilos`;
    $('procEstados').textContent = Object.entries(p.estados).map(([e, n]) => `${ESTADO_PROC[e] || e}: ${n}`).join(' · ');
    tablaProcesos();
  }

  function tablaProcesos() {
    const { filas: todas, nucleos, ramTotal } = procesosCalculados;
    const filtro = $('procFiltro').value.trim().toLowerCase();
    let filas = filtro
      ? todas.filter((x) => x.nombre.toLowerCase().includes(filtro) || String(x.usuario).toLowerCase().includes(filtro) || String(x.pid) === filtro)
      : [...todas];
    filas.sort((x, y) => ordenProc === 'cpu' ? ((y.cpu ?? -1) - (x.cpu ?? -1)) || (y.ram - x.ram) : y.ram - x.ram);

    $('procTabla').innerHTML = filas.slice(0, 60).map((x) => `<tr>
      <td class="num muted">${x.pid}</td><td class="mono">${esc(x.nombre)}</td><td class="muted">${esc(x.usuario)}</td>
      <td>${x.estado === 'Z' ? '<span class="chip rojo">Zombi</span>' : esc(ESTADO_PROC[x.estado] || x.estado)}</td>
      <td class="num" style="color:${x.cpu != null ? color(x.cpu / nucleos) : 'inherit'}">${x.cpu == null ? '—' : x.cpu.toFixed(1) + '%'}</td>
      <td class="num">${bytes(x.ram)}</td><td class="num muted">${ramTotal ? pct(x.ram / ramTotal * 100) : '—'}</td></tr>`).join('');
  }

  // ─── Ciclo de lectura ───
  async function leer() {
    if (pausado) return;
    try {
      const r = await fetch(RUTA, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      if (r.status === 401 || r.status === 404) {
        // Sesión vencida o la cuenta ya no es de administración: se deja de consultar.
        pausado = true;
        $('punto').classList.add('caido');
        $('pausa').disabled = true;
        $('actualizado').innerHTML = 'Sesión terminada · <a href="" style="color: var(--verde);">vuelve a entrar</a>';
        return;
      }
      if (!r.ok) throw new Error('HTTP ' + r.status);
      const d = await r.json();
      pintar(d);
      $('punto').classList.remove('caido');
      $('actualizado').textContent = 'actualizado ' + new Date().toLocaleTimeString('es-MX');
    } catch (e) {
      $('punto').classList.add('caido');
      $('actualizado').textContent = 'sin respuesta (' + e.message + ')';
    } finally {
      programar();
    }
  }

  function programar() {
    clearTimeout(temporizador);
    if (!pausado) temporizador = setTimeout(leer, document.hidden ? Math.max(intervalo, 10000) : intervalo);
  }

  $('intervalo').addEventListener('change', (e) => { intervalo = parseInt(e.target.value, 10); programar(); });
  $('pausa').addEventListener('click', (e) => {
    pausado = !pausado;
    e.target.textContent = pausado ? 'Reanudar' : 'Pausar';
    if (!pausado) leer();
  });
  document.querySelectorAll('[data-orden]').forEach((b) => b.addEventListener('click', () => {
    ordenProc = b.dataset.orden;
    document.querySelectorAll('[data-orden]').forEach((x) => x.classList.toggle('on', x === b));
    tablaProcesos();
  }));
  $('procFiltro').addEventListener('input', tablaProcesos);

  leer();
})();
</script>
</body>
</html>
