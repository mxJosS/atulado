<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Documento Oficial &middot; A Tu Lado</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600..700;1,9..144,400&family=IBM+Plex+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('vendor/paneles/reporte.css') }}?v={{ time() }}">
</head>
@if(!$institution)
<body style="font-family: 'Manrope', sans-serif; background: #F8FAF9; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
  <div style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 16px; padding: 3.5rem 2rem; max-width: 560px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
      <i class="fa-solid fa-file-circle-xmark"></i>
    </div>
    <h2 style="font-family: 'Fraunces', serif; color: #1A2620; font-size: 1.45rem; font-weight: 700; margin: 0 0 0.5rem;">No hay instituciones registradas</h2>
    <p style="color: #6E887E; font-size: 0.92rem; line-height: 1.5; margin: 0 0 1.5rem;">Para visualizar, emitir e imprimir reportes foliados (ejecutivos, clínicos o constancias NOM-035), primero registra una organización en la plataforma.</p>
    <a href="{{ route('admin.institutions.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #2E5D4B; color: #FFFFFF; padding: 0.65rem 1.25rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.88rem;">
      <i class="fa-solid fa-building-shield"></i> Ir a Organizaciones / Registrar
    </a>
  </div>
</body>
@else
<body class="doc-mode">

<div class="emit-bar">
  <a class="brand" href="{{ route('admin.reports.index') }}" style="color:inherit; text-decoration:none;">
    <svg viewBox="0 0 16 16" width="20" height="20">
      <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
      <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
      <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
      <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
      <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
      <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
      <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
      <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
      <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
    </svg>
    <span>a tu <em class="editorial-italic" style="color:var(--mint-accent)">lado</em></span>
  </a>
  <div class="sep"></div>
  <div>
    <div style="font-size:.85rem;font-weight:700" id="bar-nombre">—</div>
    <div class="meta" id="bar-meta">—</div>
  </div>
  <div class="spacer"></div>
  <span class="chip" id="bar-audiencia" style="background:transparent;border-color:rgba(255,255,255,.2);color:var(--text-pale-mint)">—</span>
  <span class="meta" id="bar-pags">—</span>
  <button class="btn btn-sm" id="btn-otro" type="button"><i class="fa-solid fa-arrow-left"></i> Volver a reportes</button>
  <button class="btn btn-sm btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Descargar PDF / Imprimir</button>
</div>

<div class="emit-note" id="emit-note"></div>

<div class="sheets" id="sheets"></div>

<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ time() }}"></script>
<script src="{{ asset('vendor/paneles/reportes-data.js') }}?v={{ time() }}"></script>
<script>
// Inyección dinámica de la institución evaluada desde la base de datos real
INSTITUCIONES['{{ $institution->slug }}'] = {
  nombre: @json($institution->name, JSON_UNESCAPED_UNICODE),
  corto: @json($institution->short_name ?: $institution->name, JSON_UNESCAPED_UNICODE),
  rfc: @json($institution->rfc ?: 'RFC NO REGISTRADO', JSON_UNESCAPED_UNICODE),
  sector: @json($institution->category, JSON_UNESCAPED_UNICODE),
  ciudad: @json($institution->city ?: '', JSON_UNESCAPED_UNICODE),
  padron: {{ ($analytics['total_users'] ?? 0) > 0 ? $analytics['total_users'] : ($institution->users_count ?: 0) }},
  activos: {{ ($analytics['active_count'] ?? 0) > 0 ? $analytics['active_count'] : ($institution->active_count ?: 0) }},
  contacto: @json($institution->contact_name . ($institution->contact_position ? ' — ' . $institution->contact_position : ''), JSON_UNESCAPED_UNICODE),
  profesional: @json($institution->professional_name ?: 'Sin profesional asignado', JSON_UNESCAPED_UNICODE),
  cedula: @json($institution->professional_license ?: 'En trámite', JSON_UNESCAPED_UNICODE),
  vigencia: @json($institution->renewal_date ? $institution->renewal_date->format('d/m/Y') : '14/02/2027'),
  alta: @json($institution->created_at ? $institution->created_at->format('d/m/Y') : now()->format('d/m/Y')),
  ibi: {{ $analytics['ibi'] ?? 0 }},
  irc: {{ $analytics['irc'] ?? 0 }},
  iro: {{ $analytics['iro'] ?? 0 }}
};

/* ════════════════════════════════════════════════════════════════
   MOTOR DE EMISIÓN
   Lee los parámetros de la URL o Blade, arma el contexto, pide las hojas
   a la plantilla y las envuelve con encabezado, pie y marca de agua.
   ════════════════════════════════════════════════════════════════ */

const P = new URLSearchParams(location.search);
const tipo = P.get('r') || '{{ $reportType ?? "ejecutivo" }}';
const rep = REPORTES[tipo] || REPORTES.ejecutivo;

const instParam = P.get('inst') || '{{ $institution->slug }}';
const inst = INSTITUCIONES[instParam] || INSTITUCIONES['{{ $institution->slug }}'];

/* Folio: el que venga en la URL o uno nuevo, estable por sesión */
function nuevoFolio(pref) {
  const n = String(Math.floor(Math.random() * 9000) + 1000);
  return `${pref}-2026-${n}`;
}
const folio = P.get('folio') || '{{ $folio ?? "" }}' || nuevoFolio(rep.prefijo);

const HOY = new Date();
const dosDig = n => String(n).padStart(2, '0');
const fechaLarga = `${dosDig(HOY.getDate())}/${dosDig(HOY.getMonth() + 1)}/${HOY.getFullYear()}`;
const horaCorta = `${dosDig(HOY.getHours())}:${dosDig(HOY.getMinutes())}`;

const DESTINATARIOS = {
  contacto: inst.contacto,
  direccion: 'Dirección General — ' + inst.corto,
  profesional: `${inst.profesional} · Ced. ${inst.cedula}`,
  guardia: 'Psic. Daniela Mena · Ced. 8004112 (guardia A Tu Lado)',
  direccion_atl: 'Dirección — A Tu Lado'
};
const destKey = P.get('dest') || '{{ $dest ?? "" }}' || rep.destinatarios[0];
const destinatario = DESTINATARIOS[destKey] || DESTINATARIOS.contacto;

const ctx = {
  inst, folio,
  periodo: P.get('periodo') || '{{ $period ?? "" }}' || PERIODO_DEF,
  fecha: fechaLarga,
  hora: horaCorta,
  emisor: EMISOR_DEF,
  destinatario,
  destinatarioCorto: destinatario.split(' ·')[0].split(' — ')[0]
};

/* ── Etiquetas de audiencia ─────────────────────────────────── */
const AUD = {
  institucion: { t: 'Apto para la institución', i: 'fa-building' },
  profesional: { t: 'Confidencial · profesional designado', i: 'fa-user-lock' },
  interno: { t: 'Uso interno de A Tu Lado', i: 'fa-shield-halved' }
};

/* ── Construcción de hojas ──────────────────────────────────── */
const cuerpos = rep.render(ctx);
const total = cuerpos.length;

function encabezadoPrimero() {
  if (rep.sinEncabezado) return '';
  return `<div class="sh-head">
    <svg viewBox="0 0 16 16" width="30" height="30" style="flex:none"><rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/><rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/><rect x="2" y="4" width="12" height="2" fill="#5AB56E"/><rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/><rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/><rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/><rect x="4" y="1" width="1" height="1" fill="#C0392B"/><rect x="11" y="3" width="1" height="1" fill="#C0392B"/><rect x="9" y="7" width="1" height="1" fill="#C0392B"/></svg>
    <div>
      <h2 class="tt">${rep.nombre}</h2>
      <div class="st">${inst.nombre} · ${ctx.periodo}</div>
    </div>
    <div class="fo">
      Folio <b>${folio}</b><br>
      Emitido ${ctx.fecha} ${ctx.hora}<br>
      Por ${ctx.emisor}<br>
      Para ${ctx.destinatario}
    </div>
  </div>`;
}

function encabezadoCont(i) {
  if (rep.sinEncabezado) return '';
  return `<div class="sh-cont">
    <span><b>${rep.nombre}</b> · ${inst.corto}</span>
    <span class="spacer">${folio}</span>
  </div>`;
}

const marcaFooter = rep.confidencial
  ? 'DOCUMENTO CONFIDENCIAL — no reproducir'
  : (rep.audiencia === 'interno' ? 'USO INTERNO A TU LADO' : 'A Tu Lado · acompañamiento emocional');

function marcaAgua() {
  if (!rep.confidencial) return '';
  return `<div class="wm"><span>CONFIDENCIAL<br>${ctx.destinatarioCorto.toUpperCase()}<br>${folio}</span></div>`;
}

/* ════════════════════════════════════════════════════════════════
   PAGINACIÓN MEDIDA
   Las plantillas describen contenido, no páginas. El motor mide
   bloque por bloque contra el alto útil real de la hoja y decide
   dónde cae cada salto, para que el pie y la numeración
   correspondan a las páginas que realmente se imprimen.
   ════════════════════════════════════════════════════════════════ */

const cont = document.getElementById('sheets');
const hojas = [];

function nuevaHoja() {
  const s = document.createElement('section');
  s.className = 'sheet';
  s.innerHTML = `${marcaAgua()}
    ${hojas.length === 0 ? encabezadoPrimero() : encabezadoCont()}
    <div class="sheet-body"></div>
    <div class="sh-foot"></div>`;
  cont.appendChild(s);
  hojas.push(s);

  // Alto útil = caja de contenido de la hoja menos el encabezado.
  const body = s.querySelector('.sheet-body');
  const cs = getComputedStyle(s);
  const interior = s.getBoundingClientRect().height
    - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom);
  const enc = s.querySelector('.sh-head, .sh-cont');
  let altoEnc = 0;
  if (enc) {
    const ecs = getComputedStyle(enc);
    altoEnc = enc.getBoundingClientRect().height + parseFloat(ecs.marginBottom);
  }
  body.style.height = Math.max(0, Math.floor(interior - altoEnc)) + 'px';
  return body;
}

const desborda = b => b.scrollHeight > b.clientHeight + 1;

/* Si una tabla no cabe entera, se parte y la continuación
   repite el encabezado en la hoja siguiente. */
function partirTabla(nodo, body) {
  if (!desborda(body)) return null;
  if (nodo.tagName !== 'TABLE' || !nodo.tHead) return null;
  const tb = nodo.tBodies[0];
  if (!tb || tb.rows.length < 3) return null;
  const sobra = [];
  while (desborda(body) && tb.rows.length > 2) {
    sobra.unshift(tb.rows[tb.rows.length - 1]);
    tb.deleteRow(tb.rows.length - 1);
  }
  if (!sobra.length) return null;
  const sig = nodo.cloneNode(false);
  sig.appendChild(nodo.tHead.cloneNode(true));
  const nb = document.createElement('tbody');
  sobra.forEach(r => nb.appendChild(r));
  sig.appendChild(nb);
  return sig;
}

const esTitulo = n => n && n.tagName === 'H3' && n.classList.contains('sec');

function paginar(nodos) {
  let body = nuevaHoja();
  for (const nodo of nodos) {
    if (nodo.classList && nodo.classList.contains('hardbreak')) { body = nuevaHoja(); continue; }
    body.appendChild(nodo);
    if (!desborda(body)) continue;

    if (body.children.length > 1) {
      body.removeChild(nodo);
      // un título nunca se queda huérfano al pie de la hoja
      const arrastra = esTitulo(body.lastElementChild) ? body.lastElementChild : null;
      if (arrastra) body.removeChild(arrastra);
      body = nuevaHoja();
      if (arrastra) body.appendChild(arrastra);
      body.appendChild(nodo);
    }
    // aún solo en la hoja y sin caber: se parte si es tabla
    let resto = partirTabla(nodo, body);
    while (resto) {
      body = nuevaHoja();
      body.appendChild(resto);
      const siguiente = partirTabla(resto, body);
      if (siguiente === resto) break;
      resto = siguiente;
    }
  }
}

function pintarPies() {
  const total = hojas.length;
  hojas.forEach((h, i) => {
    h.querySelector('.sh-foot').innerHTML =
      `<span>${marcaFooter}</span><span class="spacer">${folio}</span><span>Página ${i + 1} de ${total}</span>`;
  });
  document.getElementById('bar-pags').textContent = `${total} ${total === 1 ? 'página' : 'páginas'}`;
}

/* ── Construcción ────────────────────────────────────────────── */
async function construir() {
  try { await Promise.race([document.fonts.ready, new Promise(r => setTimeout(r, 2500))]); } catch (e) {}

  const stage = document.createElement('div');
  stage.className = 'stage sheet';
  stage.setAttribute('style',
    'position:absolute;left:-12000px;top:0;display:block;height:auto;min-height:0;' +
    'padding:0;box-shadow:none;overflow:visible;width:calc(8.5in - 32mm);');
  stage.innerHTML = cuerpos.join('');
  document.body.appendChild(stage);

  if (typeof rep.charts === 'function') {
    try { rep.charts(); } catch (e) { console.error('Error dibujando gráficas:', e); }
  }

  const nodos = Array.from(stage.children);
  paginar(nodos);
  stage.remove();
  pintarPies();
}

/* ── Barra superior ──────────────────────────────────────────── */
document.title = `${rep.nombre} · ${folio}`;
document.getElementById('bar-nombre').textContent = rep.nombre;
document.getElementById('bar-meta').innerHTML =
  `${inst.corto} · <b>${folio}</b> · ${ctx.periodo}`;
const a = AUD[rep.audiencia];
document.getElementById('bar-audiencia').innerHTML = `<i class="fa-solid ${a.i}"></i> ${a.t}`;
document.getElementById('bar-pags').textContent = 'paginando…';
document.getElementById('btn-otro').onclick = () => window.location.href = "{{ route('admin.reports.index') }}";

construir();

/* ── Nota de emisión ─────────────────────────────────────────── */
const notas = {
  institucion: `<b>Destinatario:</b> ${ctx.destinatario}. Este documento contiene exclusivamente proporciones del total de la organización: puede entregarse a la institución. Enlace permanente.`,
  profesional: `<b>Destinatario:</b> ${ctx.destinatario}. Documento confidencial con marca de agua nominal y folio. El enlace expira 72 horas después de la emisión; el acceso queda asentado en la bitácora.`,
  interno: `<b>Destinatario:</b> ${ctx.destinatario}. Uso interno de A Tu Lado: incluye conteos, desglose por institución e información comercial. No se comparte con instituciones ni con profesionales designados.`
};
document.getElementById('emit-note').innerHTML =
  `<i class="fa-solid fa-circle-info"></i> ${notas[rep.audiencia]}`;
</script>
</body>
@endif
</html>
