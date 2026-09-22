@use('App\Services\ExpedienteClinicoService', 'E')
@use('App\Services\PucholService', 'P')
{{--
  Resumen clínico individual. Lo arma ResumenClinicoService: $paginas dice qué
  secciones lleva cada página según el alcance elegido al emitirlo.
  Se guarda tal cual en entregas_resumen: quien abre el enlace ve esta foto.
--}}
@php
  $marca = 'CONFIDENCIAL · ' . mb_strtoupper($destinatario) . ' · ' . $folio;
  $respuestaAsq = fn (?string $r) => ['si' => 'Sí', 'no' => 'No', 'prefiero_no_contestar' => 'Prefirió no contestar (cuenta como Sí)'][$r] ?? '—';
  $total = count($paginas);
  $nivelTexto = $semaforo['etiqueta'];
  $mdiValor = fn ($m, $k) => match ($k) { 'i8' => max((int) $m->i8a, (int) $m->i8b), 'i10' => max((int) $m->i10a, (int) $m->i10b), default => (int) $m->{$k} };
  $n = 0; // numeración de secciones
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Resumen clínico {{ $folio }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=IBM+Plex+Mono:wght@400;600&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --tinta: #1A2620; --suave: #556860; --linea: #DCE8E0; --verde: #2E5D4B; --rojo: #B02418; --papel: #FFFFFF; }
    * { box-sizing: border-box; }
    body { margin: 0; background: #E6ECE8; color: var(--tinta); font: 12.5px/1.5 'Manrope', system-ui, sans-serif; }
    .pagina { position: relative; width: 210mm; min-height: 297mm; margin: 18px auto; background: var(--papel); padding: 16mm 16mm 22mm; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08); display: flex; flex-direction: column; }
    .marca { position: absolute; inset: -20%; pointer-events: none; display: flex; flex-wrap: wrap; align-content: space-around; justify-content: space-around; transform: rotate(-28deg); opacity: .055; font: 600 13px 'IBM Plex Mono', monospace; z-index: 0; }
    .marca span { padding: 26px 18px; white-space: nowrap; }
    .cuerpo { position: relative; z-index: 1; flex: 1; }
    .franja { display: flex; justify-content: space-between; gap: 12px; font: 600 9.5px 'IBM Plex Mono', monospace; letter-spacing: .08em; color: var(--rojo); border-bottom: 1px solid var(--linea); padding-bottom: 6px; margin-bottom: 14px; text-transform: uppercase; }
    .franja .gris { color: var(--suave); }
    .pie { position: absolute; left: 16mm; right: 16mm; bottom: 9mm; z-index: 1; display: flex; justify-content: space-between; font: 600 9px 'IBM Plex Mono', monospace; color: var(--suave); border-top: 1px solid var(--linea); padding-top: 6px; letter-spacing: .05em; }
    h1 { font: 700 22px 'Fraunces', serif; margin: 0 0 2px; }
    h2 { font: 700 14px 'Fraunces', serif; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 1.5px solid var(--linea); break-after: avoid; }
    h2 .num { color: var(--verde); margin-right: 4px; }
    .marca-app { font: 700 12px 'Fraunces', serif; color: var(--verde); letter-spacing: .02em; }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .muted { color: var(--suave); }
    .small { font-size: 11px; }
    dl { display: grid; grid-template-columns: 150px 1fr; gap: 3px 14px; margin: 0; }
    dt { color: var(--suave); }
    dd { margin: 0; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
    th { text-align: left; color: var(--suave); font-weight: 600; border-bottom: 1px solid var(--linea); padding: 4px 6px; }
    td { border-bottom: 1px solid #F1F5F3; padding: 4px 6px; vertical-align: top; }
    tr { break-inside: avoid; }
    .emision { display: grid; grid-template-columns: 1fr auto; gap: 16px; border-bottom: 3px solid var(--verde); padding-bottom: 12px; margin-bottom: 10px; }
    .emision dl { grid-template-columns: 70px 1fr; font-size: 11.5px; }
    .aviso { margin: 10px 0 4px; padding: 9px 12px; border-radius: 6px; font-size: 11.5px; }
    .aviso.rojo { border: 1.5px solid #E9B7B0; background: #FCEEEC; }
    .aviso.verde { border: 1.5px solid #CFE3D7; background: #F2F8F4; }
    .aviso b { display: block; }
    .nivel { display: inline-block; padding: 1px 9px; border-radius: 999px; color: #FFFFFF; font-weight: 700; font-size: 11px; }
    .nota { margin-top: 8px; padding: 8px 10px; border-left: 3px solid var(--verde); background: #F4F8F6; font-size: 11.5px; }
    .grupo { margin-bottom: 7px; }
    .grupo b { display: block; font-size: 11px; color: var(--suave); }
    .firmas { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 30px; }
    .firmas div { border-top: 1px solid var(--tinta); padding-top: 6px; font-size: 11px; }
    .legal p { font-size: 10.5px; margin: 0 0 6px; }
    .grafica { width: 100%; height: auto; }
    .bandera { color: var(--rojo); font-weight: 700; }
    @page { size: A4; margin: 0; }
    @media print {
      body { background: #FFFFFF; }
      .pagina { margin: 0; box-shadow: none; page-break-after: always; break-after: page; }
      .pagina:last-child { page-break-after: auto; break-after: auto; }
      .marca { opacity: .07; }
    }
    @media (max-width: 820px) {
      .pagina { width: auto; min-height: 0; margin: 0 0 12px; padding: 20px 16px 48px; }
      dl { grid-template-columns: 1fr; }
      .emision { grid-template-columns: 1fr; }
      .firmas { grid-template-columns: 1fr; }
      .pie { left: 16px; right: 16px; }
    }
  </style>
</head>
<body>
@foreach($paginas as $indice => $secciones)
  <section class="pagina">
    <div class="marca" aria-hidden="true">@for($i = 0; $i < 48; $i++)<span>{{ $marca }}</span>@endfor</div>
    <div class="cuerpo">
      <div class="franja"><span>Confidencial · {{ $destinatario }}</span><span class="gris">{{ $folio }}</span></div>

      @if($indice > 0)
        <div class="muted small" style="margin: -6px 0 6px;">Resumen clínico individual · {{ $institucion->nombre_corto }} · {{ $folio }}</div>
      @endif

      @foreach($secciones as $sec)
        @switch($sec['s'])

          {{-- ═══ 1 · Identificación (con la cabecera de emisión) ═══ --}}
          @case('identificacion')
            <div class="emision">
              <div>
                <div class="marca-app">a tu lado</div>
                <h1>Resumen clínico individual</h1>
                <div class="muted">{{ $institucion->razon_social ?: $institucion->nombre_corto }} · {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</div>
              </div>
              <dl>
                <dt>Folio</dt><dd class="mono" style="color: var(--rojo);">{{ $folio }}</dd>
                <dt>Emitido</dt><dd>{{ $generadoEn->format('d/m/Y H:i') }}</dd>
                <dt>Por</dt><dd>{{ $generadoPor->name }} — clínico acreditado A Tu Lado</dd>
                <dt>Para</dt><dd>{{ $destinatarioDetalle }}</dd>
              </dl>
            </div>

            @if($protocoloActivo)
              <div class="aviso rojo"><b>Este documento se emite porque hay un protocolo de crisis activo</b>
                Fuera de un protocolo activo, la entrega de información individual no está permitida por contrato. El enlace expira el {{ $venceEn->format('d/m/Y H:i') }}.</div>
            @else
              <div class="aviso verde"><b>Motivo de la entrega: {{ $motivo }}</b>
                Fuera de un protocolo activo, la entrega de información individual no está permitida por contrato. El enlace expira el {{ $venceEn->format('d/m/Y H:i') }}.</div>
            @endif

            <h2><span class="num">{{ ++$n }} ·</span> Identificación</h2>
            <dl>
              <dt>Persona</dt><dd>{{ $user->name }}</dd>
              <dt>Folio interno</dt><dd class="mono">{{ $membresia->folio }}@if($membresia->numero_empleado) · Emp. {{ $membresia->numero_empleado }}@endif</dd>
              <dt>Organización</dt><dd>{{ $institucion->nombre_corto }}{{ $area ? ' · ' . $area : '' }}{{ $identificacion['puesto'] ? ' · ' . $identificacion['puesto'] : '' }}</dd>
              @if($identificacion['jornada'])<dt>Jornada</dt><dd>{{ $identificacion['jornada'] }}</dd>@endif
              <dt>Caso</dt><dd class="mono">{{ $identificacion['caso'] }}</dd>
              <dt>Clasificación vigente</dt><dd><span class="nivel" style="background: {{ E::COLOR_NIVEL[$semaforo['nivel']] }};{{ $semaforo['nivel'] === 'AMARILLO' ? 'color:#201900;' : '' }}">{{ mb_strtoupper($nivelTexto) }}</span></dd>
              <dt>Tiempo en la plataforma</dt><dd>{{ $identificacion['tiempo'] }}</dd>
            </dl>
            @break

          {{-- ═══ 2 · Por qué se activó el protocolo ═══ --}}
          @case('porque')
            <h2><span class="num">{{ ++$n }} ·</span> {{ $caso ? 'Por qué se activó el protocolo' : 'Contexto clínico' }}</h2>
            @foreach($narrativa as $parrafo)
              <p style="margin: 0 0 6px;">{{ $parrafo }}</p>
            @endforeach
            @break

          {{-- ═══ 3 · Trayectoria de 30 días ═══ --}}
          @case('trayectoria')
            <h2><span class="num">{{ ++$n }} ·</span> Trayectoria del registro diario — 30 días</h2>
            @php $g = $grafica; @endphp
            @if(count($g['puntos']) === 0)
              <p class="muted">Sin registros diarios en los últimos 30 días.</p>
            @else
              <svg class="grafica" viewBox="0 0 {{ $g['ancho'] }} {{ $g['alto'] }}" role="img" aria-label="Registro diario de los últimos 30 días">
                @foreach($g['ejes'] as $eje)
                  <line x1="{{ $g['izq'] }}" x2="{{ $g['ancho'] - $g['der'] }}" y1="{{ $eje['y'] }}" y2="{{ $eje['y'] }}" stroke="#EEF3F0" stroke-width="1"/>
                  <text x="{{ $g['izq'] - 8 }}" y="{{ $eje['y'] + 3.5 }}" font-size="10" text-anchor="end" fill="#556860" font-family="IBM Plex Mono, monospace">{{ $eje['v'] }}</text>
                @endforeach
                @if($g['base'] !== null)
                  <line x1="{{ $g['izq'] }}" x2="{{ $g['ancho'] - $g['der'] }}" y1="{{ $g['base'] }}" y2="{{ $g['base'] }}" stroke="#8EADA4" stroke-width="1.4" stroke-dasharray="5 4"/>
                  <text x="{{ $g['ancho'] - $g['der'] }}" y="{{ $g['base'] - 4 }}" font-size="9.5" text-anchor="end" fill="#556860">línea base 30 d</text>
                @endif
                @if(count($g['puntos']) > 1)
                  <polyline points="{{ $g['linea'] }}" fill="none" stroke="#2E5D4B" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                @endif
                @foreach($g['puntos'] as $p)
                  <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.2" fill="{{ $p['v'] >= 3 ? '#B02418' : '#2E5D4B' }}"><title>{{ $p['dia'] }}: {{ E::ESTADO_DIARIO[$p['v']] ?? $p['v'] }}</title></circle>
                @endforeach
                @foreach($g['marcas'] as $m)
                  <text x="{{ $m['x'] }}" y="{{ max(10, $m['y'] - 8) }}" font-size="9.5" font-weight="700" text-anchor="middle" fill="#B02418" font-family="IBM Plex Mono, monospace">{{ $m['regla'] }}</text>
                @endforeach
                @foreach($g['etiquetas'] as $e)
                  <text x="{{ $e['x'] }}" y="{{ $g['base_y'] + 16 }}" font-size="9.5" text-anchor="middle" fill="#556860" font-family="IBM Plex Mono, monospace">{{ $e['texto'] }}</text>
                @endforeach
              </svg>
              <p class="muted small" style="margin: 4px 0 0;">
                Escala invertida: 0 = excelente, 4 = terrible.
                @if($g['base30'] !== null) Línea punteada: línea base de 30 días ({{ number_format($g['base30'], 1) }}).@endif
                @if($reglas)
                  Reglas disparadas:
                  @foreach(collect($reglas)->sort() as $clave => $fecha){{ E::REGLAS[$clave][0] }} el {{ $fecha->format('d/m') }}{{ $loop->last ? '.' : ', ' }}@endforeach
                @endif
              </p>
            @endif
            @break

          {{-- ═══ 4 · Instrumentos aplicados ═══ --}}
          @case('instrumentos')
            <h2><span class="num">{{ ++$n }} ·</span> Instrumentos aplicados</h2>
            @if($instrumentos === [])
              <p class="muted">La persona aún no ha respondido ningún instrumento.</p>
            @else
              <table>
                <thead><tr><th>Instrumento</th><th>Fecha</th><th>Resultado</th><th>Interpretación</th><th>Ítems que destacan</th></tr></thead>
                <tbody>
                  @foreach($instrumentos as $fila)
                    <tr>
                      <td><b>{{ $fila['instrumento'] }}</b></td>
                      <td class="mono">{{ $fila['fecha'] }}</td>
                      <td class="mono">{{ $fila['resultado'] }}</td>
                      <td>{{ $fila['interpretacion'] }}</td>
                      <td>{{ $fila['destacan'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          {{-- ═══ 5 · WHO-5 íntegro ═══ --}}
          @case('who5')
            <h2><span class="num">{{ ++$n }} ·</span> WHO-5, respuestas íntegras</h2>
            @if($who5)
              <p class="muted small" style="margin: 0 0 4px;">«Durante las últimas dos semanas…» · 0 = nunca, 5 = todo el tiempo · {{ $who5->fecha->format('d/m/Y') }} · crudo {{ $who5->crudo }}/25 · escala {{ $who5->escala }}/100</p>
              <table>
                <thead><tr><th>Ítem</th><th style="width: 190px;">Respuesta</th></tr></thead>
                <tbody>
                  @foreach(E::WHO5_PREGUNTAS as $i => $pregunta)
                    @php $v = (int) $who5->{'i' . $i}; @endphp
                    <tr><td>{{ $i }}. {{ $pregunta }}</td><td class="mono {{ $v <= 1 ? 'bandera' : '' }}">{{ $v }} — {{ E::WHO5_ESCALA[$v] ?? '' }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <p class="muted">Sin aplicación del WHO-5.</p>
            @endif
            @break

          {{-- ═══ 6 · MDI íntegro ═══ --}}
          @case('mdi')
            <h2><span class="num">{{ ++$n }} ·</span> MDI, respuestas íntegras</h2>
            @if($mdi)
              <p class="muted small" style="margin: 0 0 4px;">«¿Con qué frecuencia en las últimas dos semanas…?» · 0 = en ningún momento, 5 = todo el tiempo · {{ $mdi->fecha->format('d/m/Y') }} · total {{ $mdi->total }}/50</p>
              <table>
                <thead><tr><th>Ítem</th><th style="width: 150px;">Respuesta</th></tr></thead>
                <tbody>
                  @foreach(E::MDI_PREGUNTAS as $clave => $pregunta)
                    @php $v = $mdiValor($mdi, $clave); @endphp
                    <tr>
                      <td>{{ substr($clave, 1) }}. {{ $pregunta }}</td>
                      <td class="mono {{ ($clave === 'i6' && $v > 0) || $v >= 4 ? 'bandera' : '' }}">
                        @if($clave === 'i8') máx {{ $v }} <span class="muted">(8a {{ $mdi->i8a }} · 8b {{ $mdi->i8b }})</span>
                        @elseif($clave === 'i10') máx {{ $v }} <span class="muted">(10a {{ $mdi->i10a }} · 10b {{ $mdi->i10b }})</span>
                        @else {{ $v }}
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              @if($encadenamiento)
                <div class="nota"><b>Encadenamiento automático.</b> {{ $encadenamiento }}</div>
              @endif
            @else
              <p class="muted">Sin aplicación del MDI.</p>
            @endif
            @break

          {{-- ═══ 7 · ASQ íntegro ═══ --}}
          @case('asq')
            <h2><span class="num">{{ ++$n }} ·</span> ASQ, respuestas íntegras</h2>
            @if($asq)
              <table>
                <thead><tr><th>Ask Suicide-Screening Questions (NIMH) · versión en español · {{ $asq->fecha->format('d/m/Y') }}</th><th style="width: 210px;">Respuesta</th></tr></thead>
                <tbody>
                  @foreach(E::ASQ_PREGUNTAS as $clave => $pregunta)
                    @continue($clave === 'p5' && $asq->p5 === null)
                    @php $positiva = in_array($asq->{$clave}, ['si', 'prefiero_no_contestar'], true); @endphp
                    <tr><td>{{ substr($clave, 1) }}. {{ $pregunta }}</td><td class="{{ $positiva ? 'bandera' : '' }}">{{ $respuestaAsq($asq->{$clave}) }}</td></tr>
                  @endforeach
                </tbody>
              </table>
              @if($asq->p4 === 'si' && ($asq->metodo || $asq->fecha_intento))
                <div class="nota">Intento previo referido{{ $asq->metodo ? ' · método: ' . $asq->metodo : '' }}{{ $asq->fecha_intento ? ' · cuándo: ' . $asq->fecha_intento : '' }}.</div>
              @endif
              @if($asq->p5 !== null)
                <p class="muted small" style="margin: 6px 0 0;">El ítem 5 no explora método ni planificación: la aplicación deriva de inmediato a la pantalla de apoyo y al contacto humano. Esa exploración corresponde al profesional, no a la plataforma.</p>
              @endif
            @else
              <p class="muted">Sin aplicación del ASQ.</p>
            @endif
            @break

          {{-- ═══ 8 · Texto libre ═══ --}}
          @case('texto')
            <h2><span class="num">{{ ++$n }} ·</span> Texto escrito por la persona</h2>
            <p class="muted small" style="margin: 0 0 4px;">Proviene exclusivamente del campo de texto libre del registro diario. A Tu Lado no analiza conversaciones privadas ni contenido de otras secciones.</p>
            @if($textos === [])
              <p class="muted">Sin texto libre en los últimos 30 días.</p>
            @else
              <table>
                <thead><tr><th style="width: 90px;">Fecha</th><th>Texto</th><th style="width: 150px;">Categoría detectada</th></tr></thead>
                <tbody>
                  @foreach($textos as $t)
                    <tr>
                      <td class="mono">{{ $t['cuando']->format('d/m H:i') }}</td>
                      <td>«{{ \Illuminate\Support\Str::limit($t['texto'], 260) }}»</td>
                      <td class="{{ $t['bandera'] ? 'bandera' : 'muted' }}">{{ $t['categoria'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          {{-- ═══ 9 · Recursos que la persona ya tiene ═══ --}}
          @case('recursos')
            <h2><span class="num">{{ ++$n }} ·</span> Recursos que la persona ya tiene</h2>
            @if($plan)
              <p class="small" style="margin: 0 0 6px;"><b>Plan de seguridad</b> — actualizado {{ $plan->updated_at?->format('d/m/Y') }}</p>
              @forelse($recursos as $g)
                <div class="grupo"><b>{{ $g['titulo'] }}</b>{{ implode(' · ', $g['items']) }}</div>
              @empty
                <p class="muted small">El plan está registrado pero sin contenido.</p>
              @endforelse
            @else
              <p class="muted small">Sin plan de seguridad registrado.</p>
            @endif

            <p class="small" style="margin: 10px 0 4px;"><b>Contactos de emergencia</b></p>
            @forelse($contactos as $c)
              <div class="small">{{ $c->nombre }}{{ $c->relacion ? ' · ' . \Illuminate\Support\Str::lower($c->relacion) : '' }} · <span class="mono">{{ $c->telefono }}</span>@if($c->es_principal) <b>principal</b>@endif</div>
            @empty
              <div class="small muted">Sin registrar: pedirlo en el primer contacto.</div>
            @endforelse

            @if($caso)
              <p class="small" style="margin: 10px 0 4px;"><b>Al momento del disparo</b></p>
              <div class="small">
                @if($caso->estoy_con_alguien) Marcó «estoy con alguien». @endif
                @if($caso->salida_sin_contacto) Salió de la pantalla de apoyo sin pedir ayuda. @endif
                @if(!$caso->estoy_con_alguien && !$caso->salida_sin_contacto) No registró ninguna acción en la pantalla de apoyo. @endif
                Se le mostraron:
                @php $lineas = array_filter(['Línea de la Vida' => $lineasCrisis['linea_vida'] ?? null, 'Línea Amiga Yucatán' => $lineasCrisis['linea_amiga_yucatan'] ?? null, 'Emergencias' => $lineasCrisis['emergencias'] ?? null]); @endphp
                @foreach($lineas as $nombre => $numero){{ $nombre }} ({{ $numero }}){{ $loop->last ? '.' : ', ' }}@endforeach
              </div>
            @endif
            @break

          {{-- ═══ 10 · Cronología ═══ --}}
          @case('cronologia')
            <h2><span class="num">{{ ++$n }} ·</span> Cronología {{ $caso ? 'del caso' : 'reciente' }}</h2>
            <table>
              <thead><tr><th style="width: 105px;">Hora</th><th>Evento</th></tr></thead>
              <tbody>
                @foreach($cronologia as $e)
                  <tr>
                    <td class="mono">{{ $e['cuando'] ? $e['cuando']->format('d/m H:i') : '—' }}</td>
                    <td>{{ $e['texto'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            @break

          {{-- ═══ 11 · Qué sigue + leyendas + firmas ═══ --}}
          @case('que_sigue')
            <h2><span class="num">{{ ++$n }} ·</span> Qué sigue</h2>
            <p style="margin: 0 0 12px;">{{ $queSigue }}</p>
            <div class="legal">
              <p><b>Alcance.</b> A Tu Lado es una plataforma de acompañamiento emocional. No emite diagnósticos ni sustituye la valoración de un profesional de la salud. Los instrumentos citados (WHO-5, MDI, ASQ) son herramientas de tamizaje: sus resultados orientan el contacto, no lo determinan.</p>
              <p><b>Documento confidencial.</b> Se entrega exclusivamente a la persona designada en el encabezado{{ $protocoloActivo ? ', en el marco de un protocolo de crisis activo' : '' }} y de un contrato de confidencialidad vigente. Queda prohibida su reproducción o transmisión a terceros no autorizados. Cada apertura del enlace queda asentada en la bitácora de accesos.</p>
              <p><b>Uso permitido.</b> La información de este documento no puede utilizarse para evaluaciones de desempeño, decisiones de promoción, sanciones, terminación de la relación laboral, ni para ningún acto que discrimine a una persona. Su uso con esos fines constituye incumplimiento del contrato de servicio.</p>
              <p><b>Trazabilidad.</b> Esta emisión quedó asentada con el folio {{ $folio }} en la bitácora de accesos, que es inmutable y queda a disposición de la autoridad competente.</p>
            </div>
            <div class="firmas">
              <div><b>{{ $destinatarioDetalle }}</b><br><span class="muted">Profesional designado · recibe</span></div>
              <div><b>{{ $generadoPor->name }}</b><br><span class="muted">Clínico acreditado A Tu Lado · emite</span></div>
            </div>
            @break

          {{-- ═══════════ ANEXO · Historial de 12 meses ═══════════ --}}
          @case('anexo_portada')
            <h2 style="margin-top: 0;">Anexo · Historial completo de 12 meses</h2>
            <p class="muted small" style="margin: 0 0 8px;">{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}. Incluye todas las aplicaciones, el texto libre y la bitácora de accesos del periodo.</p>
            @break

          @case('anexo_meses')
            <h2>Trayectoria mensual</h2>
            <table>
              <thead><tr><th>Mes</th><th class="mono">Registros</th><th class="mono">Media (0–4)</th><th class="mono">Días Mal/Terrible</th><th class="mono">Reglas</th><th class="mono">Banderas</th></tr></thead>
              <tbody>
                @foreach($anexo['meses'] as $mes)
                  <tr><td>{{ $mes['mes'] }}</td><td class="mono">{{ $mes['registros'] }}</td><td class="mono">{{ $mes['media'] }}</td><td class="mono">{{ $mes['dificiles'] }}</td><td class="mono">{{ $mes['reglas'] }}</td><td class="mono {{ $mes['banderas'] ? 'bandera' : '' }}">{{ $mes['banderas'] }}</td></tr>
                @endforeach
              </tbody>
            </table>
            @break

          @case('anexo_casos')
            <h2>Casos de crisis</h2>
            @if($anexo['casos']->isEmpty())
              <p class="muted">Sin casos de crisis.</p>
            @else
              <table>
                <thead><tr><th>Caso</th><th>Nivel</th><th>Disparado</th><th>Primer contacto</th><th>Estado</th></tr></thead>
                <tbody>
                  @foreach($anexo['casos'] as $c)
                    <tr>
                      <td class="mono">{{ E::codigoCaso($c) }}</td>
                      <td>{{ $c->nivel === 'ROJO_AGUDO' ? 'Rojo agudo' : 'Rojo' }}</td>
                      <td class="mono">{{ $c->disparado_en?->format('d/m/Y H:i') }}</td>
                      <td class="mono">{{ $c->contactado_en ? $c->contactado_en->format('d/m/Y H:i') . ' (' . $c->minutosHastaContacto() . ' min)' : '—' }}</td>
                      <td>{{ $c->estaCerrado() ? 'Cerrado' . ($c->verificadoPor ? ' por ' . $c->verificadoPor->name : '') : 'Abierto' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_who5')
            <h2>WHO-5 · todas las aplicaciones</h2>
            @if($anexo['who5']->isEmpty())
              <p class="muted">Sin aplicaciones en el periodo.</p>
            @else
              <table>
                <thead><tr><th>Fecha</th><th>Origen</th><th class="mono">1</th><th class="mono">2</th><th class="mono">3</th><th class="mono">4</th><th class="mono">5</th><th class="mono">Crudo</th><th class="mono">Escala</th></tr></thead>
                <tbody>
                  @foreach($anexo['who5'] as $a)
                    <tr><td class="mono">{{ $a->fecha->format('d/m/Y') }}</td><td>{{ $a->origen }}</td>
                      @foreach([1, 2, 3, 4, 5] as $i)<td class="mono {{ (int) $a->{'i' . $i} <= 1 ? 'bandera' : '' }}">{{ $a->{'i' . $i} }}</td>@endforeach
                      <td class="mono">{{ $a->crudo }}</td><td class="mono">{{ $a->escala }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_mdi')
            <h2>MDI · todas las aplicaciones</h2>
            @if($anexo['mdi']->isEmpty())
              <p class="muted">Sin aplicaciones en el periodo.</p>
            @else
              <table>
                <thead><tr><th>Fecha</th>@foreach(array_keys(E::MDI_PREGUNTAS) as $k)<th class="mono">{{ substr($k, 1) }}</th>@endforeach<th class="mono">Total</th><th>Nivel</th></tr></thead>
                <tbody>
                  @foreach($anexo['mdi'] as $a)
                    <tr><td class="mono">{{ $a->fecha->format('d/m/Y') }}</td>
                      @foreach(array_keys(E::MDI_PREGUNTAS) as $k)@php $v = $mdiValor($a, $k); @endphp<td class="mono {{ ($k === 'i6' && $v > 0) ? 'bandera' : '' }}">{{ $v }}</td>@endforeach
                      <td class="mono"><b>{{ $a->total }}</b></td><td>{{ E::ETIQUETA_NIVEL[$a->nivel] ?? $a->nivel }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_asq')
            <h2>ASQ · todas las aplicaciones</h2>
            @if($anexo['asq']->isEmpty())
              <p class="muted">Sin aplicaciones en el periodo.</p>
            @else
              <table>
                <thead><tr><th>Fecha</th><th class="mono">1</th><th class="mono">2</th><th class="mono">3</th><th class="mono">4</th><th class="mono">5</th><th>Resultado</th></tr></thead>
                <tbody>
                  @foreach($anexo['asq'] as $a)
                    <tr><td class="mono">{{ $a->fecha->format('d/m/Y') }}</td>
                      @foreach(['p1', 'p2', 'p3', 'p4', 'p5'] as $p)
                        @php $r = $a->{$p}; @endphp
                        <td class="{{ in_array($r, ['si', 'prefiero_no_contestar'], true) ? 'bandera' : '' }}">{{ $r === null ? '—' : ['si' => 'Sí', 'no' => 'No', 'prefiero_no_contestar' => 'Sin resp.'][$r] ?? $r }}</td>
                      @endforeach
                      <td>{{ ['POSITIVA_AGUDA' => 'Positiva aguda', 'POSITIVA_NO_AGUDA' => 'Positiva', 'NEGATIVA' => 'Negativa'][$a->resultado] ?? $a->resultado }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_puchol')
            <h2>Test breve del estado de ánimo (Puchol) · ciclos</h2>
            @if($anexo['puchol']->isEmpty())
              <p class="muted">Sin ciclos en el periodo.</p>
            @else
              <table>
                <thead><tr><th>Ciclo</th><th>Estado</th>@foreach(P::NOMBRE_SECCION as $nombre)<th>{{ $nombre }}</th>@endforeach</tr></thead>
                <tbody>
                  @foreach($anexo['puchol'] as $c)
                    <tr><td class="mono">{{ $c->fecha->format('d/m/Y') }}</td><td>{{ $c->estado }}</td>
                      @foreach(P::SECCIONES as $seccion => [$items, $maximo])
                        <td class="mono {{ $seccion === 'suicidas' && ($c->{$seccion} ?? 0) > 0 ? 'bandera' : '' }}">{{ $c->{$seccion} === null ? '—' : $c->{$seccion} . '/' . $maximo }}</td>
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_texto')
            <h2>Texto libre del periodo{{ $sec['d']['continua'] ? ' (continúa)' : '' }}</h2>
            @if($sec['d']['filas'] === [])
              <p class="muted">Sin texto libre en el periodo.</p>
            @else
              <table>
                <thead><tr><th style="width: 90px;">Fecha</th><th>Texto</th><th style="width: 150px;">Categoría</th></tr></thead>
                <tbody>
                  @foreach($sec['d']['filas'] as $t)
                    <tr><td class="mono">{{ $t['cuando']->format('d/m/y H:i') }}</td><td>«{{ \Illuminate\Support\Str::limit($t['texto'], 220) }}»</td><td class="{{ $t['bandera'] ? 'bandera' : 'muted' }}">{{ $t['categoria'] }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break

          @case('anexo_bitacora')
            <h2>Bitácora de accesos{{ $sec['d']['continua'] ? ' (continúa)' : '' }}</h2>
            @if($sec['d']['filas'] === [])
              <p class="muted">Sin accesos registrados en el periodo.</p>
            @else
              <table>
                <thead><tr><th style="width: 100px;">Cuándo</th><th>Acción</th><th>Quién</th><th>Motivo / detalle</th></tr></thead>
                <tbody>
                  @foreach($sec['d']['filas'] as $b)
                    <tr><td class="mono">{{ $b['cuando']->format('d/m/y H:i') }}</td><td>{{ $b['que'] }}</td><td>{{ $b['quien'] ?? '—' }}</td><td>{{ \Illuminate\Support\Str::limit((string) $b['detalle'], 160) }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @endif
            @break
        @endswitch
      @endforeach
    </div>

    <div class="pie">
      <span>DOCUMENTO CONFIDENCIAL — no reproducir</span>
      <span>{{ $folio }}</span>
      <span>Página {{ $indice + 1 }} de {{ $total }}</span>
    </div>
  </section>
@endforeach
</body>
</html>
