@extends('layouts.admin')

@section('title', 'Analítica e índices' . ($institucion ? ' — ' . $institucion->nombre_corto : ''))

@push('styles')
  @include('admin.instituciones.partials.estilos')
  <style>
    .segmented a {
      display: inline-block; padding: 0.32rem 0.68rem; border-radius: var(--radius-xs);
      font-size: 0.755rem; font-weight: 600; color: var(--text-medium-gray); text-decoration: none;
    }
    .segmented a.on { background: var(--bg-surface); color: var(--text-near-black); box-shadow: var(--shadow-xs); }
    .ana-cabecera { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .ana-acciones { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
    .indice-cuerpo { display: flex; gap: 1.2rem; align-items: center; flex-wrap: wrap; }
    .indice-cuerpo > .gauge { flex: none; }
    .indice-cuerpo > .detalle { flex: 1; min-width: 190px; }
    .indice-vacio { width: 150px; height: 150px; border-radius: 50%; border: 13px solid #EEF4F0; display: grid; place-items: center; text-align: center; font-size: 0.72rem; color: var(--ink-muted); padding: 0.6rem; }
    table.componentes td { padding: 0.35rem 0.5rem; font-size: 0.76rem; }
    .matriz td.diag { font-weight: 700; }
    .aviso-datos { font-size: 0.74rem; color: var(--ink-muted); margin-top: 0.5rem; }
    @media (max-width: 1024px) { .ana-grid-3 { grid-template-columns: 1fr !important; } }
    @media print {
      .dashboard-sidebar, .admin-mobile-topbar, .ana-acciones, #adminSidebarOverlay { display: none !important; }
      .dashboard-main, .dashboard-content, .admin-content-canvas { margin: 0 !important; padding: 0 !important; width: 100% !important; }
      .card { break-inside: avoid; box-shadow: none; }
      body { background: #fff !important; }
    }
  </style>
@endpush

@section('content')
@php
  $delta = function (?int $actual, ?int $anterior, bool $subirEsBueno = true) {
      if ($actual === null || $anterior === null) {
          return null;
      }
      $d = $actual - $anterior;
      $clase = $d === 0 ? 'flat' : (($d > 0) === $subirEsBueno ? 'up' : 'down');
      return ['texto' => ($d > 0 ? '+' : ($d < 0 ? '−' : '±')) . abs($d) . ' pts', 'clase' => $clase, 'd' => $d];
  };
  $vsAnterior = 'vs. los ' . $periodo['dias'] . ' días previos';
  $colorIndice = fn (?int $v, bool $altoEsBueno = true) => $v === null ? '#A5B8B0'
      : ($altoEsBueno ? ($v >= 70 ? '#1E8449' : ($v >= 45 ? '#DCAF00' : '#B02418')) : '#2A78D6');
  $nombreCorte = $institucion ? $institucion->nombre_corto : 'Todas las instituciones';
  $sinDatos = fn (string $texto) => '<span class="locked"><i class="fa-solid fa-hourglass-half"></i> ' . e($texto) . '</span>';
@endphp

<div class="admin-content-canvas">

  <div class="ana-cabecera cabecera-atl" data-aparecer>
    <div>
      <div class="crumbs-atl">
        Consola A Tu Lado &rsaquo; <a href="{{ route('admin.instituciones.index') }}">Instituciones</a> &rsaquo;
        @if($institucion)
          <a href="{{ route('admin.instituciones.show', $institucion) }}">{{ $institucion->nombre_corto }}</a> &rsaquo;
        @endif
        <b>Analítica</b>
      </div>
      <h1 class="titulo-atl">Indicadores y analítica</h1>
      <p class="sub-atl" style="max-width: 820px;">
        Los índices de esta pantalla responden tres preguntas distintas: <b>¿la gente está mejor?</b> (clínicos),
        <b>¿la herramienta se está usando?</b> (adopción) y <b>¿estamos respondiendo bien?</b> (operación). Cada tarjeta declara su fórmula.
        <br><span class="section-note">{{ $nombreCorte }} · {{ number_format($poblacion['padron']) }} en padrón, {{ number_format($poblacion['activos']) }} con cuenta activa ·
          del {{ $periodo['desde']->format('d/m/Y') }} al {{ $periodo['hasta']->format('d/m/Y') }}</span>
      </p>
    </div>
    <div class="ana-acciones">
      <select class="select" style="width: auto; font-weight: 600;" aria-label="Institución"
              onchange="window.location.href = this.value">
        <option value="{{ route('admin.analitica', ['periodo' => $periodo['clave']]) }}" @selected(!$institucion)>Todas las instituciones</option>
        @foreach($opciones as $opcion)
          <option value="{{ route('admin.analitica', ['institucion' => $opcion->slug, 'periodo' => $periodo['clave']]) }}" @selected($institucion?->id === $opcion->id)>{{ $opcion->nombre_corto }}</option>
        @endforeach
      </select>
      <div class="segmented" role="group" aria-label="Periodo">
        @foreach($periodos as $clave => $opcion)
          <a href="{{ route('admin.analitica', array_filter(['institucion' => $institucion?->slug, 'periodo' => $clave])) }}"
             @class(['on' => (string) $clave === $periodo['clave']])>{{ $opcion['etiqueta'] }}</a>
        @endforeach
      </div>
      <button type="button" class="btn btn-dark btn-sm" onclick="window.print()" title="Abre la vista de impresión; elige «Guardar como PDF»">
        <i class="fa-solid fa-file-pdf"></i> Exportar informe
      </button>
    </div>
  </div>

  {{-- ══════════ ÍNDICES ══════════ --}}
  <div class="grid g-3 ana-grid-3">
    {{-- IBI --}}
    @php $d = $delta($ibi['valor'], $ibi['anterior']); $c = $ibi['componentes']; @endphp
    <div class="card">
      <div class="card-head"><h3>Índice de Bienestar Institucional</h3><span class="chip mono spacer">IBI</span></div>
      <div class="card-body indice-cuerpo">
        <div class="gauge">
          @if($ibi['valor'] !== null)
            <div id="g-ibi"></div>
          @else
            <div class="indice-vacio">Sin datos en el periodo</div>
          @endif
        </div>
        <div class="detalle">
          @if($d)<div class="row mb-1" style="gap: 0.4rem;"><span class="delta {{ $d['clase'] }}">{{ $d['texto'] }}</span><span class="small muted">{{ $vsAnterior }}</span></div>@endif
          @if($ibi['valor'] !== null && $ibi['valor'] < \App\Services\AnaliticaService::IBI_REVISION)
            <div class="mb-1"><span class="chip red"><i class="fa-solid fa-triangle-exclamation"></i> Bajo {{ \App\Services\AnaliticaService::IBI_REVISION }}: revisar con dirección</span></div>
          @endif
          <table class="data componentes">
            <tbody>
              <tr><td>WHO-5 normalizado <span class="chip mono">40%</span></td><td class="num">{{ $c['who5'] ?? '—' }}</td></tr>
              <tr><td>Distribución del semáforo <span class="chip mono">30%</span></td><td class="num">{{ $c['semaforo'] ?? '—' }}</td></tr>
              <tr><td>Adherencia al registro <span class="chip mono">20%</span></td><td class="num">{{ $c['adherencia'] ?? '—' }}</td></tr>
              <tr><td>Uso de herramientas <span class="chip mono">10%</span></td><td class="num">{{ $c['herramientas'] ?? '—' }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-foot"><b>IBI</b> = 0.40·WHO-5 + 0.30·(%verde + 0.5·%amarillo) + 0.20·adherencia + 0.10·uso de herramientas. Escala 0–100. Bajo {{ \App\Services\AnaliticaService::IBI_REVISION }} activa revisión con dirección. Si falta un componente, su peso se reparte entre los demás.</div>
    </div>

    {{-- IRC --}}
    @php $d = $delta($irc['valor'], $irc['anterior']); @endphp
    <div class="card">
      <div class="card-head"><h3>Índice de Riesgo Concentrado</h3><span class="chip mono spacer">IRC</span></div>
      <div class="card-body indice-cuerpo">
        <div class="gauge">
          @if($irc['valor'] !== null)
            <div id="g-irc"></div>
          @else
            <div class="indice-vacio">{{ $irc['areas'] < 2 ? 'Se necesita más de un área' : 'Sin alertas en el periodo' }}</div>
          @endif
        </div>
        <div class="detalle">
          @if($d)<div class="row mb-1" style="gap: 0.4rem;"><span class="delta flat">{{ $d['texto'] }}</span><span class="small muted">{{ $d['d'] > 0 ? 'más concentrado' : ($d['d'] < 0 ? 'más repartido' : 'igual') }} {{ $vsAnterior }}</span></div>@endif
          <p class="small muted mb-1">Mide qué tan concentradas están las alertas en pocas áreas. Un valor alto no significa una institución peor, significa un problema <b>localizable</b>.</p>
          <div class="row wrap" style="gap: 0.3rem;">
            @foreach($irc['reparto'] as $i => $r)
              <span class="chip {{ $r['area'] === 'Resto' ? '' : ($i === 0 ? 'red' : 'amber') }}">{{ $r['area'] }} {{ $r['pct'] }}%</span>
            @endforeach
          </div>
          <div class="aviso-datos">{{ $irc['alertas'] }} {{ $irc['alertas'] === 1 ? 'alerta' : 'alertas' }} en el periodo</div>
        </div>
      </div>
      <div class="card-foot"><b>IRC</b> = índice de Gini sobre la tasa de alertas (casos de crisis ÷ padrón) por área, ×100. Un valor alto es buena noticia: dice dónde intervenir.</div>
    </div>

    {{-- IRO --}}
    @php $d = $delta($iro['valor'], $iro['anterior']); $c = $iro['componentes']; @endphp
    <div class="card">
      <div class="card-head"><h3>Índice de Respuesta Operativa</h3><span class="chip mono spacer">IRO</span></div>
      <div class="card-body indice-cuerpo">
        <div class="gauge">
          @if($iro['valor'] !== null)
            <div id="g-iro"></div>
          @else
            <div class="indice-vacio">Sin casos en el periodo</div>
          @endif
        </div>
        <div class="detalle">
          @if($d)<div class="row mb-1" style="gap: 0.4rem;"><span class="delta {{ $d['clase'] }}">{{ $d['texto'] }}</span><span class="small muted">{{ abs($d['d']) <= 2 ? 'estable' : $vsAnterior }}</span></div>@endif
          <table class="data componentes">
            <tbody>
              <tr><td>Contacto &lt; {{ \App\Services\AnaliticaService::SLA_CONTACTO_MIN }} min</td><td class="num">{{ $c['contacto'] !== null ? $c['contacto'] . '%' : '—' }}</td></tr>
              <tr><td>Cierre verificado</td><td class="num">{{ $c['cierre'] !== null ? $c['cierre'] . '%' : '—' }}</td></tr>
              <tr><td>Salidas sin contacto</td><td class="num">{{ $iro['casos'] > 0 ? $iro['salidas'] : '—' }}</td></tr>
              <tr><td>ASQ respondidas en 24 h</td><td class="num">{{ $c['asq'] !== null ? $c['asq'] . '%' : '—' }}</td></tr>
            </tbody>
          </table>
          <div class="aviso-datos">{{ $iro['casos'] }} {{ $iro['casos'] === 1 ? 'caso' : 'casos' }} · {{ $iro['banderas'] }} {{ $iro['banderas'] === 1 ? 'bandera léxica' : 'banderas léxicas' }} en el periodo</div>
        </div>
      </div>
      <div class="card-foot"><b>IRO</b> = 40% contacto &lt; {{ \App\Services\AnaliticaService::SLA_CONTACTO_MIN }} min + 30% cierre verificado + 20% sin salidas sin contacto (−25 por cada una) + 10% ASQ respondidas en 24 h. Es el índice que se audita, no el que se presume.</div>
    </div>
  </div>

  {{-- ══════════ EVOLUCIÓN ══════════ --}}
  <div class="section-head">
    <h2 class="section-title">Cómo evolucionó la población</h2>
    <span class="section-note">12 {{ $evolucion['unidad'] }} · proporción del padrón con nivel vigente</span>
  </div>

  <div class="grid g-2">
    <div class="card">
      <div class="card-head"><h3>Migración entre niveles del semáforo</h3></div>
      <div class="card-body">
        @if($evolucion['semaforo']['con_datos'])
          <div id="ch-semaforo"></div>
        @else
          {!! $sinDatos('La gráfica aparece con la primera clasificación') !!}
        @endif
      </div>
      @if($evolucion['semaforo']['texto'])<div class="card-foot">{{ $evolucion['semaforo']['texto'] }}</div>@endif
    </div>

    <div class="card">
      <div class="card-head"><h3>WHO-5 institucional vs. corte de riesgo</h3>
        <span class="spacer section-note">Una sola escala 0–100</span></div>
      <div class="card-body">
        @if($evolucion['who5']['con_datos'])
          <div id="ch-who5"></div>
          <div class="stack-legend">
            @foreach($evolucion['who5']['series'] as $serie)
              <span class="legend-item"><span class="sw" style="background: {{ $serie['color'] }}"></span> {{ $serie['name'] }}</span>
            @endforeach
            <span class="legend-item"><span class="sw" style="background: #8EADA4; border-radius: 0; height: 2px;"></span> Corte {{ \App\Services\AnaliticaService::CORTE_WHO5 }}</span>
          </div>
        @else
          {!! $sinDatos('Aún no hay WHO-5 respondidos en estas semanas') !!}
        @endif
      </div>
      @if($evolucion['who5']['con_datos'])<div class="card-foot">{{ $evolucion['who5']['texto'] }}</div>@endif
    </div>
  </div>

  <div class="grid g-2 mt-2">
    <div class="card">
      <div class="card-head"><h3>Matriz de transición mensual</h3>
        <span class="spacer section-note">De dónde salió y a dónde llegó cada persona</span></div>
      <div class="card-body">
        @if($transicion['con_datos'])
          @php
            $sem = ['VERDE' => ['verde', 'Verde', '#E3F3E9'], 'AMARILLO' => ['amarillo', 'Amarillo', '#FBF2D4'], 'NARANJA' => ['naranja', 'Naranja', '#FBEADC'], 'ROJO' => ['rojo', 'Rojo', '#F8E2DF']];
          @endphp
          <div class="table-wrap">
            <table class="data matriz">
              <thead><tr><th>De ↓ / A →</th><th class="num">Verde</th><th class="num">Amarillo</th><th class="num">Naranja</th><th class="num">Rojo</th><th class="num">Silencio</th></tr></thead>
              <tbody>
                @foreach($transicion['matriz'] as $de => $fila)
                  <tr @class(['row-red' => $de === 'ROJO'])>
                    <td><span class="sem sem-{{ $sem[$de][0] }}"><span class="dot"></span> {{ $sem[$de][1] }}</span> <span class="small muted">({{ $fila['total'] }})</span></td>
                    @foreach($fila['pct'] as $a => $pct)
                      <td @class(['num', 'diag' => $a === $de]) @if($a === $de && $pct) style="background: {{ $sem[$de][2] }};" @endif>{{ $pct === null ? '—' : $pct . '%' }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          {!! $sinDatos('Se necesita al menos una clasificación de hace un mes') !!}
        @endif
      </div>
      @if($transicion['texto'])<div class="card-foot">{{ $transicion['texto'] }}</div>@endif
    </div>

    <div class="card">
      <div class="card-head"><h3>Embudo de acompañamiento</h3>
        <span class="spacer section-note">Del alta al caso atendido · {{ $periodo['etiqueta'] }}</span></div>
      <div class="card-body">
        @if($embudo['con_datos'])
          <div id="ch-embudo"></div>
        @else
          {!! $sinDatos('Aún no hay personas en el padrón') !!}
        @endif
      </div>
      @if($embudo['texto'])<div class="card-foot">{{ $embudo['texto'] }}</div>@endif
    </div>
  </div>

  {{-- ══════════ USO ══════════ --}}
  <div class="section-head">
    <h2 class="section-title">Cómo se usa la aplicación</h2>
    <span class="section-note">Interacción por módulo y hora · insumo para producto y para el discurso comercial</span>
  </div>

  <div class="grid g-2-1">
    <div class="card">
      <div class="card-head"><h3>Interacciones por módulo</h3>
        <span class="spacer section-note">{{ $periodo['etiqueta'] }} · {{ number_format($modulos['total']) }} eventos</span></div>
      <div class="card-body">
        @if($modulos['total'] > 0)
          <div id="ch-modulos"></div>
        @else
          {!! $sinDatos('Sin actividad en el periodo') !!}
        @endif
        @if($modulos['aviso'])<div class="aviso-datos"><i class="fa-solid fa-circle-info"></i> {{ $modulos['aviso'] }}</div>@endif
      </div>
      @if($modulos['texto'])<div class="card-foot">{{ $modulos['texto'] }}</div>@endif
    </div>

    <div class="card">
      <div class="card-head"><h3>Hora del día</h3><span class="spacer section-note">% de sesiones</span></div>
      <div class="card-body">
        @if($horas['con_datos'])
          <div id="ch-horas"></div>
          <div class="alert {{ $horas['noche_pct'] >= 10 ? 'warn' : 'info' }} mt-2">
            <div class="ico"><i class="fa-solid fa-moon"></i></div>
            <div class="txt"><b>{{ $horas['noche_pct'] }}% de las sesiones ocurren entre 22:00 y 06:00</b>
              <span>
                @if($horas['area_noche'])
                  En {{ $horas['area_noche'] }} sube a {{ $horas['area_noche_pct'] }}%. El uso nocturno puede ser señal de insomnio o de turnos nocturnos.
                @else
                  {{ number_format($horas['total']) }} eventos con hora en el periodo.
                @endif
              </span></div>
          </div>
        @else
          {!! $sinDatos('Sin actividad en el periodo') !!}
        @endif
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ filemtime(public_path('vendor/paneles/panel.js')) }}"></script>
<script>
(() => {
  const pinta = (id, fn) => { const host = document.getElementById(id); if (host) fn(host); };

  pinta('g-ibi', h => gaugeRing(h, { value: @json($ibi['valor']), color: @json($colorIndice($ibi['valor'])), label: 'IBI' }));
  pinta('g-irc', h => gaugeRing(h, { value: @json($irc['valor']), color: @json($colorIndice($irc['valor'], false)), label: 'IRC' }));
  pinta('g-iro', h => gaugeRing(h, { value: @json($iro['valor']), color: @json($colorIndice($iro['valor'])), label: 'IRO' }));

  const evolucion = @json($evolucion);
  pinta('ch-semaforo', h => stackedArea(h, {
    height: 240,
    labels: evolucion.etiquetas,
    series: evolucion.semaforo.series.filter(s => s.data.some(v => v > 0)),
  }));
  if (evolucion.who5.con_datos) {
    pinta('ch-who5', h => lineChart(h, {
      height: 240, labels: evolucion.etiquetas, series: evolucion.who5.series,
      min: 0, max: 100, refLine: {{ \App\Services\AnaliticaService::CORTE_WHO5 }}, refLabel: 'corte {{ \App\Services\AnaliticaService::CORTE_WHO5 }}',
    }));
  }

  const embudo = @json($embudo['pasos']);
  pinta('ch-embudo', h => barChart(h, {
    labelW: 190,
    data: embudo.map((p, i) => ({ k: p.k, v: p.v, color: i < 4 ? '#2E5D4B' : (i < 7 ? '#D9660F' : '#B02418') })),
  }));

  pinta('ch-modulos', h => barChart(h, { labelW: 270, data: @json($modulos['datos']), color: '#2A78D6' }));

  @if($horas['con_datos'])
  const noche = [22, 23, 0, 1, 2, 3, 4, 5];
  pinta('ch-horas', h => columnChart(h, {
    height: 220, unit: '%', showValues: false,
    data: @json($horas['datos']).map(d => ({ ...d, color: noche.includes(Number(d.k)) ? '#5B4A8A' : '#2A78D6' })),
  }));
  @endif
})();
</script>
@endpush
