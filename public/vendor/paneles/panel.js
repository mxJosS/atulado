/* ════════════════════════════════════════════════════════════════
   A TU LADO — Chrome compartido de la maqueta
   Inyecta la barra lateral, resuelve tabs/modales y dibuja las
   micro-gráficas SVG. Sin dependencias, funciona con file://
   ════════════════════════════════════════════════════════════════ */

const TREE_SVG = `<img src="/images/marca/logo-atulado-64.png" width="22" height="22" alt="" aria-hidden="true" style="object-fit:contain">`;

const NAV_ATL = [
  { label: 'Operación de plataforma' },
  { id: 'instituciones', href: 'index.html', icon: 'fa-building-shield', text: 'Instituciones' },
  { id: 'detalle', href: '2-institucion-detalle.html', icon: 'fa-users-viewfinder', text: 'Detalle de institución' },
  { id: 'analitica', href: '3-analitica.html', icon: 'fa-chart-line', text: 'Analítica e índices' },
  { id: 'altas', href: '4-alta-masiva.html', icon: 'fa-file-arrow-up', text: 'Altas y estructura' },
  { label: 'Entregables' },
  { id: 'reportes', href: '6-reportes.html', icon: 'fa-file-pdf', text: 'Centro de reportes' },
  { label: 'Vista del cliente' },
  { id: 'institucional', href: '5-dashboard-institucional.html', icon: 'fa-eye', text: 'Panel institucional', badgeText: 'DEMO' },
];

const NAV_INST = [
  { label: 'Mi organización' },
  { id: 'institucional', href: '5-dashboard-institucional.html', icon: 'fa-chart-pie', text: 'Estado general' },
  { id: 'i-nom', href: '#', icon: 'fa-clipboard-check', text: 'Cumplimiento NOM-035' },
  { id: 'i-rep', href: '#', icon: 'fa-file-pdf', text: 'Reportes ejecutivos' },
  { label: 'Protocolo' },
  { id: 'i-prof', href: '#', icon: 'fa-user-shield', text: 'Profesional designado' },
  { id: 'i-contr', href: '#', icon: 'fa-file-signature', text: 'Contrato y alcance' },
];

function buildSidebar(el) {
  const mode = el.dataset.console || 'atl';
  const active = el.dataset.active || '';
  const items = mode === 'inst' ? NAV_INST : NAV_ATL;

  const nav = items.map(it => {
    if (it.label) return `<div class="nav-label">${it.label}</div>`;
    const badge = it.badge
      ? `<span class="nav-badge ${it.badgeTone || 'red'}">${it.badge}</span>`
      : (it.badgeText ? `<span class="chip mono" style="margin-left:auto;padding:0 5px;font-size:.6rem">${it.badgeText}</span>` : '');
    return `<a href="${it.href}" class="nav-item ${it.id === active ? 'active' : ''}">
      <span class="nav-icon"><i class="fa-solid ${it.icon}"></i></span><span>${it.text}</span>${badge}</a>`;
  }).join('');

  const tag = mode === 'inst'
    ? `<span class="console-tag is-institution"><i class="fa-solid fa-building"></i> PANEL INSTITUCIONAL</span>`
    : `<span class="console-tag"><i class="fa-solid fa-shield-halved"></i> CONSOLA A TU LADO</span>`;

  const user = mode === 'inst'
    ? { ini: 'MR', name: 'Mónica Rivas', role: 'RH · Grupo Peninsular' }
    : { ini: 'SC', name: 'Said Canul', role: 'Clínico acreditado · Ced. 8841923' };

  el.innerHTML = `
    <div class="sidebar-header"><a href="index.html" class="sidebar-brand">${TREE_SVG}
      <span>a tu <em class="editorial-italic" style="color:var(--mint-accent)">lado</em></span></a></div>
    ${tag}
    <nav class="nav-group" style="flex:1">${nav}</nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar">${user.ini}</div>
        <div><div class="who">${user.name}</div><div class="role">${user.role}</div></div>
      </div>
    </div>`;
}

/* ── Tabs ─────────────────────────────────────────────────── */
function wireTabs(root = document) {
  root.querySelectorAll('[data-tabs]').forEach(group => {
    group.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        const scope = group.dataset.tabs;
        group.querySelectorAll('.tab').forEach(t => t.classList.remove('on'));
        tab.classList.add('on');
        document.querySelectorAll(`[data-tabpane="${scope}"]`).forEach(p => p.classList.remove('on'));
        const pane = document.querySelector(`[data-tabpane="${scope}"][data-pane="${tab.dataset.pane}"]`);
        if (pane) pane.classList.add('on');
      });
    });
  });
}

/* ── Modales ──────────────────────────────────────────────── */
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  // Sin id se cierra la ventana de arriba (la última abierta), no la primera del documento.
  const abiertas = [...document.querySelectorAll('.modal-backdrop.open')];
  const m = id ? document.getElementById(id) : abiertas[abiertas.length - 1];
  if (!m) return;
  m.classList.remove('open');
  // El scroll de la página sólo vuelve cuando ya no queda ninguna ventana abierta.
  if (!document.querySelector('.modal-backdrop.open')) document.body.style.overflow = '';
}
function wireModals() {
  document.querySelectorAll('[data-open]').forEach(b =>
    b.addEventListener('click', e => { e.preventDefault(); openModal(b.dataset.open); }));
  document.querySelectorAll('[data-close]').forEach(b =>
    b.addEventListener('click', e => { e.preventDefault(); closeModal(b.dataset.close || null); }));
  document.querySelectorAll('.modal-backdrop').forEach(bd =>
    bd.addEventListener('click', e => { if (e.target === bd) closeModal(bd.id); }));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
}

/* ── Switches / segmentados ───────────────────────────────── */
function wireToggles() {
  document.querySelectorAll('.switch').forEach(s =>
    s.addEventListener('click', () => s.classList.toggle('on')));
  document.querySelectorAll('.segmented').forEach(g =>
    g.querySelectorAll('button').forEach(b =>
      b.addEventListener('click', () => {
        g.querySelectorAll('button').forEach(x => x.classList.remove('on'));
        b.classList.add('on');
      })));
}

/* ════════════ GRÁFICAS SVG ════════════
   Marcas delgadas, sin dobles ejes, etiquetas directas y
   capa de hover con <title> nativo en cada marca.            */

const NS = 'http://www.w3.org/2000/svg';
const el = (n, a = {}) => { const e = document.createElementNS(NS, n); for (const k in a) e.setAttribute(k, a[k]); return e; };
const title = (parent, txt) => { const t = el('title'); t.textContent = txt; parent.appendChild(t); return parent; };

/* Sparkline compacto para tarjetas KPI */
function sparkline(host, values, color = '#2E5D4B') {
  const w = 240, h = 34, pad = 3;
  const min = Math.min(...values), max = Math.max(...values), rng = (max - min) || 1;
  const x = i => pad + (i * (w - pad * 2)) / (values.length - 1);
  const y = v => h - pad - ((v - min) / rng) * (h - pad * 2);
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${w} ${h}`, preserveAspectRatio: 'none' });
  svg.setAttribute('style', 'width:100%;height:30px');
  const d = values.map((v, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(v).toFixed(1)}`).join(' ');
  svg.appendChild(el('path', { d: `${d} L${x(values.length - 1)},${h} L${x(0)},${h} Z`, fill: color, opacity: 0.10 }));
  svg.appendChild(el('path', {
    d, fill: 'none', stroke: color, 'stroke-width': 2,
    'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'vector-effect': 'non-scaling-stroke'
  }));
  host.innerHTML = ''; host.appendChild(svg);
}

/* Área apilada 100% — evolución del semáforo */
function stackedArea(host, cfg) {
  const w = 900, h = cfg.height || 240, ml = 34, mr = 118, mt = 12, mb = 26;
  const iw = w - ml - mr, ih = h - mt - mb;
  const n = cfg.labels.length;
  const x = i => ml + (i * iw) / (n - 1);
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${w} ${h}`, preserveAspectRatio: 'xMidYMid meet' });

  [0, 25, 50, 75, 100].forEach(p => {
    const yy = mt + ih - (p / 100) * ih;
    svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: yy, y2: yy, class: 'grid-line' }));
    const t = el('text', { x: ml - 7, y: yy + 3, 'text-anchor': 'end' }); t.textContent = p + '%'; svg.appendChild(t);
  });

  const cum = new Array(n).fill(0);
  const labels = [];
  cfg.series.forEach(s => {
    const lower = cum.slice();
    s.data.forEach((v, i) => cum[i] += v);
    const top = cum.map(v => mt + ih - (v / 100) * ih);
    const bot = lower.map(v => mt + ih - (v / 100) * ih);
    const d = top.map((yy, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${yy.toFixed(1)}`).join(' ')
      + ' ' + bot.map((yy, i) => `L${x(n - 1 - i).toFixed(1)},${bot[n - 1 - i].toFixed(1)}`).join(' ') + ' Z';
    const path = el('path', { d, fill: s.color, opacity: 0.92 });
    title(path, `${s.name}: ${s.data[n - 1]}% en ${cfg.labels[n - 1]}`);
    svg.appendChild(path);
    svg.appendChild(el('path', {
      d: top.map((yy, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${yy.toFixed(1)}`).join(' '),
      fill: 'none', stroke: '#FFFFFF', 'stroke-width': 2
    }));
    // etiqueta directa al final de cada banda
    labels.push({ y: (top[n - 1] + bot[n - 1]) / 2, text: `${s.name} ${s.data[n - 1]}%`, color: s.color });
  });

  // separa etiquetas que se encimarían
  labels.sort((a, b) => a.y - b.y);
  const GAP = 13;
  for (let i = 1; i < labels.length; i++)
    if (labels[i].y - labels[i - 1].y < GAP) labels[i].y = labels[i - 1].y + GAP;
  const over = labels.length ? labels[labels.length - 1].y - (mt + ih) : 0;
  if (over > 0) labels.forEach(l => l.y -= over);
  labels.forEach(l => {
    const t = el('text', { x: ml + iw + 9, y: l.y + 3, class: 'val-label', 'text-anchor': 'start', fill: l.color });
    t.textContent = l.text; svg.appendChild(t);
  });

  cfg.labels.forEach((l, i) => {
    if (n > 8 && i % 2) return;
    const t = el('text', { x: x(i), y: h - 8, 'text-anchor': 'middle' }); t.textContent = l; svg.appendChild(t);
  });
  svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: mt + ih, y2: mt + ih, class: 'baseline' }));
  host.innerHTML = ''; host.appendChild(svg);
}

/* Línea simple con banda de referencia */
function lineChart(host, cfg) {
  const w = 900, h = cfg.height || 230, ml = 40, mr = 58, mt = 16, mb = 26;
  const iw = w - ml - mr, ih = h - mt - mb;
  const all = cfg.series.flatMap(s => s.data);
  const min = cfg.min !== undefined ? cfg.min : Math.min(...all) * 0.9;
  const max = cfg.max !== undefined ? cfg.max : Math.max(...all) * 1.08;
  const n = cfg.labels.length;
  const x = i => ml + (i * iw) / (n - 1);
  const y = v => mt + ih - ((v - min) / (max - min)) * ih;
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${w} ${h}`, preserveAspectRatio: 'xMidYMid meet' });

  const ticks = cfg.ticks || 4;
  for (let i = 0; i <= ticks; i++) {
    const v = min + ((max - min) * i) / ticks, yy = y(v);
    svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: yy, y2: yy, class: 'grid-line' }));
    const t = el('text', { x: ml - 7, y: yy + 3, 'text-anchor': 'end' }); t.textContent = Math.round(v); svg.appendChild(t);
  }
  if (cfg.refLine !== undefined) {
    const yy = y(cfg.refLine);
    svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: yy, y2: yy, class: 'ref-line' }));
    const t = el('text', { x: ml + 4, y: yy - 5 }); t.textContent = cfg.refLabel || ''; svg.appendChild(t);
  }
  cfg.series.forEach(s => {
    const d = s.data.map((v, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(v).toFixed(1)}`).join(' ');
    svg.appendChild(el('path', { d, class: 'series-line', stroke: s.color }));
    s.data.forEach((v, i) => {
      const c = el('circle', { cx: x(i), cy: y(v), r: 4, fill: s.color, class: 'dot-mark' });
      title(c, `${s.name} · ${cfg.labels[i]}: ${v}${cfg.unit || ''}`);
      svg.appendChild(c);
    });
    const lab = el('text', { x: ml + iw + 8, y: y(s.data[n - 1]) + 3, class: 'val-label' });
    lab.setAttribute('fill', s.color);
    lab.textContent = `${s.data[n - 1]}${cfg.unit || ''}`;
    svg.appendChild(lab);
  });
  cfg.labels.forEach((l, i) => {
    if (n > 9 && i % 2) return;
    const t = el('text', { x: x(i), y: h - 8, 'text-anchor': 'middle' }); t.textContent = l; svg.appendChild(t);
  });
  svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: mt + ih, y2: mt + ih, class: 'baseline' }));
  host.innerHTML = ''; host.appendChild(svg);
}

/* Barras horizontales con etiqueta directa */
function barChart(host, cfg) {
  const rowH = cfg.rowH || 30, labelW = cfg.labelW || 150, valW = 52;
  const w = 720, h = cfg.data.length * rowH + 14;
  const iw = w - labelW - valW - 10;
  const max = cfg.max || Math.max(...cfg.data.map(d => d.v)) * 1.05;
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${w} ${h}`, preserveAspectRatio: 'xMinYMin meet' });
  cfg.data.forEach((d, i) => {
    const yy = i * rowH + 6;
    const t = el('text', { x: labelW - 9, y: yy + 12, 'text-anchor': 'end', fill: '#1A2620' });
    t.setAttribute('font-size', '11'); t.textContent = d.k; svg.appendChild(t);
    svg.appendChild(el('rect', { x: labelW, y: yy + 3, width: iw, height: 13, rx: 4, fill: '#EEF4F0' }));
    const bw = Math.max(3, (d.v / max) * iw);
    const r = el('rect', { x: labelW, y: yy + 3, width: bw, height: 13, rx: 4, fill: d.color || cfg.color || '#2E5D4B' });
    title(r, `${d.k}: ${d.v}${cfg.unit || ''}`);
    svg.appendChild(r);
    const v = el('text', { x: labelW + bw + 7, y: yy + 14, class: 'val-label' });
    v.textContent = `${d.v}${cfg.unit || ''}`; svg.appendChild(v);
  });
  host.innerHTML = ''; host.appendChild(svg);
}

/* Barras verticales agrupadas por una sola serie */
function columnChart(host, cfg) {
  const w = 900, h = cfg.height || 210, ml = 36, mr = 12, mt = 20, mb = 34;
  const iw = w - ml - mr, ih = h - mt - mb;
  const n = cfg.data.length;
  const max = cfg.max || Math.max(...cfg.data.map(d => d.v)) * 1.15;
  const gap = 8, bw = Math.max(6, iw / n - gap);
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${w} ${h}`, preserveAspectRatio: 'xMidYMid meet' });
  for (let i = 0; i <= 4; i++) {
    const yy = mt + ih - (ih * i) / 4;
    svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: yy, y2: yy, class: 'grid-line' }));
    const t = el('text', { x: ml - 7, y: yy + 3, 'text-anchor': 'end' }); t.textContent = Math.round((max * i) / 4); svg.appendChild(t);
  }
  cfg.data.forEach((d, i) => {
    const bh = Math.max(2, (d.v / max) * ih);
    const xx = ml + i * (iw / n) + gap / 2;
    const r = el('rect', { x: xx, y: mt + ih - bh, width: bw, height: bh, rx: 4, fill: d.color || cfg.color || '#2E5D4B' });
    title(r, `${d.k}: ${d.v}${cfg.unit || ''}`);
    svg.appendChild(r);
    if (cfg.showValues !== false) {
      const v = el('text', { x: xx + bw / 2, y: mt + ih - bh - 6, 'text-anchor': 'middle', class: 'val-label' });
      v.textContent = d.v + (cfg.unit || ''); svg.appendChild(v);
    }
    const t = el('text', { x: xx + bw / 2, y: h - 12, 'text-anchor': 'middle' }); t.textContent = d.k; svg.appendChild(t);
  });
  svg.appendChild(el('line', { x1: ml, x2: ml + iw, y1: mt + ih, y2: mt + ih, class: 'baseline' }));
  host.innerHTML = ''; host.appendChild(svg);
}

/* Anillo de índice compuesto */
function gaugeRing(host, cfg) {
  const size = cfg.size || 168, r = size / 2 - 14, c = size / 2;
  const circ = 2 * Math.PI * r, pct = Math.max(0, Math.min(100, cfg.value));
  const svg = el('svg', { class: 'chart', viewBox: `0 0 ${size} ${size}`, width: size, height: size });
  svg.appendChild(el('circle', { cx: c, cy: c, r, fill: 'none', stroke: '#EEF4F0', 'stroke-width': 13 }));
  const arc = el('circle', {
    cx: c, cy: c, r, fill: 'none', stroke: cfg.color || '#2E5D4B', 'stroke-width': 13,
    'stroke-linecap': 'round', 'stroke-dasharray': `${(circ * pct) / 100} ${circ}`,
    transform: `rotate(-90 ${c} ${c})`
  });
  title(arc, `${cfg.label || 'Índice'}: ${pct}`);
  svg.appendChild(arc);
  const v = el('text', { x: c, y: c + 4, 'text-anchor': 'middle' });
  v.setAttribute('font-family', "'Fraunces', serif"); v.setAttribute('font-size', '34');
  v.setAttribute('font-weight', '600'); v.setAttribute('fill', '#1A2620');
  v.textContent = pct; svg.appendChild(v);
  const l = el('text', { x: c, y: c + 24, 'text-anchor': 'middle' });
  l.textContent = cfg.caption || '/ 100'; svg.appendChild(l);
  host.innerHTML = ''; host.appendChild(svg);
}

/* Cualquier tabla suelta recibe su contenedor con scroll horizontal,
   para que en pantallas angostas la página nunca se desborde. */
function wrapLooseTables() {
  document.querySelectorAll('table.data').forEach(t => {
    if (t.closest('.table-wrap')) return;
    const w = document.createElement('div');
    w.className = 'table-wrap';
    w.style.borderRadius = '0';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
  });
}

/* ── Arranque ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-sidebar]').forEach(buildSidebar);
  wrapLooseTables();
  wireTabs(); wireModals(); wireToggles();
  document.querySelectorAll('[data-spark]').forEach(h =>
    sparkline(h, JSON.parse(h.dataset.spark), h.dataset.sparkColor || '#2E5D4B'));
});
