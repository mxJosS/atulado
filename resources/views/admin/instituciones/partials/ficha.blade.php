{{-- Ficha individual. Se inyecta en #ficha-contenido; sólo llega aquí quien tiene acreditación clínica. --}}
@use('App\Services\ExpedienteClinicoService', 'E')
@php
  $nivel = $semaforo['nivel'];
  $colorNivel = E::COLOR_NIVEL[$nivel];
  $iniciales = \App\Models\Institucion::calcularIniciales($user->name ?? '?');
  $nivelLegible = fn (?string $n) => $n ? (E::ETIQUETA_NIVEL[$n] ?? ucfirst(strtolower($n))) : '—';
  $claseDiario = fn (int $v) => [0 => 'sem-verde', 1 => 'sem-verde', 2 => 'sem-amarillo', 3 => 'sem-naranja', 4 => 'sem-rojo'][$v] ?? '';
  $origenes = ['who5' => 'WHO-5', 'mdi' => 'MDI', 'asq' => 'ASQ', 'manual_clinico' => 'Elevación manual', 'puchol' => 'Puchol (impulsos suicidas)', 'diario' => 'Registro diario'];
  $respuestaAsq = fn (?string $r) => ['si' => 'Sí', 'no' => 'No', 'prefiero_no_contestar' => 'Prefirió no contestar'][$r] ?? '—';
  $mdiValor = function ($mdi, string $clave) {
      return match ($clave) {
          'i8' => max((int) $mdi->i8a, (int) $mdi->i8b),
          'i10' => max((int) $mdi->i10a, (int) $mdi->i10b),
          default => (int) $mdi->{$clave},
      };
  };

  $datosGraficas = [
      'animo' => $grafica_animo,
      'who5' => [
          'labels' => $who5_historial->map(fn ($a) => $a->fecha->format('d/m/y'))->all(),
          'data' => $who5_historial->pluck('escala')->map(fn ($v) => (int) $v)->all(),
      ],
  ];
@endphp

<div class="modal-head">
  <div class="who-cell" style="gap: .9rem;">
    <div class="av" style="width: 44px; height: 44px; background: {{ $colorNivel }}; font-size: .9rem;{{ $nivel === 'AMARILLO' ? 'color:#201900;' : '' }}">{{ $iniciales }}</div>
    <div>
      <h2>{{ $user->name }}</h2>
      <div class="sub">
        {{ $membresia->folio }}@if($membresia->numero_empleado) · Emp. {{ $membresia->numero_empleado }}@endif
        @if($area) · {{ $area }}@endif · {{ $institucion->nombre_corto }}
      </div>
    </div>
  </div>
  <span style="align-self: center;">@include('admin.instituciones.partials.semaforo', ['s' => $semaforo])</span>
  <button class="x" type="button" data-close="m-ficha" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
</div>

<div class="modal-body">

  <div class="alert crit mb-3">
    <div class="ico"><i class="fa-solid fa-fingerprint"></i></div>
    <div class="txt"><b>Acceso registrado en bitácora</b>
      <span>Abierto por {{ $acceso->profesional?->name }} (clínico acreditado) el {{ $acceso->created_at->format('d/m/Y H:i') }} · motivo declarado: «{{ $acceso->motivo }}». Este registro es inmutable: no se puede editar ni borrar.</span></div>
  </div>

  <div class="tabs" data-tabs="ficha">
    <button class="tab on" type="button" data-pane="resumen">Resumen clínico</button>
    <button class="tab" type="button" data-pane="diario">Registro diario</button>
    <button class="tab" type="button" data-pane="who5">WHO-5</button>
    <button class="tab" type="button" data-pane="mdi">MDI</button>
    <button class="tab" type="button" data-pane="asq">ASQ</button>
    <button class="tab" type="button" data-pane="lexico">Texto libre y banderas</button>
    <button class="tab" type="button" data-pane="plan">Plan de seguridad</button>
    <button class="tab" type="button" data-pane="bitacora">Bitácora</button>
  </div>

  {{-- ─── RESUMEN ─── --}}
  <div class="tabpane on" data-tabpane="ficha" data-pane="resumen">
    <div class="grid g-2-1">
      <div class="card">
        <div class="card-head"><h3>Trayectoria del ánimo · 30 días</h3>
          <span class="spacer section-note">0 excelente — 4 terrible (valor invertido)</span></div>
        <div class="card-body">
          <div id="ch-animo">
            @if(count($grafica_animo['data']) < 2)
              <p class="small muted" style="margin: 0;">Hacen falta al menos dos registros diarios en los últimos 30 días para trazar la trayectoria.</p>
            @endif
          </div>
          <div class="stack-legend">
            <span class="legend-item"><span class="sw" style="background: #2E5D4B;"></span> Registro diario</span>
            @if($grafica_animo['base30'] !== null)
              <span class="legend-item"><span class="sw" style="background: #8EADA4; border-radius: 0;"></span> Línea base 30 d ({{ $grafica_animo['base30'] }})</span>
            @endif
          </div>
        </div>
      </div>

      <div class="col" style="gap: 1rem;">
        <div class="card card-pad">
          <div class="kpi-label mb-1">Clasificación vigente</div>
          @include('admin.instituciones.partials.semaforo', ['s' => $semaforo, 'estilo' => 'font-size:.85rem'])
          <dl class="dl mt-2">
            <dt>Origen</dt><dd>{{ $clasificacion ? ($origenes[$clasificacion->origen] ?? $clasificacion->origen) : 'Sin clasificación aún' }}</dd>
            @if($clasificacion)<dt>Desde</dt><dd>{{ $clasificacion->fecha->format('d/m/Y') }}</dd>@endif
            <dt>Caso</dt>
            <dd class="mono">
              @if($caso_abierto)
                {{ E::codigoCaso($caso_abierto) }} · {{ $caso_abierto->contactado_en ? 'en atención' : 'abierto, sin contacto' }}
              @else
                Sin caso abierto
              @endif
            </dd>
          </dl>
          @if($caso_abierto)
            <div class="row mt-2" style="gap: .4rem; flex-wrap: wrap;">
              @if(!$caso_abierto->contactado_en)
                <button type="button" class="btn btn-sm btn-danger" data-open="m-caso-contacto"><i class="fa-solid fa-phone"></i> Registrar contacto humano</button>
              @else
                <button type="button" class="btn btn-sm" data-open="m-caso-cierre"><i class="fa-solid fa-lock"></i> Cerrar caso</button>
              @endif
            </div>
          @endif
        </div>

        <div class="card card-pad">
          <div class="kpi-label mb-1">Reglas de vigilancia disparadas · 30 d</div>
          <div class="col" style="gap: .4rem;">
            @foreach(E::REGLAS as $clave => [$codigo, , $descripcion])
              <div class="row">
                @if(isset($reglas[$clave]))
                  <span class="chip red">{{ $codigo }}</span> <span class="small">{{ $descripcion }} — {{ $reglas[$clave]->format('d/m') }}</span>
                @else
                  <span class="chip">{{ $codigo }}</span> <span class="small muted">{{ $descripcion }} — no se ha disparado</span>
                @endif
              </div>
            @endforeach
          </div>
        </div>

        <div class="card card-pad">
          <div class="kpi-label mb-1">Contacto de emergencia</div>
          @if($principal = $contactos->first())
            <div class="strong">{{ $principal->nombre }}</div>
            <div class="small muted">{{ $principal->relacion ?: 'Sin parentesco' }}{{ $principal->es_principal ? ' · principal' : '' }}</div>
            <div class="mono mt-1">{{ $principal->telefono }}</div>
          @else
            <div class="small muted">Sin registrar. <span class="chip amber xsmall">pedirlo en el primer contacto</span></div>
          @endif
        </div>
      </div>
    </div>

    <div class="grid g-4 mt-2">
      <div class="kpi">
        <div class="kpi-label">WHO-5 actual</div>
        <div class="kpi-value">{{ $who5?->escala ?? '—' }}</div>
        <div class="kpi-foot">
          @if($who5 && $who5_anterior)
            @php $delta = $who5->escala - $who5_anterior->escala; @endphp
            <span class="delta {{ $delta < 0 ? 'down' : 'up' }}">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</span> vs. aplicación previa
          @elseif($who5)
            primera aplicación
          @else
            sin aplicación
          @endif
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-label">MDI total</div>
        <div class="kpi-value">{{ $mdi?->total ?? '—' }} @if($mdi)<span class="unit">/ 50</span>@endif</div>
        <div class="kpi-foot">{{ $mdi ? 'nivel ' . $nivelLegible($mdi->nivel) . ' · ítem 6 en ' . $mdi->i6 : 'sin aplicación' }}</div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Registros en 30 d</div>
        <div class="kpi-value">{{ $registros_30 }}</div>
        <div class="kpi-foot">{{ $adherencia }}% de adherencia</div>
      </div>
      <div class="kpi">
        <div class="kpi-label">Racha actual</div>
        <div class="kpi-value">{{ $racha }} <span class="unit">{{ $racha === 1 ? 'día' : 'días' }}</span></div>
        <div class="kpi-foot">días seguidos con registro</div>
      </div>
    </div>
  </div>

  {{-- ─── DIARIO ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="diario">
    <div class="card">
      <div class="card-head"><h3>Registro diario de caritas · últimos 14 registros</h3>
        <span class="spacer section-note">Capa 0 del motor clínico</span></div>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Fecha</th><th>Hora</th><th>Estado</th><th class="num">Valor invertido</th><th>Texto libre</th><th>Bandera</th><th>Regla</th></tr></thead>
          <tbody>
            @forelse($diario as ['log' => $log, 'regla' => $regla])
              @php $v = (int) $log->valor; @endphp
              <tr class="{{ $log->bandera_lexica ? 'row-critical' : ($v >= 4 ? 'row-red' : '') }}">
                <td class="mono">{{ $log->logged_date->format('d/m/Y') }}</td>
                <td class="mono">{{ $log->created_at?->format('H:i') }}</td>
                <td><span class="sem {{ $claseDiario($v) }}"><span class="dot"></span> {{ E::ESTADO_DIARIO[$v] ?? '—' }}</span></td>
                <td class="num">{{ $v }}</td>
                <td class="small">
                  @if($log->journal_entry) «{{ \Illuminate\Support\Str::limit($log->journal_entry, 160) }}» @else <span class="muted">—</span> @endif
                </td>
                <td>@if($log->bandera_lexica)<span class="chip red">bandera</span>@else — @endif</td>
                <td>@if($regla && isset(E::REGLAS[$regla]))<span class="chip red">{{ E::REGLAS[$regla][0] }}</span>@else — @endif</td>
              </tr>
            @empty
              <tr><td colspan="7" class="muted" style="text-align: center; padding: 1.5rem;">Todavía no hay registros diarios.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($base30 !== null)
        <div class="card-foot">La <b>línea base 30 días</b> de esta persona es {{ $base30 }} y su <b>media móvil de 7 días</b> es {{ $movil7 }}. Una desviación sostenida de 1 punto o más dispara R1.</div>
      @endif
    </div>
  </div>

  {{-- ─── WHO-5 ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="who5">
    @if($who5)
      <div class="row mb-2 wrap">
        <span class="chip mono">Aplicación {{ $who5->fecha->format('d/m/Y') }}@if($who5->origen) · origen: {{ str_replace('_', ' ', $who5->origen) }}@endif</span>
        <span class="chip {{ $who5->escala < 50 ? 'red' : 'green' }}">Crudo {{ $who5->crudo }} / 25 · Escala {{ $who5->escala }} / 100</span>
        @if($who5_anterior)<span class="chip">Anterior: {{ $who5_anterior->escala }} ({{ $who5_anterior->fecha->format('d/m/Y') }})</span>@endif
      </div>
      <div class="card card-pad">
        <div class="section-note mb-2">En las últimas dos semanas… <span class="muted">(0 = nunca · 5 = todo el tiempo)</span></div>
        @foreach(E::WHO5_PREGUNTAS as $n => $pregunta)
          @php $valor = (int) $who5->{'i' . $n}; @endphp
          <div class="qa-item {{ $valor <= 1 ? 'flag' : ($valor <= 2 ? 'warn' : '') }}">
            <span class="qa-num">{{ $n }}</span><div class="qa-q">{{ $pregunta }}</div><span class="qa-a">{{ $valor }} — {{ E::WHO5_ESCALA[$valor] ?? '' }}</span>
          </div>
        @endforeach
      </div>
      <div class="card mt-2">
        <div class="card-head"><h3>Histórico de aplicaciones</h3></div>
        <div class="card-body">
          <div id="ch-who5">
            @if($who5_historial->count() < 2)<p class="small muted" style="margin: 0;">Sólo hay una aplicación; la gráfica aparece desde la segunda.</p>@endif
          </div>
        </div>
        <div class="card-foot">El corte de 50 es el umbral que la literatura asocia a revisión clínica.</div>
      </div>
    @else
      <div class="card card-pad"><p class="small muted" style="margin: 0;">Esta persona todavía no ha respondido el WHO-5.</p></div>
    @endif
  </div>

  {{-- ─── MDI ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="mdi">
    @if($mdi)
      <div class="row mb-2 wrap">
        <span class="chip mono">Aplicación {{ $mdi->fecha->format('d/m/Y') }}</span>
        <span class="chip {{ in_array($mdi->nivel, ['ROJO', 'NARANJA'], true) ? 'red' : 'amber' }}">Total {{ $mdi->total }} / 50 · nivel {{ strtoupper($nivelLegible($mdi->nivel)) }}</span>
        @if($mdi->i6 > 0)<span class="chip red"><i class="fa-solid fa-triangle-exclamation"></i> Ítem 6 (ideación) en {{ $mdi->i6 }}</span>@endif
      </div>
      <div class="card card-pad">
        <div class="section-note mb-2">¿Con qué frecuencia en las últimas dos semanas…? <span class="muted">(0 = nunca · 5 = todo el tiempo)</span></div>
        @foreach(E::MDI_PREGUNTAS as $clave => $pregunta)
          @php $valor = $mdiValor($mdi, $clave); @endphp
          <div class="qa-item {{ $valor >= 4 || ($clave === 'i6' && $valor > 0) ? 'flag' : ($valor >= 3 ? 'warn' : '') }}">
            <span class="qa-num">{{ substr($clave, 1) }}</span>
            <div class="qa-q">@if($clave === 'i6')<b>{{ $pregunta }}</b>@else{{ $pregunta }}@endif</div>
            <span class="qa-a">{{ in_array($clave, ['i8', 'i10'], true) ? 'máx ' : '' }}{{ $valor }}</span>
          </div>
        @endforeach
      </div>
      @if($mdi->i6 > 0)
        <div class="alert crit mt-2">
          <div class="ico"><i class="fa-solid fa-arrow-right-long"></i></div>
          <div class="txt"><b>Encadenamiento automático</b><span>El ítem 6 por encima de 0 obliga a aplicar el ASQ de inmediato.</span></div>
        </div>
      @endif
    @else
      <div class="card card-pad"><p class="small muted" style="margin: 0;">Esta persona todavía no ha respondido el MDI.</p></div>
    @endif
  </div>

  {{-- ─── ASQ ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="asq">
    @if($asq)
      <div class="row mb-2 wrap">
        <span class="chip mono">Aplicación {{ $asq->fecha->format('d/m/Y') }}</span>
        <span class="chip {{ $asq_resumen['tono'] }}">Resultado: {{ strtoupper($asq_resumen['texto']) }}</span>
        @if($asq->nivel)<span class="chip red">Nivel: {{ strtoupper($nivelLegible($asq->nivel)) }}</span>@endif
      </div>
      <div class="card card-pad">
        <div class="section-note mb-2">Ask Suicide-Screening Questions (NIMH) · versión en español</div>
        @foreach(E::ASQ_PREGUNTAS as $clave => $pregunta)
          @continue($clave === 'p5' && $asq->p5 === null)
          @php $positiva = in_array($asq->{$clave}, ['si', 'prefiero_no_contestar'], true); @endphp
          <div class="qa-item {{ $positiva ? 'flag' : '' }}">
            <span class="qa-num">{{ substr($clave, 1) }}</span>
            <div class="qa-q">@if($clave === 'p5')<b>{{ $pregunta }}</b>@else{{ $pregunta }}@endif</div>
            <span class="qa-a">{{ $respuestaAsq($asq->{$clave}) }}</span>
          </div>
        @endforeach
        @if($asq->metodo || $asq->fecha_intento)
          <p class="small muted mt-2" style="margin-bottom: 0;">
            @if($asq->metodo) Método referido: {{ $asq->metodo }}. @endif
            @if($asq->fecha_intento) Fecha del intento: {{ $asq->fecha_intento }}. @endif
          </p>
        @endif
      </div>
      @if($asq->resultado === 'POSITIVA_AGUDA')
        <div class="alert crit mt-2">
          <div class="ico"><i class="fa-solid fa-tower-broadcast"></i></div>
          <div class="txt"><b>El ítem 5 positivo define POSITIVA AGUDA</b>
            <span>El caso no se cierra hasta que un profesional registre el contacto humano verificado.</span></div>
        </div>
      @endif
    @else
      <div class="card card-pad"><p class="small muted" style="margin: 0;">No aplica: el ASQ sólo se abre cuando el ítem 6 del MDI es positivo.</p></div>
    @endif
  </div>

  {{-- ─── LÉXICO ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="lexico">
    <div class="card">
      <div class="card-head"><h3>Detecciones del filtro léxico</h3>
        <span class="spacer section-note">Sobre el texto libre del registro diario · nunca sobre conversaciones privadas</span></div>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Fecha</th><th>Términos</th><th>Fragmento</th><th>Estado ese día</th></tr></thead>
          <tbody>
            @forelse($lexico as $log)
              <tr class="row-critical">
                <td class="mono">{{ $log->logged_date->format('d/m') }} {{ $log->created_at?->format('H:i') }}</td>
                <td class="mono xsmall">{{ collect($log->terminos_detectados ?? [])->map(fn ($t) => '«' . $t . '»')->implode(', ') ?: '—' }}</td>
                <td class="small">«{{ \Illuminate\Support\Str::limit($log->journal_entry, 200) }}»</td>
                <td class="small">{{ E::ESTADO_DIARIO[(int) $log->valor] ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="muted" style="text-align: center; padding: 1.5rem;">Sin detecciones del filtro léxico.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-foot">El filtro compara el texto libre contra la lista cerrada de términos críticos (<code>config/clinical.php</code>). Levanta una bandera para el plano clínico; no clasifica por sí solo.</div>
    </div>
    <div class="card mt-2">
      <div class="card-head"><h3>Puchol · test breve del estado de ánimo</h3>
        @if($puchol)<span class="spacer section-note">ciclo del {{ $puchol->fecha->format('d/m/Y') }} · {{ ['en_curso' => 'en curso', 'completado' => 'completo', 'abandonado' => 'incompleto'][$puchol->estado] ?? $puchol->estado }}</span>@endif
      </div>
      <div class="card-body">
        @if($puchol)
          @foreach(\App\Services\PucholService::SECCIONES as $seccion => [$items, $maximo])
            @php
              $total = $puchol->{$seccion};
              $lectura = \App\Services\PucholService::interpretar($seccion, $total);
              $color = $seccion === 'suicidas' && $total > 0 ? '#6E140C' : ($total !== null && $total / $maximo >= 0.5 ? '#B02418' : ($total !== null && $total / $maximo >= 0.25 ? '#D9660F' : '#2E5D4B'));
            @endphp
            <div style="margin-bottom: 0.85rem;">
              <div style="display: flex; justify-content: space-between; gap: 0.75rem; font-size: 0.82rem;">
                <b>{{ \App\Services\PucholService::NOMBRE_SECCION[$seccion] }}</b>
                <span class="mono">{{ $total === null ? 'pendiente' : $total . ' / ' . $maximo }}</span>
              </div>
              <div style="background: #EEF4F0; border-radius: 5px; height: 10px; margin: 0.3rem 0;">
                <div style="width: {{ $total === null ? 0 : max(2, round($total / $maximo * 100)) }}%; background: {{ $color }}; height: 10px; border-radius: 5px;"></div>
              </div>
              <div class="small {{ $seccion === 'suicidas' && $total > 0 ? '' : 'muted' }}" style="{{ $seccion === 'suicidas' && $total > 0 ? 'color: #8C1C10; font-weight: 700;' : '' }}">
                {{ $total === null ? 'Aún sin responder en este ciclo.' : ($lectura ?? 'El instrumento no trae clave de interpretación para esta sección.') }}
              </div>
            </div>
          @endforeach

          @if($puchol_historial->count() > 1)
            <table class="data" style="margin-top: 0.5rem;">
              <thead><tr><th>Ciclo</th><th>Estado</th><th class="num">Ansiedad</th><th class="num">Física</th><th class="num">Depresión</th><th class="num">Impulsos</th></tr></thead>
              <tbody>
                @foreach($puchol_historial as $ciclo)
                  <tr>
                    <td class="mono">{{ $ciclo->fecha->format('d/m/Y') }}{{ $ciclo->origen === 'adelantado' ? ' · adelantado' : '' }}</td>
                    <td class="small">{{ ['en_curso' => 'En curso', 'completado' => 'Completo', 'abandonado' => 'Incompleto'][$ciclo->estado] ?? $ciclo->estado }}</td>
                    <td class="num">{{ $ciclo->ansiedad ?? '—' }}</td>
                    <td class="num">{{ $ciclo->fisica ?? '—' }}</td>
                    <td class="num">{{ $ciclo->depresion ?? '—' }}</td>
                    <td class="num">{{ $ciclo->suicidas ?? '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        @else
          <p class="small muted" style="margin: 0;">Sin respuestas todavía. Se aplica por partes cortas cada 30 días (o antes, si el MDI marca estrés o sueño alto).</p>
        @endif
      </div>
      <div class="card-foot">Resultados informativos: no cambian el semáforo. La única excepción son los impulsos suicidas: cualquier valor mayor a 0 abre un caso Rojo al momento.</div>
    </div>
  </div>

  {{-- ─── PLAN ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="plan">
    <div class="grid g-2">
      <div class="card">
        <div class="card-head"><h3>Plan de seguridad</h3>
          @if($plan)<span class="chip green spacer">Actualizado {{ $plan->updated_at->format('d/m') }}</span>@endif</div>
        <div class="card-body">
          @if($plan)
            @php
              $lista = fn ($v) => collect(is_array($v) ? $v : (is_array($j = json_decode((string) $v, true)) ? $j : array_filter(array_map('trim', explode("\n", (string) $v)))))
                  ->map(fn ($x) => is_array($x) ? implode(' · ', array_filter($x)) : $x)->filter()->implode(' · ');
              $secciones = [
                  'Señales de alarma que reconozco' => $plan->warning_signs,
                  'Cosas que me ayudan por mi cuenta' => $plan->internal_coping,
                  'Actividades y personas que me distraen' => $plan->social_distractions,
                  'Personas a las que puedo llamar' => $plan->trusted_contacts,
                  'Profesionales y servicios' => $plan->professional_contacts,
                  'Cómo hago mi entorno más seguro' => $plan->safe_environment_steps,
                  'Razones para vivir' => $plan->reasons_to_live,
              ];
            @endphp
            @foreach($secciones as $titulo => $valor)
              @continue(($texto = $lista($valor)) === '')
              <div class="doc-section-title">{{ $titulo }}</div>
              <p class="small">{{ $texto }}</p>
            @endforeach
          @else
            <p class="small muted" style="margin: 0;">La persona aún no ha llenado su plan de seguridad.</p>
          @endif
        </div>
      </div>
      <div class="col" style="gap: 1rem;">
        <div class="card">
          <div class="card-head"><h3>Contactos de emergencia</h3></div>
          <div class="card-body col" style="gap: .7rem;">
            @forelse($contactos as $c)
              @if(!$loop->first)<div class="divider" style="margin: .3rem 0;"></div>@endif
              <div>
                <div class="strong">{{ $c->nombre }}</div>
                <div class="small muted">{{ $c->relacion ?: 'Sin parentesco' }}{{ $c->es_principal ? ' · principal' : '' }}</div>
                <div class="mono small">{{ $c->telefono }}</div>
              </div>
            @empty
              <p class="small muted" style="margin: 0;">Sin contactos registrados.</p>
            @endforelse
          </div>
        </div>
        <div class="card">
          <div class="card-head"><h3>Líneas de ayuda</h3></div>
          <div class="card-body">
            <div class="small"><b>Línea de la Vida</b> · {{ config('clinical.crisis_numbers.linea_vida') }}</div>
            <div class="small mt-1"><b>Línea Amiga Yucatán</b> · {{ config('clinical.crisis_numbers.linea_amiga_yucatan') }}</div>
            <div class="small mt-1"><b>Emergencias</b> · {{ config('clinical.crisis_numbers.emergencias') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ─── BITÁCORA ─── --}}
  <div class="tabpane" data-tabpane="ficha" data-pane="bitacora">
    <div class="card card-pad">
      <div class="tl">
        @foreach($bitacora as $evento)
          <div class="tl-item {{ $evento['tono'] }}">
            <div class="tl-when">{{ $evento['cuando']->format('d/m/Y · H:i') }}</div>
            <div class="tl-what">{{ $evento['que'] }}</div>
            @if($evento['detalle'])<div class="tl-detail">{{ $evento['detalle'] }}</div>@endif
          </div>
        @endforeach
        @if($caso_abierto)
          <div class="tl-item green">
            <div class="tl-when">pendiente</div>
            <div class="tl-what">Cierre con contacto humano verificado — {{ E::codigoCaso($caso_abierto) }}</div>
            <div class="tl-detail">Requiere la nota del profesional. El sistema no cierra este caso por tiempo.</div>
          </div>
        @endif
      </div>
    </div>
  </div>

</div>

<div class="modal-foot">
  <span class="small muted"><i class="fa-solid fa-shield-halved"></i> La institución no tiene acceso a ninguna pestaña de esta ficha.</span>
  <div class="spacer"></div>
  <button class="btn" type="button" data-close="m-ficha">Cerrar</button>
  <button class="btn btn-dark" type="button" data-open="m-entrega"><i class="fa-solid fa-file-pdf"></i> Generar resumen para el profesional</button>
  <button class="btn btn-danger" type="button" data-open="m-escalar"><i class="fa-solid fa-tower-broadcast"></i> Escalar protocolo</button>
</div>

<script type="application/json" id="ficha-datos">@json($datosGraficas)</script>

{{-- ═════ Modales de acción: el script los mueve a <body> al abrir la ficha ═════ --}}

<div class="modal-backdrop" id="m-entrega" data-ficha-modal>
  @php
    $ndaVigente = $institucion->profesional_nda_hasta && !$institucion->profesional_nda_hasta->isPast();
    $profesionalListo = $institucion->profesional_nombre && $institucion->profesional_email && $ndaVigente;
    $guardiaLista = (bool) config('atulado.guardia.email');
  @endphp
  <div class="modal narrow">
    <div class="modal-head">
      <div><h2>Entregar resumen clínico</h2><div class="sub">Se envía por correo como enlace que vence · folio y marca de agua</div></div>
      <button class="x" type="button" data-close="m-entrega" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.instituciones.ficha.resumen', [$institucion, $membresia]) }}">
      @csrf
      <div class="modal-body">
        <div class="field mb-2"><label>Destinatario</label>
          <select class="select" name="destinatario" required>
            @if($institucion->profesional_nombre)
              <option value="profesional" @disabled(!$profesionalListo)>
                {{ $institucion->profesional_nombre }}{{ $institucion->profesional_cedula ? ' (Céd. ' . $institucion->profesional_cedula . ')' : '' }} — profesional designado
                @if(!$institucion->profesional_email) · SIN CORREO @elseif(!$ndaVigente) · NDA NO VIGENTE @else · NDA hasta {{ $institucion->profesional_nda_hasta->format('d/m/Y') }} @endif
              </option>
            @endif
            <option value="guardia" @disabled(!$guardiaLista) @selected(!$profesionalListo)>
              {{ config('atulado.guardia.nombre') }}{{ $guardiaLista ? '' : ' · SIN CORREO CONFIGURADO' }}
            </option>
          </select>
          <span class="hint">Sólo profesionales con correo y acuerdo de confidencialidad vigente. Nunca RR. HH. ni la dirección de la institución.</span>
        </div>
        <div class="field mb-2"><label>Motivo de la entrega</label>
          <select class="select" name="motivo" required>
            @if($caso_abierto)<option>Protocolo de crisis activo ({{ E::codigoCaso($caso_abierto) }})</option>@endif
            <option>Seguimiento de caso ya abierto</option>
            <option>Canalización a servicio externo</option>
          </select>
          <span class="hint">Fuera de un protocolo activo, la entrega de información individual no está permitida por contrato.</span>
        </div>
        <div class="field mb-2"><label>Vigencia del enlace</label>
          <select class="select" name="vigencia_horas" required>
            <option value="72" selected>72 horas (recomendado)</option>
            <option value="24">24 horas</option>
            <option value="168">7 días</option>
          </select>
          <span class="hint">Al vencer, el enlace deja de abrir. Se registra cuántas veces se abrió.</span>
        </div>
        <div class="alert warn">
          <div class="ico"><i class="fa-solid fa-file-signature"></i></div>
          <div class="txt"><b>Se adjunta la leyenda del contrato</b><span>Prohibido usar esta información para evaluación de desempeño, sanciones, despido o cualquier acto discriminatorio. La entrega queda en la bitácora.</span></div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="spacer"></div>
        <button class="btn" type="button" data-close="m-entrega">Cancelar</button>
        <button class="btn btn-primary" type="submit" @disabled(!$profesionalListo && !$guardiaLista)><i class="fa-solid fa-paper-plane"></i> Generar y enviar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-backdrop" id="m-escalar" data-ficha-modal>
  <div class="modal narrow">
    <div class="modal-head" style="background: #6E140C;">
      <div><h2>Escalar protocolo</h2><div class="sub">{{ $user->name }} · {{ $membresia->folio }} · hoy: {{ $semaforo['etiqueta'] }}</div></div>
      <button class="x" type="button" data-close="m-escalar" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.instituciones.ficha.escalar', [$institucion, $membresia]) }}">
      @csrf
      <div class="modal-body">
        <div class="field mb-2"><label>Nuevo nivel</label>
          <select class="select" name="nivel" required>
            <option value="ROJO_AGUDO">Rojo agudo — contacto en menos de {{ config('clinical.asq.tiempo_contacto_agudo_min', 5) }} min</option>
            <option value="ROJO" selected>Rojo — contacto el mismo día</option>
            <option value="NARANJA">Naranja — seguimiento cercano, sin caso de crisis</option>
          </select>
        </div>
        <div class="field mb-2"><label>Justificación clínica</label>
          <textarea class="input" name="justificacion" rows="3" minlength="10" maxlength="1000" required placeholder="Qué observaste que justifica elevar el nivel"></textarea>
        </div>
        <div class="alert crit">
          <div class="ico"><i class="fa-solid fa-tower-broadcast"></i></div>
          <div class="txt"><b>Rojo y Rojo agudo abren un caso de crisis</b><span>Se notifica al profesional designado y el caso sólo se cierra con contacto humano verificado. La elevación queda en la bitácora con tu nombre.</span></div>
        </div>
      </div>
      <div class="modal-foot">
        <div class="spacer"></div>
        <button class="btn" type="button" data-close="m-escalar">Cancelar</button>
        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-tower-broadcast"></i> Escalar</button>
      </div>
    </form>
  </div>
</div>

@if($caso_abierto)
  <div class="modal-backdrop" id="m-caso-contacto" data-ficha-modal>
    <div class="modal narrow">
      <div class="modal-head">
        <div><h2>Registrar contacto humano</h2><div class="sub">{{ E::codigoCaso($caso_abierto) }} · disparado {{ $caso_abierto->disparado_en?->format('d/m/Y H:i') }}</div></div>
        <button class="x" type="button" data-close="m-caso-contacto" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" action="{{ route('admin.instituciones.caso.contacto', [$institucion, $membresia, $caso_abierto]) }}">
        @csrf
        <div class="modal-body">
          <div class="field"><label>Nota del contacto (opcional)</label>
            <textarea class="input" name="nota" rows="3" maxlength="1000" placeholder="Ej. llamada telefónica, la persona está acompañada por su madre"></textarea>
            <span class="hint">Registra sólo un contacto real con la persona. Queda con tu nombre y la hora actual.</span>
          </div>
        </div>
        <div class="modal-foot">
          <div class="spacer"></div>
          <button class="btn" type="button" data-close="m-caso-contacto">Cancelar</button>
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-phone"></i> Registrar contacto</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-backdrop" id="m-caso-cierre" data-ficha-modal>
    <div class="modal narrow">
      <div class="modal-head">
        <div><h2>Cerrar caso</h2><div class="sub">{{ E::codigoCaso($caso_abierto) }} · contacto {{ $caso_abierto->contactado_en?->format('d/m/Y H:i') }}</div></div>
        <button class="x" type="button" data-close="m-caso-cierre" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" action="{{ route('admin.instituciones.caso.cerrar', [$institucion, $membresia, $caso_abierto]) }}">
        @csrf
        <div class="modal-body">
          <div class="field"><label>Nota de cierre</label>
            <textarea class="input" name="notas" rows="4" minlength="10" maxlength="2000" required placeholder="Cómo se verificó que la persona está a salvo y qué seguimiento queda"></textarea>
            <span class="hint">Un caso rojo no se cierra por puntaje: sólo con contacto humano verificado y esta nota.</span>
          </div>
        </div>
        <div class="modal-foot">
          <div class="spacer"></div>
          <button class="btn" type="button" data-close="m-caso-cierre">Cancelar</button>
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-lock"></i> Cerrar caso</button>
        </div>
      </form>
    </div>
  </div>
@endif
