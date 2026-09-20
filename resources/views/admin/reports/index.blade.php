@extends('layouts.admin')

@section('title', 'Centro de Reportes y Entregables')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
<style>
  .admin-content-canvas {
    padding: 1.75rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
  }
  @media (max-width: 768px) {
    .admin-content-canvas {
      padding: 1rem;
    }
  }
</style>
@endpush

@section('content')
<div class="admin-content-canvas">

  <!-- Breadcrumbs & Quick Filters -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        Consola A Tu Lado &rsaquo; <b>Centro de Reportes</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        Centro de Reportes & Entregables
      </h1>
      <p style="font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; max-width: 860px;">
        Cada documento se emite con folio único irremplazable, marca de agua nominal y vigencia estricta. Los marcados en <b>verde</b> son aptos para la institución; los marcados en <b>rojo</b> pertenecen al expediente clínico confidencial con protocolo activo.
      </p>
    </div>

    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
      <select class="select" style="width:auto" id="f-inst">
        @forelse($institutions as $iSlug => $iName)
          <option value="{{ $iSlug }}">{{ $iName }}</option>
        @empty
          <option value="">Sin instituciones registradas</option>
        @endforelse
      </select>
      <select class="select" style="width:auto" id="f-periodo">
        <option>15/06/2026 – 14/09/2026</option>
        <option>01/09/2026 – 14/09/2026</option>
        <option>01/01/2026 – 14/09/2026</option>
      </select>
      <button class="btn btn-sm" type="button"><i class="fa-solid fa-clock"></i> Programar envíos</button>
    </div>
  </div>

  <!-- Navegación por pestañas -->
  <div class="tabs" data-tabs="rep">
    <button type="button" class="tab on" data-pane="catalogo">Catálogo y emisión</button>
    <button type="button" class="tab" data-pane="metricas">Qué contiene cada reporte</button>
    <button type="button" class="tab" data-pane="historial">Emisiones y entregas</button>
  </div>

  <!-- ════════════ PESTAÑA: CATÁLOGO ════════════ -->
  <div class="tabpane on" data-tabpane="rep" data-pane="catalogo">
    <div class="alert info mb-3">
      <div class="ico"><i class="fa-solid fa-file-pdf"></i></div>
      <div class="txt">
        <b>Los 13 documentos están armados, foliados y son 100% imprimibles</b>
        <span>«Ver» abre el documento paginado en tamaño carta tal como saldrá impreso. «Emitir» pasa primero por las validaciones de destinatario, motivo y vigencia.</span>
      </div>
    </div>

    <div id="cat-institucion"></div>
    <div id="cat-profesional" class="mt-4"></div>
    <div id="cat-interno" class="mt-4"></div>

    <div class="card mt-4 mb-3">
      <div class="card-head"><h3>Reglas de seguridad y diseño que aplican a todos los PDF</h3></div>
      <div class="card-body grid g-4">
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-hashtag"></i> Folio único</div>
          <p class="small muted mb-0">Cada emisión genera un folio irrepetible ligado a quién la solicitó y por qué motivo.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-droplet"></i> Marca de agua nominal</div>
          <p class="small muted mb-0">Los confidenciales llevan el nombre del destinatario en diagonal en cada página.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-hourglass-half"></i> Enlace con vigencia</div>
          <p class="small muted mb-0">72 horas por omisión. Al vencer deja de resolver, protegiendo el eslabón digital.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-scale-balanced"></i> Leyenda de uso</div>
          <p class="small muted mb-0">Prohibición expresa de uso para evaluaciones, sanciones, despidos o discriminación.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-triangle-exclamation"></i> Aviso de alcance</div>
          <p class="small muted mb-0">«A Tu Lado no diagnostica ni sustituye a un profesional de la salud» presente en todos.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-book-open"></i> Metodología</div>
          <p class="small muted mb-0">Fórmula de cada índice y el instrumento clínico del que proviene cada indicador.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-file-lines"></i> Paginación y pie</div>
          <p class="small muted mb-0">Cada hoja lleva folio y «página N de M», para detectar folios mutilados o incompletos.</p>
        </div>
        <div>
          <div class="kpi-label mb-1"><i class="fa-solid fa-universal-access"></i> Legibilidad accesible</div>
          <p class="small muted mb-0">Toda gráfica lleva su tabla de datos asociada; ningún indicador depende únicamente del color.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ════════════ PESTAÑA: MÉTRICAS ════════════ -->
  <div class="tabpane" data-tabpane="rep" data-pane="metricas">
    <div class="card mb-3">
      <div class="card-head">
        <h3>Qué contiene cada reporte</h3>
        <span class="spacer section-note">Matriz de métricas por documento y audiencia</span>
      </div>
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th style="min-width:230px">Métrica</th>
              <th class="center">Ejecutivo<br><span class="xsmall muted">institución</span></th>
              <th class="center">NOM-035<br><span class="xsmall muted">institución</span></th>
              <th class="center">Clínico<br><span class="xsmall muted">profesional</span></th>
              <th class="center">Bitácora<br><span class="xsmall muted">seudónimo</span></th>
              <th class="center">Plataforma<br><span class="xsmall muted">interno</span></th>
            </tr>
          </thead>
          <tbody>
            <tr><td>IBI y su composición</td><td class="center">●</td><td class="center">—</td><td class="center">—</td><td class="center">—</td><td class="center">●</td></tr>
            <tr><td>Distribución del semáforo en %</td><td class="center">●</td><td class="center">●</td><td class="center">—</td><td class="center">—</td><td class="center">●</td></tr>
            <tr><td>Conteos absolutos de personas</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">●</td><td class="center">●</td></tr>
            <tr><td>Desglose por departamento</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">●</td><td class="center">●</td></tr>
            <tr><td>Adopción y constancia de uso</td><td class="center">●</td><td class="center">●</td><td class="center">—</td><td class="center">—</td><td class="center">●</td></tr>
            <tr><td>Cumplimiento de SLA</td><td class="center">●</td><td class="center">●</td><td class="center">●</td><td class="center">●</td><td class="center">●</td></tr>
            <tr><td>Hallazgos y recomendaciones</td><td class="center">●</td><td class="center">—</td><td class="center">—</td><td class="center">—</td><td class="center">●</td></tr>
            <tr class="row-red"><td>Nombre de la persona</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">—</td><td class="center">—</td></tr>
            <tr class="row-red"><td>Respuestas ítem por ítem</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">—</td><td class="center">—</td></tr>
            <tr class="row-red"><td>Texto libre de la persona</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">—</td><td class="center">—</td></tr>
            <tr class="row-red"><td>Plan de seguridad y contactos</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">—</td><td class="center">—</td></tr>
            <tr><td>Bitácora de accesos con motivo</td><td class="center">—</td><td class="center">—</td><td class="center">—</td><td class="center">●</td><td class="center">●</td></tr>
            <tr><td>Perfil estadístico anonimizado</td><td class="center">—</td><td class="center">—</td><td class="center">—</td><td class="center">—</td><td class="center">●</td></tr>
          </tbody>
        </table>
      </div>
      <div class="card-foot">Esta matriz es la especificación de generación: el motor construye cada PDF a partir de una consulta distinta, no filtrando un mismo conjunto de datos. Un dato marcado «—» jamás llega a la plantilla.</div>
    </div>
  </div>

  <!-- ════════════ PESTAÑA: HISTORIAL ════════════ -->
  <div class="tabpane" data-tabpane="rep" data-pane="historial">
    <div class="toolbar mb-3">
      <div class="input-icon" style="min-width:260px">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input class="input" placeholder="Buscar por folio o destinatario…" id="hist-search">
      </div>
      <select class="select" style="width:auto">
        <option>Todos los tipos</option>
        <option>Confidenciales</option>
        <option>Institucionales</option>
        <option>Internos</option>
      </select>
      <div class="spacer"></div>
      <span class="small muted">Retención oficial de emisiones: 5 años</span>
    </div>

    <div class="card mb-3">
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th>Folio</th>
              <th>Reporte</th>
              <th>Institución</th>
              <th>Emitido por</th>
              <th>Destinatario</th>
              <th>Motivo</th>
              <th>Enlace</th>
              <th class="center">Abierto</th>
              <th class="actions">Acciones</th>
            </tr>
          </thead>
          <tbody id="hist-body"></tbody>
        </table>
      </div>
      <div class="card-foot">Cada emisión de un documento confidencial que no haya sido abierto por su destinatario en 24 horas genera un recordatorio directo y, a las 72 horas, una anotación preventiva en la bitácora del caso.</div>
    </div>
  </div>

</div>

<!-- ══════════════ MODAL DE EMISIÓN ══════════════ -->
<div class="modal-backdrop" id="m-emitir">
  <div class="modal narrow">
    <div class="modal-head" id="em-head">
      <div>
        <h2 id="em-titulo">Emitir documento</h2>
        <div class="sub" id="em-sub">—</div>
      </div>
      <button class="x" type="button" data-close><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div id="em-aviso"></div>

      <div class="field mb-2">
        <label>Institución</label>
        <select class="select" id="em-inst">
          @forelse($institutions as $iSlug => $iName)
            <option value="{{ $iSlug }}">{{ $iName }}</option>
          @empty
            <option value="">Sin instituciones registradas</option>
          @endforelse
        </select>
      </div>

      <div class="field mb-2">
        <label>Periodo</label>
        <select class="select" id="em-periodo">
          <option>Últimos 90 días (recomendado)</option>
          <option>Mes en curso</option>
          <option>Año acumulado</option>
        </select>
      </div>

      <div class="field mb-2" id="em-persona-wrap" hidden>
        <label>Persona (Protocolo Activo)</label>
        <select class="select" id="em-persona">
          <option disabled selected>Sin protocolos de crisis activos</option>
        </select>
        <span class="hint">Sólo se listan colaboradores con protocolo de crisis activo verificado. Fuera de un protocolo, la emisión de datos individuales no procede.</span>
      </div>

      <div class="field mb-2">
        <label>Destinatario</label>
        <select class="select" id="em-dest"></select>
        <span class="hint" id="em-dest-hint"></span>
      </div>

      <div class="field mb-2" id="em-motivo-wrap" hidden>
        <label>Motivo de la emisión</label>
        <select class="select" id="em-motivo">
          <option>Protocolo de crisis activo (CR-2026-0117)</option>
          <option>Seguimiento de caso ya abierto</option>
          <option>Canalización a servicio externo</option>
        </select>
        <span class="hint">El motivo queda asentado en la bitácora junto al folio. Sin motivo, la emisión no se completa.</span>
      </div>

      <div class="field mb-2" id="em-alcance-wrap" hidden>
        <label>Alcance del documento</label>
        <div class="card card-pad" style="background:var(--bg-subtler)">
          <div class="switch-row"><button type="button" class="switch on"></button><div class="txt"><b>Trayectoria del ánimo y reglas disparadas</b></div></div>
          <div class="switch-row"><button type="button" class="switch on"></button><div class="txt"><b>Respuestas WHO-5, MDI y ASQ ítem por ítem</b></div></div>
          <div class="switch-row"><button type="button" class="switch on"></button><div class="txt"><b>Texto libre con contexto de las banderas</b></div></div>
          <div class="switch-row"><button type="button" class="switch on"></button><div class="txt"><b>Plan de seguridad y contactos de emergencia</b></div></div>
          <div class="switch-row"><button type="button" class="switch"></button><div class="txt"><b>Historial completo de 12 meses</b><span>Amplía el documento a ~14 páginas.</span></div></div>
        </div>
      </div>

      <div class="field mb-2" id="em-vigencia-wrap" hidden>
        <label>Vigencia del enlace</label>
        <select class="select">
          <option>72 horas (recomendado)</option>
          <option>24 horas</option>
          <option>7 días</option>
        </select>
        <span class="hint">Al vencer, el enlace deja de resolver. El PDF descargado conserva folio y marca de agua.</span>
      </div>

      <div class="card card-pad" style="background:var(--bg-subtler)">
        <div class="kpi-label mb-1"><i class="fa-solid fa-hashtag"></i> Folio que se asignará</div>
        <div class="mono strong" id="em-folio" style="font-size:1.05rem; color: #2E5D4B;">—</div>
        <div class="xsmall muted mt-1">Se genera al confirmar y queda ligado a esta emisión de forma permanente.</div>
      </div>
    </div>

    <div class="modal-foot">
      <span class="small muted" id="em-pie"></span>
      <div class="spacer"></div>
      <button class="btn" type="button" data-close>Cancelar</button>
      <button class="btn btn-primary" type="button" id="em-go"><i class="fa-solid fa-file-pdf"></i> Generar documento</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  window.INSTITUCIONES = @json($institutionsData ?? []);
</script>
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ time() }}"></script>
<script src="{{ asset('vendor/paneles/reportes-data.js') }}?v={{ time() }}"></script>
<script>
const VIEWER_BASE_URL = "{{ route('admin.reports.viewer') }}";

/* ════════ Catálogo generado desde la definición de los reportes ════════ */
const GRUPOS = [
  { key: 'institucion', host: 'cat-institucion', titulo: 'Para la institución',
    nota: 'Sólo porcentajes · sin identidad, sin conteos, sin desglose por área' },
  { key: 'profesional', host: 'cat-profesional', titulo: 'Para el profesional designado',
    nota: 'Con identidad · sólo con protocolo de crisis activo y contrato de confidencialidad vigente' },
  { key: 'interno', host: 'cat-interno', titulo: 'Para A Tu Lado',
    nota: 'Uso interno · comercial, producto e investigación' }
];

function etiquetas(r) {
  const out = [];
  if (r.audiencia === 'institucion') out.push('<span class="chip green"><i class="fa-solid fa-building"></i> Institución</span>');
  if (r.audiencia === 'profesional') out.push(r.seudonimo
    ? '<span class="chip amber"><i class="fa-solid fa-user-secret"></i> Seudonimizado</span>'
    : '<span class="chip red"><i class="fa-solid fa-lock"></i> Confidencial</span>');
  if (r.audiencia === 'interno') out.push('<span class="chip dark"><i class="fa-solid fa-shield-halved"></i> Interno</span>');
  out.push(`<span class="chip mono">${r.periodicidad}</span>`);
  if (r.requiereProtocolo) out.push('<span class="chip amber">Requiere protocolo activo</span>');
  return out.join('');
}

document.addEventListener('DOMContentLoaded', () => {
  if (typeof REPORTES === 'undefined') {
    console.error('REPORTES no está cargado');
    return;
  }

  const getActiveInst = () => document.getElementById('f-inst')?.value || '';

  GRUPOS.forEach(g => {
    const hostEl = document.getElementById(g.host);
    if (!hostEl) return;

    const items = Object.entries(REPORTES).filter(([, r]) => r.audiencia === g.key);
    const cards = items.map(([id, r]) => `
      <div class="report-card"${r.audiencia === 'profesional' && !r.seudonimo ? ' style="border-color:#E9B7B0"' : ''}>
        <div class="rico" style="background:${r.color}${r.color === '#0D1410' ? ';color:#A8E6C0' : ''}"><i class="fa-solid ${r.icono}"></i></div>
        <div style="flex:1">
          <h4>${r.n} · ${r.nombre}</h4>
          <p>${r.resumen}</p>
          <div class="meta">${etiquetas(r)}</div>
        </div>
        <div class="col" style="gap:.35rem">
          <a class="btn btn-sm btn-reporte-ver" href="${VIEWER_BASE_URL}?r=${id}&inst=${getActiveInst()}" data-repid="${id}" target="_blank"><i class="fa-solid fa-eye"></i> Ver</a>
          <button type="button" class="btn btn-sm ${r.audiencia === 'interno' ? 'btn-dark' : (r.audiencia === 'profesional' && !r.seudonimo ? 'btn-danger' : 'btn-primary')}" data-emitir="${id}"><i class="fa-solid fa-paper-plane"></i> Emitir</button>
        </div>
      </div>`).join('');

    hostEl.innerHTML = `
      <div class="section-head"><h2 class="section-title">${g.titulo}</h2>
        <span class="section-note">${g.nota}</span></div>
      <div class="grid g-2">${cards}</div>`;
  });

  // Listener para selector de institución
  const filterInst = document.getElementById('f-inst');
  if (filterInst) {
    filterInst.addEventListener('change', () => {
      const val = filterInst.value;
      document.querySelectorAll('.btn-reporte-ver').forEach(a => {
        a.href = `${VIEWER_BASE_URL}?r=${a.dataset.repid}&inst=${val}`;
      });
      const emInst = document.getElementById('em-inst');
      if (emInst) {
        emInst.value = val;
        pintarDestinatarios();
      }
    });
  }

  // Listener para botones de emisión
  document.querySelectorAll('[data-emitir]').forEach(b =>
    b.addEventListener('click', () => abrirEmision(b.dataset.emitir)));
});

/* ════════ Flujo de emisión ════════ */
let repActual = null;

function folioNuevo(pref) {
  return `${pref}-2026-${Math.floor(Math.random() * 9000) + 1000}`;
}

const DEST_LABEL = {
  contacto: id => (window.INSTITUCIONES && window.INSTITUCIONES[id]) ? window.INSTITUCIONES[id].contacto : 'Contacto de Recursos Humanos',
  direccion: id => 'Dirección General — ' + ((window.INSTITUCIONES && window.INSTITUCIONES[id]) ? window.INSTITUCIONES[id].corto : 'Institución'),
  profesional: id => (window.INSTITUCIONES && window.INSTITUCIONES[id]) ? `${window.INSTITUCIONES[id].profesional} · Ced. ${window.INSTITUCIONES[id].cedula} — profesional designado` : 'Profesional Designado',
  guardia: () => 'Psic. Daniela Mena · Ced. 8004112 — guardia A Tu Lado',
  direccion_atl: () => 'Dirección — A Tu Lado'
};

function pintarDestinatarios() {
  if (!repActual) return;
  const instId = document.getElementById('em-inst').value;
  const sel = document.getElementById('em-dest');
  sel.innerHTML = repActual.destinatarios
    .map(d => `<option value="${d}">${DEST_LABEL[d](instId)}</option>`).join('');
  if (repActual.audiencia === 'profesional' && !repActual.seudonimo) {
    if (window.INSTITUCIONES && window.INSTITUCIONES[instId]) {
      sel.innerHTML += `<option disabled>${window.INSTITUCIONES[instId].contacto} — sin acreditación clínica · NO ELEGIBLE</option>`;
    }
    document.getElementById('em-dest-hint').textContent =
      'Sólo aparecen profesionales con cédula verificada y contrato de confidencialidad vigente.';
  } else if (repActual.audiencia === 'institucion') {
    document.getElementById('em-dest-hint').textContent =
      'Cualquier persona con rol institucional puede recibirlo: no contiene datos individuales.';
  } else {
    document.getElementById('em-dest-hint').textContent =
      'Documento de uso interno. No se entrega a instituciones ni a profesionales designados.';
  }
}

function abrirEmision(id) {
  repActual = REPORTES[id];
  if (!repActual) return;
  repActual._id = id;

  document.getElementById('em-titulo').textContent = 'Emitir · ' + repActual.nombre;
  document.getElementById('em-sub').textContent =
    repActual.audiencia === 'profesional' ? 'Documento confidencial · folio y marca de agua'
    : repActual.audiencia === 'interno' ? 'Uso interno de A Tu Lado'
    : 'Apto para entrega a la institución';
  document.getElementById('em-head').style.background =
    repActual.audiencia === 'profesional' && !repActual.seudonimo ? '#6E140C' : 'var(--bg-panel)';

  const conf = repActual.audiencia === 'profesional' && !repActual.seudonimo;
  document.getElementById('em-persona-wrap').hidden = !repActual.requierePersona;
  document.getElementById('em-motivo-wrap').hidden = !conf;
  document.getElementById('em-alcance-wrap').hidden = repActual._id !== 'clinico';
  document.getElementById('em-vigencia-wrap').hidden = !conf;

  document.getElementById('em-aviso').innerHTML = conf
    ? `<div class="alert crit mb-2"><div class="ico"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="txt"><b>Este documento sólo puede emitirse con un protocolo de crisis activo</b>
          <span>Se entregará con folio, marca de agua a nombre del destinatario y enlace de vigencia limitada. La emisión queda asentada en la bitácora de accesos de la institución.</span></div></div>`
    : repActual.audiencia === 'institucion'
      ? `<div class="alert ok mb-2"><div class="ico"><i class="fa-solid fa-shield-halved"></i></div>
          <div class="txt"><b>Contiene sólo proporciones del total de la organización</b>
            <span>Sin nombres, sin conteos de personas y sin desglose por área: puede entregarse a la institución sin restricción.</span></div></div>`
      : `<div class="alert info mb-2"><div class="ico"><i class="fa-solid fa-lock"></i></div>
          <div class="txt"><b>Uso interno</b><span>Incluye conteos, desglose por institución e información comercial.</span></div></div>`;

  document.getElementById('em-pie').innerHTML = conf
    ? '<i class="fa-solid fa-fingerprint"></i> La emisión queda en bitácora'
    : '<i class="fa-solid fa-file-pdf"></i> Se abrirá el documento listo para descargar';

  document.getElementById('em-folio').textContent = folioNuevo(repActual.prefijo);
  pintarDestinatarios();
  if (typeof openModal === 'function') {
    openModal('m-emitir');
  }
}

document.getElementById('em-inst').addEventListener('change', pintarDestinatarios);

document.getElementById('em-go').addEventListener('click', () => {
  if (!repActual) return;
  const p = new URLSearchParams({
    r: repActual._id,
    inst: document.getElementById('em-inst').value,
    periodo: document.getElementById('em-periodo').value,
    dest: document.getElementById('em-dest').value,
    folio: document.getElementById('em-folio').textContent
  });
  window.open(`${VIEWER_BASE_URL}?${p.toString()}`, '_blank');
  if (typeof closeModal === 'function') {
    closeModal('m-emitir');
  }
});

/* ════════ Historial ════════ */
const HIST = [];

document.addEventListener('DOMContentLoaded', () => {
  const histBody = document.getElementById('hist-body');
  if (histBody) {
    if (HIST.length === 0) {
      histBody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:2.5rem 1rem;color:#8EADA4;"><i class="fa-solid fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;opacity:0.5;"></i>No hay emisiones ni entregas de reportes registradas aún</td></tr>`;
    } else {
      histBody.innerHTML = HIST.map(h => `
        <tr class="${h[9]}">
          <td class="mono xsmall">${h[0]}</td><td class="small">${h[2]}</td><td class="small">${h[3]}</td>
          <td class="small">${h[4]}</td><td class="small">${h[5]}</td><td class="small">${h[6]}</td>
          <td>${h[7]}</td><td class="center">${h[8]}</td>
          <td class="actions"><a class="btn btn-sm" href="${VIEWER_BASE_URL}?r=${h[1]}&folio=${h[0]}" target="_blank" title="Abrir visor de reporte"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></td>
        </tr>`).join('');
    }
  }
});
</script>
@endpush
