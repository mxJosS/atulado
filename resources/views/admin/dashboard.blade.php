@extends('layouts.admin')

@section('title', 'Estado de la plataforma — Panel Administrador')

@push('styles')
  @include('admin.instituciones.partials.estilos')
  <style>
    .segmented a {
      display: inline-block; padding: 0.32rem 0.68rem; border-radius: var(--radius-xs);
      font-size: 0.755rem; font-weight: 600; color: var(--text-medium-gray); text-decoration: none;
    }
    .segmented a.on { background: var(--bg-surface); color: var(--text-near-black); box-shadow: var(--shadow-xs); }
    .kpi-foot { display: block; }
    .kpi-foot + .kpi-foot { margin-top: 0.2rem; }
    .kpi-value .sep { color: var(--ink-muted); font-weight: 400; margin: 0 0.3rem; }
    a.alert { text-decoration: none; color: inherit; }
    a.alert:hover { box-shadow: var(--shadow-xs); }
    .inst-card { text-decoration: none; color: inherit; }
    .inst-card.en-alta { opacity: 0.72; }
    .btn[aria-disabled="true"] { opacity: 0.45; pointer-events: none; }
    .dash-cabecera { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .dash-acciones { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
    @media (max-width: 680px) { .toolbar .input-icon { min-width: 0 !important; width: 100%; } }
  </style>
@endpush

@section('content')
@php
  $k = $kpis;
  $signo = fn ($n) => $n > 0 ? '+' . number_format($n) : ($n < 0 ? '−' . number_format(abs($n)) : '±0');
  $clase = fn ($n) => $n > 0 ? 'up' : ($n < 0 ? 'down' : 'flat');
  $colorNivel = [
      'VERDE' => 'var(--sem-verde)', 'AMARILLO' => 'var(--sem-amarillo)', 'NARANJA' => 'var(--sem-naranja)',
      'ROJO' => 'var(--sem-rojo)', 'ROJO_AGUDO' => 'var(--sem-rojo-agudo)',
  ];
  $etiquetaNivel = ['VERDE' => 'Verde', 'AMARILLO' => 'Amarillo', 'NARANJA' => 'Naranja', 'ROJO' => 'Rojo', 'ROJO_AGUDO' => 'Rojo agudo'];
  $instActual = array_sum($k['instrumentos']['actual']);
  $instAnterior = array_sum($k['instrumentos']['anterior']);
  $etiquetaPeriodo = $periodo['clave'] === 'ciclo' ? 'ciclo de 14 días' : mb_strtolower($periodo['etiqueta']);
  $vsAnterior = 'vs. los ' . $periodo['dias'] . ' días previos';
@endphp

<div class="admin-content-canvas">

  {{-- Encabezado y filtro de periodo --}}
  <div class="dash-cabecera">
    <div>
      <div class="crumbs-atl">Consola A Tu Lado &rsaquo; <b>Dashboard</b></div>
      <h1 class="titulo-atl">Estado de la plataforma</h1>
      <p class="sub-atl">Vista de operación de A Tu Lado sobre todas las organizaciones contratantes. Los indicadores clínicos de esta pantalla son agregados; el detalle identificado vive dentro de cada institución.</p>
    </div>
    <div class="dash-acciones">
      <div class="segmented" role="group" aria-label="Periodo">
        @foreach(\App\Services\PlataformaService::PERIODOS as $clave => $opcion)
          <a href="{{ route('admin.dashboard', ['periodo' => $clave]) }}" @class(['on' => (string) $clave === $periodo['clave']])
             title="{{ $clave === 'ciclo' ? 'Ciclo del WHO-5: 14 días' : 'Últimos ' . $opcion['etiqueta'] }}">{{ $opcion['etiqueta'] }}</a>
        @endforeach
      </div>
      <a href="{{ route('admin.dashboard', ['periodo' => $periodo['clave']]) }}" class="btn btn-sm" title="Volver a calcular los indicadores">
        <i class="fa-solid fa-rotate"></i> Actualizado {{ $actualizado->format('H:i') }}
      </a>
      <a href="{{ route('admin.instituciones.index', ['alta' => 1]) }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i> Nueva institución
      </a>
    </div>
  </div>

  {{-- ══════════ PULSO OPERATIVO ══════════ --}}
  <div class="section-head" style="margin-top: 0.5rem;">
    <h2 class="section-title">Pulso operativo</h2>
    <span class="section-note">El periodo ({{ $etiquetaPeriodo }}) cambia instrumentos, revista y detecciones léxicas; lo demás es el estado actual.</span>
  </div>

  <div class="grid g-4">
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-building-shield"></i> Instituciones activas</div>
      <div class="kpi-value">{{ number_format($k['instituciones']['activas']) }}</div>
      <div class="kpi-foot">
        <span class="delta {{ $clase($k['instituciones']['trimestre']) }}">{{ $signo($k['instituciones']['trimestre']) }}</span> en el trimestre
        · {{ $k['instituciones']['onboarding'] }} en onboarding
      </div>
      <div class="kpi-spark" data-spark='@json($k['instituciones']['spark'])'></div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-users"></i> Cuentas activadas</div>
      <div class="kpi-value">{{ number_format($k['cuentas']['activas']) }} <span class="unit">/ {{ number_format($k['cuentas']['padron']) }}</span></div>
      <div class="kpi-foot">
        @if($k['cuentas']['adopcion'] !== null)
          <span class="delta {{ $k['cuentas']['adopcion'] >= $k['cuentas']['meta'] ? 'up' : 'down' }}">{{ $k['cuentas']['adopcion'] }}%</span> de adopción · meta contractual {{ $k['cuentas']['meta'] }}%
        @else
          Aún no hay personas en el padrón
        @endif
      </div>
      <div class="kpi-spark" data-spark='@json($k['cuentas']['spark'])'></div>
    </div>

    <div class="kpi accent-red">
      <div class="kpi-label"><i class="fa-solid fa-triangle-exclamation"></i> Casos abiertos nivel rojo</div>
      <div class="kpi-value">{{ number_format($k['casos']['total']) }}</div>
      <div class="kpi-foot">
        @if($k['casos']['total'] > 0)
          <b class="delta {{ $k['casos']['agudos'] > 0 ? 'down' : 'flat' }}">{{ $k['casos']['agudos'] }} {{ $k['casos']['agudos'] === 1 ? 'agudo' : 'agudos' }}</b>
          · en {{ $k['casos']['instituciones'] }} {{ $k['casos']['instituciones'] === 1 ? 'institución' : 'instituciones' }}
          @if($k['casos']['sin_institucion'] > 0) · {{ $k['casos']['sin_institucion'] }} sin institución @endif
        @else
          Sin casos rojos abiertos
        @endif
      </div>
    </div>

    <div class="kpi accent-violet">
      <div class="kpi-label"><i class="fa-solid fa-clipboard-question"></i> Instrumentos aplicados ({{ $periodo['clave'] === 'ciclo' ? 'ciclo' : $periodo['dias'] . 'd' }})</div>
      <div class="kpi-value">{{ number_format($instActual) }}</div>
      <div class="kpi-foot">
        {{ collect($k['instrumentos']['actual'])->map(fn ($n, $nombre) => $nombre . ' ' . number_format($n))->implode(' · ') }}
      </div>
      <div class="kpi-foot"><span class="delta {{ $clase($instActual - $instAnterior) }}">{{ $signo($instActual - $instAnterior) }}</span> {{ $vsAnterior }}</div>
    </div>
  </div>

  <div class="grid g-3 mt-2">
    <div class="kpi accent-green">
      <div class="kpi-label"><i class="fa-solid fa-signal"></i> Usuarios activos en este momento</div>
      <div class="kpi-value">{{ number_format($k['en_linea']) }}</div>
      <div class="kpi-foot">Con actividad en los últimos 5 minutos · sin contar a la administración</div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-book-open"></i> Revistas leídas ({{ $periodo['clave'] === 'ciclo' ? 'ciclo' : $periodo['dias'] . 'd' }})</div>
      <div class="kpi-value">{{ number_format($k['lecturas']['periodo']) }}</div>
      <div class="kpi-foot">
        <span class="delta {{ $clase($k['lecturas']['periodo'] - $k['lecturas']['anterior']) }}">{{ $signo($k['lecturas']['periodo'] - $k['lecturas']['anterior']) }}</span> {{ $vsAnterior }}
      </div>
      <div class="kpi-foot">
        @if($k['lecturas']['total'] > 0)
          {{ number_format($k['lecturas']['total']) }} en total desde el {{ $k['lecturas']['desde']->format('d/m/y') }} · una por persona, artículo y día
        @else
          Se cuentan desde esta versión · una por persona, artículo y día
        @endif
      </div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-user-doctor"></i> Psicólogos</div>
      <div class="kpi-value">
        {{ number_format($k['psicologos']['publican']) }} <span class="unit">publican</span><span class="sep">·</span>{{ number_format($k['psicologos']['clinicos']) }} <span class="unit">clínicos</span>
      </div>
      <div class="kpi-foot">{{ $k['psicologos']['ya_publicaron'] }} ya publicaron · {{ $k['psicologos']['con_institucion'] }} clínicos con institución asignada</div>
      <div class="kpi-foot">El perfil «ambos» cuenta en los dos</div>
    </div>
  </div>

  {{-- ══════════ SEMÁFORO Y ALERTAS ══════════ --}}
  <div class="grid g-3-2 mt-3">
    <div>
      <div class="section-head" style="margin-top: 0;">
        <h2 class="section-title">Semáforo consolidado de la plataforma</h2>
        <span class="section-note">{{ number_format($semaforo['total']) }} {{ $semaforo['total'] === 1 ? 'persona' : 'personas' }} del padrón con una clasificación en los últimos 30 días</span>
      </div>
      <div class="card card-pad">
        @if($semaforo['total'] > 0)
          <div class="dist-bar">
            @foreach($semaforo['conteos'] as $nivel => $n)
              @continue($n === 0)
              @php $pct = round($n / $semaforo['total'] * 100, 1); @endphp
              <div @class(['seg', 'light-text' => $nivel === 'AMARILLO']) style="background: {{ $colorNivel[$nivel] }}; width: {{ $pct }}%;"
                   title="{{ $etiquetaNivel[$nivel] }}: {{ $n }} ({{ $pct }}%)">{{ $pct >= 12 ? $pct . '% ' . $etiquetaNivel[$nivel] : ($pct >= 5 ? $pct . '%' : '') }}</div>
            @endforeach
          </div>
          <div class="stack-legend">
            @foreach($semaforo['conteos'] as $nivel => $n)
              <span class="legend-item"><span class="sw" style="background: {{ $colorNivel[$nivel] }}"></span> {{ $etiquetaNivel[$nivel] }} <b>{{ number_format($n) }}</b></span>
            @endforeach
          </div>
        @else
          <span class="locked"><i class="fa-solid fa-hourglass-half"></i> Aún no hay clasificaciones del padrón en los últimos 30 días</span>
        @endif
        <div class="divider"></div>
        <div class="section-note mb-1">Evolución 12 semanas — proporción del padrón clasificado por nivel</div>
        @if($semaforo['evolucion']['con_datos'])
          <div id="ch-evolucion"></div>
        @else
          <span class="locked"><i class="fa-solid fa-chart-area"></i> La gráfica aparece con la primera clasificación</span>
        @endif
      </div>
    </div>

    <div>
      <div class="section-head" style="margin-top: 0;">
        <h2 class="section-title">Alertas que exigen acción</h2>
        <a href="{{ route('admin.cola.index') }}" class="section-note spacer">Cola de atención</a>
      </div>
      <div class="col" style="gap: 0.6rem;" id="alertas">
        @foreach($alertas as $alerta)
          <{{ $alerta['url'] ? 'a' : 'div' }} class="alert {{ $alerta['tono'] }}" @if($alerta['url']) href="{{ $alerta['url'] }}" @endif>
            <div class="ico"><i class="fa-solid {{ $alerta['icono'] }}"></i></div>
            <div class="txt"><b>{{ $alerta['titulo'] }}</b>
              <span>{{ $alerta['texto'] }}</span></div>
            <div class="when">{{ $alerta['cuando'] }}</div>
          </{{ $alerta['url'] ? 'a' : 'div' }}>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ══════════ INSTITUCIONES ══════════ --}}
  <div class="section-head">
    <h2 class="section-title">Instituciones</h2>
    <span class="section-note">Clic en una tarjeta para entrar a su detalle</span>
    <div class="spacer"></div>
    <div class="segmented" role="group" aria-label="Vista">
      <button type="button" class="on" data-vista="tarjetas">Tarjetas</button><button type="button" data-vista="tabla">Tabla</button>
    </div>
  </div>

  <div class="toolbar">
    <div class="input-icon" style="min-width: 250px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input class="input" id="f-q" type="search" placeholder="Buscar institución, RFC o contacto…" aria-label="Buscar institución">
    </div>
    <select class="select" id="f-sector" style="width: auto;" aria-label="Sector">
      <option value="">Todos los sectores</option>
      @foreach($sectores as $sector)
        <option value="{{ $sector }}">{{ $sector }}</option>
      @endforeach
    </select>
    <select class="select" id="f-estado" style="width: auto;" aria-label="Estado">
      <option value="">Todos los estados</option>
      @foreach($estados as $valor => $texto)
        <option value="{{ $valor }}">{{ $texto }}</option>
      @endforeach
    </select>
    <select class="select" id="f-orden" style="width: auto;" aria-label="Ordenar">
      <option value="prioridad">Ordenar: prioridad clínica</option>
      <option value="actividad">Ordenar: actividad</option>
      <option value="adopcion">Ordenar: adopción</option>
      <option value="alta">Ordenar: alta más reciente</option>
    </select>
    <div class="spacer"></div>
    <a class="btn btn-sm" id="exportar-padron" href="{{ route('admin.dashboard.exportar-padron') }}"
       data-base="{{ route('admin.dashboard.exportar-padron') }}" title="Excel con nombre, correo, área y estado de la cuenta. Sin datos clínicos.">
      <i class="fa-solid fa-file-excel"></i> Exportar padrón
    </a>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.instituciones.index', ['alta' => 1]) }}"><i class="fa-solid fa-plus"></i> Nueva institución</a>
  </div>

  @if(count($instituciones) === 0)
    <div class="card card-pad" style="text-align: center; color: var(--text-medium-gray);">
      Aún no hay instituciones registradas.
    </div>
  @endif

  <div id="sin-resultados" class="card card-pad" style="display: none; text-align: center; color: var(--text-medium-gray);">
    Ninguna institución coincide con los filtros.
  </div>

  {{-- Tarjetas --}}
  <div class="inst-grid" id="vista-tarjetas">
    @foreach($instituciones as $i)
      <a href="{{ $i['url'] }}" @class(['inst-card', 'has-crit' => $i['agudos'] > 0, 'en-alta' => $i['onboarding']])
         data-inst="{{ $i['id'] }}" data-nombre="{{ $i['nombre'] }}" data-busqueda="{{ $i['busqueda'] }}" data-sector="{{ $i['sector'] }}" data-estado="{{ $i['estado'] }}"
         data-prioridad="{{ $i['orden']['prioridad'] }}" data-actividad="{{ $i['orden']['actividad'] }}" data-adopcion="{{ $i['orden']['adopcion'] }}" data-alta="{{ $i['orden']['alta'] }}">
        <div class="inst-top">
          <div class="inst-logo" style="background: {{ $i['color'] }};">{{ $i['iniciales'] }}</div>
          <div style="flex: 1; min-width: 0;">
            <div class="inst-name">{{ $i['razon_social'] ?: $i['nombre'] }}</div>
            <div class="inst-meta">{{ $i['ubicacion'] }}</div>
          </div>
          @if($i['badge']['clase'] === 'chip')
            <span class="chip"><i class="fa-solid fa-hourglass-half"></i> {{ $i['badge']['texto'] }}</span>
          @else
            <span class="{{ $i['badge']['clase'] }}"><span class="dot"></span> {{ $i['badge']['texto'] }}</span>
          @endif
        </div>
        <div class="bar-track" style="height: 10px;" title="{{ $i['semaforo_visible'] ? 'Semáforo actual de ' . $i['clasificados'] . ' personas' : 'Sin datos suficientes' }}">
          @if($i['semaforo_visible'])
            @foreach($i['semaforo'] as $nivel => $n)
              @continue($n === 0)
              <div class="seg" style="background: {{ $colorNivel[$nivel] }}; width: {{ round($n / $i['clasificados'] * 100, 1) }}%;"></div>
            @endforeach
          @else
            <div class="seg" style="background: var(--bg-subtle); width: 100%;"></div>
          @endif
        </div>
        <div class="inst-stats">
          <div class="inst-stat"><div class="v">{{ number_format($i['padron']) }}</div><div class="l">{{ $i['etiqueta_personas'] }}</div></div>
          <div class="inst-stat"><div class="v">{{ $i['adopcion'] !== null ? $i['adopcion'] . '%' : '—' }}</div><div class="l">Adopción</div></div>
          <div class="inst-stat"><div class="v">{{ $i['arraigo'] !== null ? number_format($i['arraigo'], 2) : '—' }}</div><div class="l">Arraigo</div></div>
        </div>
        <div class="row" style="gap: 0.35rem; flex-wrap: wrap;">
          @foreach($i['chips'] as [$tono, $icono, $texto])
            <span class="chip {{ $tono }}">@if($icono)<i class="fa-solid {{ $icono }}"></i> @endif{{ $texto }}</span>
          @endforeach
        </div>
      </a>
    @endforeach
  </div>

  {{-- Tabla: comparativo entre instituciones --}}
  <div id="vista-tabla" style="display: none;">
    <div class="section-head" style="margin-top: 0;">
      <h2 class="section-title">Comparativo entre instituciones</h2>
      <span class="section-note">Todos los valores son agregados.</span>
    </div>
    <div class="card">
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th>Institución</th><th>Sector</th><th class="num">Padrón</th><th class="num">Adopción</th>
              <th style="min-width: 150px;">Distribución del semáforo</th>
              <th class="num">Casos abiertos</th><th class="num">1er contacto</th>
              <th class="num">WHO-5 inst.</th><th class="num">Adherencia</th><th class="num">R4 silencio</th><th>Estado</th>
            </tr>
          </thead>
          <tbody id="tabla-instituciones">
            @foreach($instituciones as $i)
              <tr @class(['row-red' => $i['agudos'] > 0])
                  data-inst="{{ $i['id'] }}" data-nombre="{{ $i['nombre'] }}"
                  data-prioridad="{{ $i['orden']['prioridad'] }}" data-actividad="{{ $i['orden']['actividad'] }}" data-adopcion="{{ $i['orden']['adopcion'] }}" data-alta="{{ $i['orden']['alta'] }}">
                <td><a href="{{ $i['url'] }}"><b>{{ $i['nombre'] }}</b></a></td>
                <td class="muted small">{{ $i['sector'] ?: '—' }}</td>
                <td class="num">{{ number_format($i['padron']) }}</td>
                <td class="num">{{ $i['adopcion'] !== null ? $i['adopcion'] . '%' : '—' }}</td>
                <td>
                  @if($i['semaforo_visible'])
                    <div class="bar-track" title="Semáforo actual de {{ $i['clasificados'] }} personas">
                      @foreach($i['semaforo'] as $nivel => $n)
                        @continue($n === 0)
                        <div class="seg" style="background: {{ $colorNivel[$nivel] }}; width: {{ round($n / $i['clasificados'] * 100, 1) }}%;"></div>
                      @endforeach
                    </div>
                  @else
                    <span class="locked"><i class="fa-solid fa-hourglass-half"></i> Sin datos suficientes</span>
                  @endif
                </td>
                <td class="num">
                  @if($i['casos'] > 0)
                    <span class="sem sem-{{ $i['nivel_caso'] }}"><span class="dot"></span> {{ $i['casos'] }}</span>
                  @else
                    <span class="chip green">0</span>
                  @endif
                </td>
                <td class="num">{{ $i['primer_contacto'] !== null ? $i['primer_contacto'] . ' min' : '—' }}</td>
                <td class="num">{{ $i['who5'] ?? '—' }}</td>
                <td class="num">{{ $i['adherencia'] !== null ? $i['adherencia'] . '%' : '—' }}</td>
                <td class="num">{{ $i['padron'] > 0 ? $i['silencio'] : '—' }}</td>
                <td><span class="chip {{ $i['estado_chip'] }}">{{ $i['estado_legible'] }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-foot">
        <b>WHO-5 institucional</b> es el promedio de la última aplicación de cada persona, en escala 0–100; bajo 50 es el punto de corte asociado a riesgo de sintomatología depresiva.
        <b>Arraigo</b> = promedio de personas activas por día ÷ personas activas en el mes.
        <b>Adherencia</b> = días con registro diario en los últimos 30 (desde la activación de la cuenta).
        <b>1er contacto</b> = mediana de minutos hasta el contacto humano, casos de los últimos 90 días.
        <b>R4 silencio</b> = personas sin registrar en {{ \App\Services\PlataformaService::DIAS_SILENCIO }} días o más.
      </div>
    </div>
  </div>

  {{-- ══════════ ACREDITACIONES Y USUARIOS ══════════ --}}
  <div class="section-head" style="margin-top: 2.5rem;">
    <h2 class="section-title">Acreditaciones y usuarios</h2>
    <div class="spacer"></div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus"></i> Dar de alta usuario</a>
    <a href="{{ route('admin.verifications.index') }}" class="btn btn-sm"><i class="fa-solid fa-id-card-clip"></i> Auditar cédulas</a>
  </div>

  @include('admin.partials.dashboard-listas')

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ filemtime(public_path('vendor/paneles/panel.js')) }}"></script>
<script>
(() => {
  // Evolución del semáforo (sólo los niveles que aparecen en las 12 semanas)
  const evolucion = @json($semaforo['evolucion']);
  const host = document.getElementById('ch-evolucion');
  if (host && evolucion.con_datos) {
    stackedArea(host, {
      height: 220,
      labels: evolucion.labels,
      series: evolucion.series.filter(s => s.data.some(v => v > 0)),
    });
  }

  // Tarjetas | Tabla
  const tarjetas = document.getElementById('vista-tarjetas');
  const tabla = document.getElementById('vista-tabla');
  const mostrar = vista => {
    tarjetas.style.display = vista === 'tabla' ? 'none' : '';
    tabla.style.display = vista === 'tabla' ? '' : 'none';
    document.querySelectorAll('[data-vista]').forEach(b => b.classList.toggle('on', b.dataset.vista === vista));
    try { localStorage.setItem('atl-dashboard-vista', vista); } catch (e) {}
  };
  document.querySelectorAll('[data-vista]').forEach(b => b.addEventListener('click', () => mostrar(b.dataset.vista)));
  try { if (localStorage.getItem('atl-dashboard-vista') === 'tabla') mostrar('tabla'); } catch (e) {}

  // Filtros, orden y exportación
  const sinAcentos = s => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  const q = document.getElementById('f-q');
  const sector = document.getElementById('f-sector');
  const estado = document.getElementById('f-estado');
  const orden = document.getElementById('f-orden');
  const exportar = document.getElementById('exportar-padron');
  const sinResultados = document.getElementById('sin-resultados');
  const cuerpo = document.getElementById('tabla-instituciones');
  const cartas = [...tarjetas.querySelectorAll('[data-inst]')];
  const filas = [...cuerpo.querySelectorAll('[data-inst]')];

  const aplicar = () => {
    const texto = sinAcentos(q.value.trim());
    const visibles = new Set();
    cartas.forEach(c => {
      const ok = (!texto || sinAcentos(c.dataset.busqueda).includes(texto))
        && (!sector.value || c.dataset.sector === sector.value)
        && (!estado.value || c.dataset.estado === estado.value);
      c.style.display = ok ? '' : 'none';
      if (ok) visibles.add(c.dataset.inst);
    });
    filas.forEach(f => { f.style.display = visibles.has(f.dataset.inst) ? '' : 'none'; });

    const clave = orden.value;
    const comparar = (a, b) => (Number(b.dataset[clave]) - Number(a.dataset[clave]))
      || a.dataset.nombre.localeCompare(b.dataset.nombre, 'es');
    cartas.slice().sort(comparar).forEach(c => tarjetas.appendChild(c));
    filas.slice().sort(comparar).forEach(f => cuerpo.appendChild(f));

    sinResultados.style.display = cartas.length && !visibles.size ? '' : 'none';
    exportar.href = exportar.dataset.base + '?instituciones=' + [...visibles].join(',');
    exportar.setAttribute('aria-disabled', visibles.size ? 'false' : 'true');
  };
  [q, sector, estado, orden].forEach(el => el.addEventListener('input', aplicar));
  aplicar();
})();
</script>
@endpush
