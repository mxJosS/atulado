@use('App\Services\ExpedienteClinicoService', 'E')
@php
  $marca = 'CONFIDENCIAL · ' . $destinatario . ' · ' . $folio;
  $respuestaAsq = fn (?string $r) => ['si' => 'Sí', 'no' => 'No', 'prefiero_no_contestar' => 'Prefirió no contestar'][$r] ?? '—';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Resumen clínico {{ $folio }}</title>
  @include('partials.iconos')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=IBM+Plex+Mono:wght@400;600&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --tinta: #1A2620; --suave: #556860; --linea: #DCE8E0; --verde: #2E5D4B; --rojo: #B02418; }
    * { box-sizing: border-box; }
    body { margin: 0; background: #EEF3F0; color: var(--tinta); font: 13px/1.5 'Manrope', system-ui, sans-serif; }
    .hoja { position: relative; max-width: 820px; margin: 24px auto; background: #FFFFFF; padding: 40px 48px; border-radius: 6px; overflow: hidden; }
    .marca { position: fixed; inset: 0; pointer-events: none; display: flex; flex-wrap: wrap; align-content: space-around; justify-content: space-around; transform: rotate(-24deg) scale(1.4); opacity: .06; font: 600 15px 'IBM Plex Mono', monospace; z-index: 0; }
    .marca span { padding: 34px 24px; white-space: nowrap; }
    .contenido { position: relative; z-index: 1; }
    h1 { font: 700 22px 'Fraunces', serif; margin: 0 0 2px; }
    h2 { font: 700 14px 'Fraunces', serif; margin: 26px 0 8px; padding-bottom: 4px; border-bottom: 1.5px solid var(--linea); }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .muted { color: var(--suave); }
    .cabecera { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; border-bottom: 3px solid var(--verde); padding-bottom: 14px; }
    .folio { text-align: right; font-size: 11px; }
    .folio b { display: block; font: 600 15px 'IBM Plex Mono', monospace; color: var(--rojo); }
    dl { display: grid; grid-template-columns: 170px 1fr; gap: 3px 14px; margin: 0; }
    dt { color: var(--suave); }
    dd { margin: 0; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th { text-align: left; color: var(--suave); font-weight: 600; border-bottom: 1px solid var(--linea); padding: 4px 6px; }
    td { border-bottom: 1px solid #F1F5F3; padding: 4px 6px; vertical-align: top; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .kpi { border: 1px solid var(--linea); border-radius: 6px; padding: 8px 10px; }
    .kpi b { display: block; font: 700 20px 'Fraunces', serif; }
    .nivel { display: inline-block; padding: 2px 10px; border-radius: 999px; color: #FFFFFF; font-weight: 700; font-size: 12px; }
    .leyenda { margin-top: 28px; padding: 10px 12px; border: 1.5px solid #E9B7B0; background: #FCEEEC; border-radius: 6px; font-size: 11.5px; }
    .acciones { max-width: 820px; margin: 16px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
    .acciones button { font: 600 13px 'Manrope', sans-serif; padding: 8px 16px; border-radius: 8px; border: none; background: var(--verde); color: #FFFFFF; cursor: pointer; }
    @media print {
      body { background: #FFFFFF; }
      .acciones { display: none; }
      .hoja { margin: 0; padding: 0; max-width: none; }
      .marca { opacity: .08; }
      h2 { break-after: avoid; }
      tr { break-inside: avoid; }
    }
    @media (max-width: 640px) {
      .hoja { padding: 24px 16px; margin: 0; }
      .kpis { grid-template-columns: repeat(2, 1fr); }
      dl { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="acciones"><button type="button" onclick="window.print()">Imprimir o guardar como PDF</button></div>

  <div class="hoja">
    <div class="marca" aria-hidden="true">@for($i = 0; $i < 40; $i++)<span>{{ $marca }}</span>@endfor</div>

    <div class="contenido">
      <div class="cabecera">
        <div>
          <div class="muted mono" style="font-size: 11px; text-transform: uppercase; letter-spacing: .08em;">A Tu Lado · Resumen clínico confidencial</div>
          <h1>{{ $user->name }}</h1>
          <div class="muted">{{ $membresia->folio }}@if($membresia->numero_empleado) · Emp. {{ $membresia->numero_empleado }}@endif @if($area) · {{ $area }}@endif · {{ $institucion->nombre_corto }}</div>
        </div>
        <div class="folio">Folio <b>{{ $folio }}</b>{{ $generadoEn->format('d/m/Y H:i') }}</div>
      </div>

      <h2>Entrega</h2>
      <dl>
        <dt>Destinatario</dt><dd>{{ $destinatario }}</dd>
        <dt>Motivo</dt><dd>{{ $motivo }}</dd>
        <dt>Generado por</dt><dd>{{ $generadoPor->name }} (clínico acreditado)</dd>
      </dl>

      <h2>Estado clínico</h2>
      <dl>
        <dt>Semáforo vigente</dt>
        <dd><span class="nivel" style="background: {{ E::COLOR_NIVEL[$semaforo['nivel']] }};">{{ $semaforo['etiqueta'] }}</span></dd>
        <dt>Última clasificación</dt>
        <dd>{{ $clasificacion ? (E::ETIQUETA_NIVEL[$clasificacion->nivel] ?? $clasificacion->nivel) . ' · ' . strtoupper($clasificacion->origen) . ' · ' . $clasificacion->fecha->format('d/m/Y') : 'Sin clasificación' }}</dd>
        <dt>Caso de crisis</dt>
        <dd>
          @if($caso_abierto)
            {{ E::codigoCaso($caso_abierto) }} · disparado {{ $caso_abierto->disparado_en?->format('d/m/Y H:i') }}
            · {{ $caso_abierto->contactado_en ? 'contacto ' . $caso_abierto->contactado_en->format('d/m/Y H:i') : 'sin contacto humano aún' }}
          @else
            Sin caso abierto
          @endif
        </dd>
        <dt>Reglas disparadas (30 d)</dt>
        <dd>
          @forelse($reglas as $clave => $fecha)
            {{ E::REGLAS[$clave][0] }} {{ E::REGLAS[$clave][2] }} ({{ $fecha->format('d/m') }})@if(!$loop->last); @endif
          @empty
            Ninguna
          @endforelse
        </dd>
      </dl>

      <div class="kpis" style="margin-top: 12px;">
        <div class="kpi"><span class="muted">WHO-5</span><b>{{ $who5?->escala ?? '—' }}</b>{{ $who5 ? $who5->fecha->format('d/m/Y') : 'sin aplicación' }}</div>
        <div class="kpi"><span class="muted">MDI</span><b>{{ $mdi?->total ?? '—' }}</b>{{ $mdi ? 'ítem 6 en ' . $mdi->i6 : 'sin aplicación' }}</div>
        <div class="kpi"><span class="muted">ASQ</span><b style="font-size: 15px; padding: 3px 0;">{{ $asq_resumen['texto'] }}</b>{{ $asq?->fecha?->format('d/m/Y') }}</div>
        <div class="kpi"><span class="muted">Registros 30 d</span><b>{{ $registros_30 }}</b>{{ $adherencia }}% · racha {{ $racha }} d</div>
      </div>

      <h2>Registro diario · últimos 14</h2>
      <table>
        <thead><tr><th>Fecha</th><th>Estado</th><th>Valor</th><th>Texto libre</th><th>Regla</th></tr></thead>
        <tbody>
          @forelse($diario as ['log' => $log, 'regla' => $regla])
            <tr>
              <td class="mono">{{ $log->logged_date->format('d/m/Y') }}</td>
              <td>{{ E::ESTADO_DIARIO[(int) $log->valor] ?? '—' }}</td>
              <td class="mono">{{ (int) $log->valor }}</td>
              <td>{{ $log->journal_entry ? '«' . \Illuminate\Support\Str::limit($log->journal_entry, 180) . '»' : '—' }}{{ $log->bandera_lexica ? ' [bandera léxica]' : '' }}</td>
              <td>{{ $regla && isset(E::REGLAS[$regla]) ? E::REGLAS[$regla][0] : '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="muted">Sin registros.</td></tr>
          @endforelse
        </tbody>
      </table>

      @if($who5)
        <h2>WHO-5 · {{ $who5->fecha->format('d/m/Y') }} · crudo {{ $who5->crudo }}/25 · escala {{ $who5->escala }}/100</h2>
        <table><tbody>
          @foreach(E::WHO5_PREGUNTAS as $n => $pregunta)
            <tr><td>{{ $n }}. {{ $pregunta }}</td><td class="mono" style="width: 170px;">{{ $who5->{'i' . $n} }} — {{ E::WHO5_ESCALA[(int) $who5->{'i' . $n}] ?? '' }}</td></tr>
          @endforeach
        </tbody></table>
      @endif

      @if($mdi)
        <h2>MDI · {{ $mdi->fecha->format('d/m/Y') }} · total {{ $mdi->total }}/50</h2>
        <table><tbody>
          @foreach(E::MDI_PREGUNTAS as $clave => $pregunta)
            @php $valor = match ($clave) { 'i8' => max($mdi->i8a, $mdi->i8b), 'i10' => max($mdi->i10a, $mdi->i10b), default => $mdi->{$clave} }; @endphp
            <tr><td>{{ substr($clave, 1) }}. {{ $pregunta }}</td><td class="mono" style="width: 70px;">{{ $valor }}</td></tr>
          @endforeach
        </tbody></table>
      @endif

      @if($asq)
        <h2>ASQ · {{ $asq->fecha->format('d/m/Y') }} · {{ $asq_resumen['texto'] }}</h2>
        <table><tbody>
          @foreach(E::ASQ_PREGUNTAS as $clave => $pregunta)
            @continue($clave === 'p5' && $asq->p5 === null)
            <tr><td>{{ substr($clave, 1) }}. {{ $pregunta }}</td><td class="mono" style="width: 150px;">{{ $respuestaAsq($asq->{$clave}) }}</td></tr>
          @endforeach
        </tbody></table>
      @endif

      @if($puchol)
        <h2>Puchol · test breve del estado de ánimo · ciclo del {{ $puchol->fecha->format('d/m/Y') }}</h2>
        <table><tbody>
          @foreach(\App\Services\PucholService::SECCIONES as $seccion => [$items, $maximo])
            <tr>
              <td>{{ \App\Services\PucholService::NOMBRE_SECCION[$seccion] }}</td>
              <td class="mono" style="width: 80px;">{{ $puchol->{$seccion} === null ? '—' : $puchol->{$seccion} . '/' . $maximo }}</td>
              <td>{{ $puchol->{$seccion} === null ? 'Sin responder' : (\App\Services\PucholService::interpretar($seccion, $puchol->{$seccion}) ?? 'Sin clave de interpretación') }}</td>
            </tr>
          @endforeach
        </tbody></table>
      @endif

      <h2>Plan de seguridad y contactos</h2>
      <dl>
        <dt>Plan de seguridad</dt><dd>{{ $plan ? 'Registrado · actualizado ' . $plan->updated_at->format('d/m/Y') : 'No registrado' }}</dd>
        @forelse($contactos as $c)
          <dt>{{ $loop->first ? 'Contactos de emergencia' : '' }}</dt>
          <dd>{{ $c->nombre }}{{ $c->relacion ? ' · ' . $c->relacion : '' }} · <span class="mono">{{ $c->telefono }}</span></dd>
        @empty
          <dt>Contactos de emergencia</dt><dd>Sin registrar</dd>
        @endforelse
      </dl>

      <div class="leyenda">
        <b>Uso exclusivo del profesional destinatario.</b> Prohibido usar esta información para evaluación de desempeño, sanciones, despido o cualquier acto discriminatorio.
        La institución contratante no tiene acceso a este documento. La generación de este resumen quedó registrada en la bitácora clínica con el folio {{ $folio }}.
      </div>
    </div>
  </div>
</body>
</html>
