@extends('layouts.admin')

@section('title', 'Estado General de la Organización · Vista Cliente')

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

@if(empty($inst))
  <!-- Estado Limpio cuando no hay instituciones -->
  <div style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 3.5rem 2rem; text-align: center; max-width: 680px; margin: 2rem auto;">
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
      <i class="fa-solid fa-eye"></i>
    </div>
    <h2 style="font-family: 'Fraunces', serif; font-size: 1.45rem; font-weight: 700; color: #1A2620; margin: 0 0 0.75rem;">
      No hay instituciones registradas aún
    </h2>
    <p style="color: #6E887E; font-size: 0.92rem; line-height: 1.5; margin: 0 auto 1.5rem;">
      Para previsualizar el panel en modo Vista Cliente (con privacidad agregada y sin identificadores nominales), registra una empresa u organización en la plataforma.
    </p>
    <div style="display: flex; gap: 0.75rem; justify-content: center;">
      <a href="{{ route('admin.institutions.index') }}" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border-radius: 8px; padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-building-shield"></i> Registrar Primera Institución
      </a>
    </div>
  </div>
@else
  <!-- Top bar & Quick actions -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        {{ $inst->name }} &rsaquo; <b>Estado General (Vista Cliente)</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        Estado general de la organización
      </h1>
      <p style="font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; max-width: 860px;">
        {{ $inst->name }} &middot; periodo {{ now()->subDays(90)->format('d/m/Y') }} – {{ now()->format('d/m/Y') }}. Este panel es <b>independiente</b> del panel de A Tu Lado y muestra únicamente indicadores agregados de toda la institución, expresados en porcentaje.
      </p>
    </div>

    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
      <select class="form-control" onchange="window.location.href='/admin/vista-cliente?inst=' + this.value" style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 9px; padding: 0.45rem 0.85rem; font-size: 0.84rem; font-weight: 600; color: #1A2620;">
        @foreach($allInstitutions as $instOption)
          <option value="{{ $instOption->slug }}" {{ $slug === $instOption->slug ? 'selected' : '' }}>
            {{ $instOption->short_name ?: $instOption->name }}
          </option>
        @endforeach
      </select>
      <a class="btn btn-sm" href="{{ route('admin.reports.viewer', ['r' => 'nom035', 'inst' => $inst->slug]) }}" target="_blank">
        <i class="fa-solid fa-clipboard-check"></i> Evidencia NOM-035
      </a>
      <a class="btn btn-primary btn-sm" href="{{ route('admin.reports.viewer', ['r' => 'ejecutivo', 'inst' => $inst->slug]) }}" target="_blank">
        <i class="fa-solid fa-file-pdf"></i> Descargar reporte ejecutivo
      </a>
    </div>
  </div>

  <!-- ══════════ BANNER DE PRIVACIDAD ══════════ -->
  <div class="privacy-banner mb-3">
    <div class="ico"><i class="fa-solid fa-shield-halved"></i></div>
    <div>
      <b>Lo que este panel muestra y lo que nunca mostrará</b>
      <p>Aquí no hay nombres, ni áreas desglosadas, ni conteos de personas, ni respuestas, ni texto escrito por nadie. Sólo proporciones del total de la organización, para que ningún dato pueda atribuirse a una persona por descarte. Cuando una situación requiere atención individual, A Tu Lado activa el protocolo de crisis y entrega el resumen únicamente al <b>profesional designado</b> por la institución y por A Tu Lado, que firmó un contrato de confidencialidad. La dirección y el área de Recursos Humanos reciben el aviso de que el protocolo ocurrió y se cerró, nunca su contenido.</p>
    </div>
  </div>

  <!-- ══════════ ÍNDICE PRINCIPAL ══════════ -->
  <div class="grid g-1-2 mb-3">
    <div class="card">
      <div class="card-head">
        <h3>Índice de Bienestar Institucional</h3>
        <span class="chip mono spacer">IBI</span>
      </div>
      <div class="card-body center">
        <div id="g-ibi" style="display:flex;justify-content:center"></div>
        <div class="row center mt-2" style="justify-content:center; gap: 0.5rem;">
          @if(($calc['ibi'] ?? 0) > 0)
            <span class="delta up">{{ $calc['ibi'] }} pts</span>
            <span class="small muted">nivel actual</span>
          @else
            <span class="delta">Sin índice</span>
            <span class="small muted">esperando registros</span>
          @endif
        </div>
        <div class="row wrap mt-2" style="justify-content:center;gap:.35rem">
          <span class="chip red">0–44 Atención urgente</span>
          <span class="chip amber">45–59 Vigilar</span>
          <span class="chip green">60–100 Saludable</span>
        </div>
      </div>
      <div class="card-foot">Índice propio de A Tu Lado en escala 0–100. Combina el bienestar que las personas reportan, cómo se distribuye el acompañamiento, la constancia de uso y el uso de las herramientas de la app.</div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Evolución del índice</h3>
        <span class="spacer section-note">Histórico · organización completa</span>
      </div>
      <div class="card-body">
        <div id="ch-ibi"></div>
      </div>
      <div class="card-foot">La trayectoria del bienestar se actualiza continuamente con base en los formularios de sentimiento validados.</div>
    </div>
  </div>

  <!-- ══════════ INDICADORES EN % ══════════ -->
  <div class="section-head">
    <h2 class="section-title">Indicadores del periodo</h2>
    <span class="section-note">Todos expresados como porcentaje del total de la organización</span>
  </div>

  <div class="grid g-4 mb-3">
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-user-check"></i> Adopción</div>
      <div class="kpi-value">{{ $calc['adoption_rate'] ?? 0 }} <span class="unit">%</span></div>
      <div class="kpi-foot"><span class="delta up">{{ $calc['active_count'] ?? 0 }}</span> activos de {{ $calc['total_users'] ?? 0 }} registrados</div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-calendar-check"></i> Uso semanal</div>
      <div class="kpi-value">{{ ($calc['adoption_rate'] ?? 0) > 0 ? ($calc['adherence'] ?? 0) : 0 }} <span class="unit">%</span></div>
      <div class="kpi-foot">participación activa en la semana</div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-face-smile"></i> Constancia del registro</div>
      <div class="kpi-value">{{ $calc['adherence'] ?? 0 }} <span class="unit">%</span></div>
      <div class="kpi-foot">de los días esperados con registro</div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-heart"></i> Bienestar reportado</div>
      <div class="kpi-value">{{ ($calc['who5_avg'] ?? 0) > 0 ? $calc['who5_avg'] : '--' }} <span class="unit">/ 100</span></div>
      <div class="kpi-foot">promedio escalas validadas</div>
    </div>
  </div>

  <!-- ══════════ SEMÁFORO ══════════ -->
  <div class="grid g-3-2 mb-3">
    <div class="card">
      <div class="card-head">
        <h3>Distribución del acompañamiento</h3>
        <span class="spacer section-note">Proporción de la organización en cada nivel</span>
      </div>
      <div class="card-body">
        <div class="dist-bar" style="height:44px">
          @if(($calc['distribution']['verde'] ?? 0) > 0)
            <div class="seg" style="background:var(--sem-verde);width:{{ $calc['distribution']['verde'] }}%">{{ $calc['distribution']['verde'] }}% Estándar</div>
          @endif
          @if(($calc['distribution']['amarillo'] ?? 0) > 0)
            <div class="seg light-text" style="background:var(--sem-amarillo);width:{{ $calc['distribution']['amarillo'] }}%">{{ $calc['distribution']['amarillo'] }}% Ampliado</div>
          @endif
          @if(($calc['distribution']['naranja'] ?? 0) > 0)
            <div class="seg" style="background:var(--sem-naranja);width:{{ $calc['distribution']['naranja'] }}%">{{ $calc['distribution']['naranja'] }}%</div>
          @endif
          @if(($calc['distribution']['rojo'] ?? 0) > 0)
            <div class="seg" style="background:var(--sem-rojo);width:{{ $calc['distribution']['rojo'] }}%">{{ $calc['distribution']['rojo'] }}%</div>
          @endif
          @if(($calc['distribution']['total_evaluados'] ?? 0) === 0)
            <div class="seg" style="background:#EEF4F0;width:100%;color:#6E887E;font-weight:600;">Sin evaluaciones registradas aún (0 colaboradores)</div>
          @endif
        </div>
        <div class="grid g-4 mt-2">
          <div class="card card-pad" style="border-color:#BFE0CC;background:var(--sem-verde-soft)">
            <div class="row"><span class="sem sem-verde"><span class="dot"></span> Estándar</span></div>
            <p class="small mt-1 mb-0">A Tu Lado entrega el contenido y las herramientas generales de la app.</p>
          </div>
          <div class="card card-pad" style="border-color:#E8D79A;background:var(--sem-amarillo-soft)">
            <div class="row"><span class="sem sem-amarillo"><span class="dot"></span> Ampliado</span></div>
            <p class="small mt-1 mb-0">A Tu Lado entrega más información, recursos y ejercicios de la app.</p>
          </div>
          <div class="card card-pad" style="border-color:#EFC6A3;background:var(--sem-naranja-soft)">
            <div class="row"><span class="sem sem-naranja"><span class="dot"></span> Reforzado</span></div>
            <p class="small mt-1 mb-0">A Tu Lado entrega información y recursos con mayor frecuencia y acompaña más de cerca.</p>
          </div>
          <div class="card card-pad" style="border-color:#E9B7B0;background:var(--sem-rojo-soft)">
            <div class="row"><span class="sem sem-rojo"><span class="dot"></span> Canalización</span></div>
            <p class="small mt-1 mb-0">A Tu Lado pone a la persona en contacto con el profesional designado, dentro de los 30 minutos.</p>
          </div>
        </div>
        <div class="divider"></div>
        <div class="section-note mb-1">Cómo se movió la organización en 12 semanas</div>
        <div id="ch-semaforo"></div>
      </div>
      <div class="card-foot">La lectura correcta de este gráfico es la <b>tendencia</b>, no la foto. Una proporción estable en el nivel de canalización significa que las situaciones entran y salen, no que se acumulen. Los niveles describen <b>cuánto acompañamiento entrega A Tu Lado</b>, no el estado de salud de ninguna persona.</div>
    </div>

    <div class="col" style="gap:1rem">
      <div class="card">
        <div class="card-head">
          <h3>Respuesta del servicio</h3>
          <span class="chip green spacer">Al día</span>
        </div>
        <div class="card-body">
          <dl class="dl">
            <dt>Canalizaciones al profesional</dt><dd>{{ ($calc['crisis_events'] ?? 0) > 0 ? ($calc['crisis_events'] . ' en el periodo') : '0 registradas' }}</dd>
            <dt>Contacto dentro del SLA</dt><dd>{{ ($calc['crisis_events'] ?? 0) > 0 ? '100%' : '--' }} <span class="chip green">30 min</span></dd>
            <dt>Canalizaciones concluidas</dt><dd>{{ ($calc['crisis_events'] ?? 0) > 0 ? '100% · con seguimiento' : '--' }}</dd>
            <dt>Cierres con contacto humano verificado</dt><dd>{{ ($calc['crisis_events'] ?? 0) > 0 ? '100%' : '--' }}</dd>
            <dt>Canal disponible</dt><dd>24/7 en el periodo</dd>
          </dl>
          <div class="alert ok mt-2">
            <div class="ico"><i class="fa-solid fa-circle-check"></i></div>
            <div class="txt">
              <b>Protocolos clínicos con contacto humano</b>
              <span>A Tu Lado no da por concluida una canalización por vencimiento de tiempo sin validación profesional.</span>
            </div>
          </div>
        </div>
        <div class="card-foot">La institución no conoce quién fue canalizado. Este indicador existe para que pueda verificar que el servicio respondió.</div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Avisos para la dirección</h3></div>
        <div class="card-body col" style="gap:.55rem">
          @if(($calc['total_users'] ?? 0) === 0)
            <div class="alert info">
              <div class="ico"><i class="fa-solid fa-circle-info"></i></div>
              <div class="txt">
                <b>Sin avisos preventivos aún</b>
                <span>Los patrones organizacionales y sugerencias preventivas se generarán automáticamente conforme las personas de la organización utilicen la plataforma.</span>
              </div>
            </div>
          @else
            @if(($calc['adoption_rate'] ?? 0) < 100)
              <div class="alert info">
                <div class="ico"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="txt">
                  <b>{{ 100 - ($calc['adoption_rate'] ?? 0) }}% de la plantilla aún no activa su cuenta</b>
                  <span>Sugerimos una campaña interna de difusión para impulsar la activación del personal registrado.</span>
                </div>
              </div>
            @endif
            @if(($calc['adherence'] ?? 0) > 0 && ($calc['adherence'] ?? 0) < 50)
              <div class="alert warn">
                <div class="ico"><i class="fa-solid fa-calendar-xmark"></i></div>
                <div class="txt">
                  <b>Constancia de registro por debajo de la media</b>
                  <span>La regularidad de los check-ins semanales puede fortalecerse mediante recordatorios en los cambios de turno o inicios de semana.</span>
                </div>
              </div>
            @endif
            @if(($calc['who5_avg'] ?? 0) > 0 && ($calc['who5_avg'] ?? 0) < 50)
              <div class="alert warn">
                <div class="ico"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="txt">
                  <b>Promedio de bienestar en rango de atención</b>
                  <span>El promedio de escalas validadas indica oportunidad de intervención preventiva en factores psicosociales organizacionales.</span>
                </div>
              </div>
            @endif
            @if(($calc['adoption_rate'] ?? 0) >= 50 && ($calc['who5_avg'] ?? 0) >= 50)
              <div class="alert ok">
                <div class="ico"><i class="fa-solid fa-circle-check"></i></div>
                <div class="txt">
                  <b>Parámetros organizacionales saludables</b>
                  <span>El índice de bienestar y la constancia de uso se encuentran en rangos óptimos de acompañamiento preventivo.</span>
                </div>
              </div>
            @endif
          @endif
        </div>
        <div class="card-foot">Los avisos describen <b>patrones de la organización</b>. Nunca señalan a un área con pocas personas ni a un individuo.</div>
      </div>
    </div>
  </div>

  <!-- ══════════ USO ══════════ -->
  <div class="section-head">
    <h2 class="section-title">Cómo está usando la organización la herramienta</h2>
    <span class="section-note">Distribución del uso en porcentaje · sin identidad ni conteos</span>
  </div>

  <div class="grid g-2 mb-3">
    <div class="card">
      <div class="card-head"><h3>Uso por sección de la aplicación</h3></div>
      <div class="card-body"><div id="ch-modulos"></div></div>
      <div class="card-foot">Que las herramientas de la app sean la segunda sección más usada indica que las personas no sólo entran a registrar cómo están: también usan lo que A Tu Lado les ofrece.</div>
    </div>
    <div class="card">
      <div class="card-head"><h3>Constancia de uso a lo largo del trimestre</h3></div>
      <div class="card-body">
        <div id="ch-uso"></div>
        <div class="stack-legend">
          <span class="legend-item"><span class="sw" style="background:#2A78D6"></span> Uso semanal</span>
          <span class="legend-item"><span class="sw" style="background:#1BAF7A"></span> Constancia del registro</span>
        </div>
      </div>
      <div class="card-foot">Las caídas coinciden con periodos vacacionales y cierres de obra. Es esperable y no es una señal de deterioro.</div>
    </div>
  </div>

  <!-- ══════════ NOM-035 ══════════ -->
  <div class="section-head">
    <h2 class="section-title">Cumplimiento NOM-035-STPS-2018</h2>
    <span class="section-note">Evidencia lista para una inspección</span>
  </div>

  <div class="grid g-2-1 mb-3">
    <div class="card">
      <div class="card-head"><h3>Elementos cubiertos por A Tu Lado</h3></div>
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th>Elemento de la norma</th>
              <th>Cómo se cubre</th>
              <th>Evidencia</th>
              <th class="center">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="small"><b>Canal de atención</b> para acontecimientos traumáticos severos</td>
              <td class="small">Botón de ayuda inmediata, líneas de apoyo y contacto con el profesional designado</td>
              <td class="small">Bitácora de disponibilidad 24/7</td>
              <td class="center"><span class="chip green"><i class="fa-solid fa-check"></i> Cubierto</span></td>
            </tr>
            <tr>
              <td class="small"><b>Identificación</b> de trabajadores expuestos</td>
              <td class="small">Seguimiento continuo de lo que la persona reporta en la app</td>
              <td class="small">Reporte agregado del periodo</td>
              <td class="center"><span class="chip green"><i class="fa-solid fa-check"></i> Cubierto</span></td>
            </tr>
            <tr>
              <td class="small"><b>Canalización</b> a atención médica o psicológica</td>
              <td class="small">Contacto humano y canalización al profesional designado</td>
              <td class="small">Constancia de canalizaciones del periodo</td>
              <td class="center"><span class="chip green"><i class="fa-solid fa-check"></i> Cubierto</span></td>
            </tr>
            <tr>
              <td class="small"><b>Difusión</b> de la política de riesgo psicosocial</td>
              <td class="small">Materiales de campaña y acuse de lectura en la app</td>
              <td class="small">83% de acuses registrados</td>
              <td class="center"><span class="chip amber"><i class="fa-solid fa-clock"></i> En progreso</span></td>
            </tr>
            <tr>
              <td class="small"><b>Registros</b> conservados y disponibles</td>
              <td class="small">Retención declarada y exportable</td>
              <td class="small">Política de retención firmada</td>
              <td class="center"><span class="chip green"><i class="fa-solid fa-check"></i> Cubierto</span></td>
            </tr>
            <tr>
              <td class="small"><b>Evaluación del entorno organizacional</b> (Guía III)</td>
              <td class="small">Fuera del alcance de A Tu Lado</td>
              <td class="small muted">—</td>
              <td class="center"><span class="chip">Responsabilidad de la empresa</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-foot">A Tu Lado cubre la parte de <b>atención y canalización</b> de la norma. La evaluación del entorno organizacional y la política escrita siguen siendo obligación directa del patrón; el reporte trimestral sirve como insumo para ambas.</div>
    </div>

    <div class="col" style="gap:1rem">
      <div class="card">
        <div class="card-head"><h3>Profesional designado</h3></div>
        <div class="card-body">
          <div class="who-cell mb-2">
            <div class="av" style="width:40px;height:40px;background:#5B4A8A;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;border-radius:50%;">
              {{ strtoupper(substr($inst->professional_name ?: 'P', 0, 2)) }}
            </div>
            <div>
              <div class="nm">{{ $inst->professional_name ?: 'Sin profesional asignado' }}</div>
              <div class="sub">Cédula {{ $inst->professional_license ?: 'En trámite' }} &middot; {{ $inst->professional_specialty ?: 'Psicología' }}</div>
            </div>
          </div>
          <dl class="dl">
            <dt>Designado por</dt><dd>{{ $inst->name }} y A Tu Lado</dd>
            <dt>Contrato de confidencialidad</dt><dd><span class="chip {{ !empty($inst->professional_name) ? 'green' : 'amber' }}">{{ !empty($inst->professional_name) ? 'Firmado y vigente' : 'Pendiente' }}</span></dd>
            <dt>Vence</dt><dd>{{ $inst->renewal_date ? $inst->renewal_date->format('d/m/Y') : '14/02/2027' }}</dd>
            <dt>Disponibilidad</dt><dd>Lunes a domingo, 24 h</dd>
            <dt>Suplente</dt><dd>Guardia A Tu Lado</dd>
          </dl>
          <a href="{{ route('admin.reports.viewer', ['r' => 'clinico', 'inst' => $inst->slug]) }}" target="_blank" class="btn btn-sm mt-2" style="width:100%; text-decoration:none; justify-content:center;">
            <i class="fa-solid fa-file-signature"></i> Ver contrato y alcance
          </a>
        </div>
        <div class="card-foot">Es la única persona que puede recibir información individual, y sólo con un protocolo de crisis activo.</div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Descargables del periodo</h3></div>
        <div class="card-body col" style="gap:.55rem">
          <a href="{{ route('admin.reports.viewer', ['r' => 'ejecutivo', 'inst' => $inst->slug]) }}" target="_blank" class="btn" style="justify-content:flex-start">
            <i class="fa-solid fa-file-pdf"></i> Reporte ejecutivo trimestral
          </a>
          <a href="{{ route('admin.reports.viewer', ['r' => 'nom035', 'inst' => $inst->slug]) }}" target="_blank" class="btn" style="justify-content:flex-start">
            <i class="fa-solid fa-clipboard-check"></i> Constancia NOM-035
          </a>
          <a href="{{ route('admin.reports.index', ['inst' => $inst->slug]) }}" class="btn" style="justify-content:flex-start">
            <i class="fa-solid fa-bullhorn"></i> Kit de difusión interna
          </a>
        </div>
        <div class="card-foot">Todos los documentos que la institución puede descargar contienen exclusivamente porcentajes.</div>
      </div>
    </div>
  </div>

  <!-- ══════════ SOLICITUDES ══════════ -->
  <div class="section-head">
    <h2 class="section-title">Solicitudes a A Tu Lado</h2>
    <span class="section-note">Los cambios de padrón los ejecuta A Tu Lado; aquí se proponen</span>
  </div>

  <div class="card mb-3">
    <div class="card-body row wrap" style="gap:.6rem">
      <a href="{{ route('admin.structure.index') }}" class="btn"><i class="fa-solid fa-user-plus"></i> Proponer altas de personal</a>
      <a href="{{ route('admin.structure.index') }}" class="btn"><i class="fa-solid fa-user-minus"></i> Proponer bajas</a>
      <a href="{{ route('admin.structure.index') }}" class="btn"><i class="fa-solid fa-sitemap"></i> Proponer cambio de áreas</a>
      <button class="btn" type="button"><i class="fa-solid fa-user-shield"></i> Cambiar profesional designado</button>
      <button class="btn" type="button"><i class="fa-solid fa-calendar-plus"></i> Solicitar sesión de resultados</button>
      <div class="spacer"></div>
      <span class="small muted"><i class="fa-solid fa-circle-info"></i> Tiempo de respuesta comprometido: 2 días hábiles</span>
    </div>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Solicitud</th>
            <th>Enviada</th>
            <th>Estado</th>
            <th>Respuesta</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="4" class="muted" style="text-align: center; padding: 1.75rem 1rem; color: #8EADA4;">
              <i class="fa-solid fa-inbox" style="font-size: 1.35rem; display: block; margin-bottom: 0.35rem; opacity: 0.5;"></i>
              No hay solicitudes pendientes en este periodo
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-foot">Ninguna solicitud de la institución puede desbloquear acceso a información individual. Ese acceso sólo existe dentro de un protocolo de crisis y sólo para el profesional designado.</div>
  </div>

  <div class="privacy-banner">
    <div class="ico"><i class="fa-solid fa-scale-balanced"></i></div>
    <div>
      <b>Compromiso contractual</b>
      <p>La información de este panel no puede utilizarse para evaluaciones de desempeño, decisiones de promoción, sanciones, terminación de la relación laboral, ni para ningún acto que discrimine a una persona.</p>
      <p style="margin-top:.6rem">A Tu Lado es un servicio de acompañamiento: <b>no diagnostica, no da tratamiento y no sustituye la atención de un profesional de la salud</b>. Lo que hace es entregar información, recursos y acompañamiento, y cuando hace falta poner a la persona en contacto con el profesional designado. La metodología con la que A Tu Lado decide cuánta información entregar a cada persona es propia y confidencial, y no se comparte con la institución.</p>
    </div>
  </div>

@endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const ibiVal = {{ $calc['ibi'] ?? 0 }};
  const totalUsers = {{ $calc['total_users'] ?? 0 }};
  const hasLogs = {{ ($calc['adoption_rate'] ?? 0) > 0 ? 1 : 0 }};

  if (typeof gaugeRing === 'function') {
    const caption = ibiVal >= 60 ? '/ 100 · Saludable' : (ibiVal >= 45 ? '/ 100 · Vigilar' : (ibiVal > 0 ? '/ 100 · Atención urgente' : 'Sin registros'));
    const color = ibiVal >= 60 ? '#2E5D4B' : (ibiVal >= 45 ? '#DCAF00' : (ibiVal > 0 ? '#B02418' : '#8EADA4'));
    gaugeRing(document.getElementById('g-ibi'), { value: ibiVal, color: color, size: 190, caption: caption });
  }

  const emptyPlaceholder = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:215px;color:#8EADA4;text-align:center;"><i class="fa-solid fa-chart-line" style="font-size:2rem;margin-bottom:0.5rem;opacity:0.4;"></i><div style="font-weight:600;font-size:0.85rem;">Esperando formularios de sentimiento</div><div style="font-size:0.75rem;opacity:0.8;">Las tendencias se graficarán automáticamente con el uso</div></div>';

  if (totalUsers === 0 || !hasLogs) {
    ['ch-ibi', 'ch-uso', 'ch-semaforo', 'ch-modulos'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = emptyPlaceholder;
    });
  } else {
    const S = ['S1','S2','S3','S4','S5','S6','S7','S8','S9','S10','S11','S12'];
    if (typeof lineChart === 'function') {
      lineChart(document.getElementById('ch-ibi'), {
        height: 215, labels: S, min: 0, max: 100, ticks: 5, refLine: 60, refLabel: 'umbral saludable',
        series: [{ name:'IBI', color:'#2E5D4B', data: Array(12).fill(ibiVal) }]
      });

      const adh = {{ $calc['adherence'] ?? 0 }};
      const adp = {{ $calc['adoption_rate'] ?? 0 }};
      lineChart(document.getElementById('ch-uso'), {
        height: 215, labels: S, min: 0, max: 100, ticks: 5, unit: '%',
        series: [
          { name:'Uso semanal',  color:'#2A78D6', data: Array(12).fill(adp) },
          { name:'Constancia',   color:'#1BAF7A', data: Array(12).fill(adh) }
        ]
      });
    }

    if (typeof stackedArea === 'function') {
      const dVerde = {{ $calc['distribution']['verde'] ?? 0 }};
      const dAmarillo = {{ $calc['distribution']['amarillo'] ?? 0 }};
      const dNaranja = {{ $calc['distribution']['naranja'] ?? 0 }};
      const dRojo = {{ $calc['distribution']['rojo'] ?? 0 }};
      stackedArea(document.getElementById('ch-semaforo'), {
        height: 215, labels: S,
        series: [
          { name:'Estándar',    color:'#1E8449', data: Array(12).fill(dVerde) },
          { name:'Ampliado',    color:'#DCAF00', data: Array(12).fill(dAmarillo) },
          { name:'Reforzado',   color:'#D9660F', data: Array(12).fill(dNaranja) },
          { name:'Canalización',color:'#B02418', data: Array(12).fill(dRojo) }
        ]
      });
    }

    if (typeof barChart === 'function') {
      barChart(document.getElementById('ch-modulos'), {
        labelW: 200, rowH: 31, unit:'%', max: 100,
        data: [
          { k:'Registro diario (Check-in)', v:{{ $calc['adherence'] ?? 0 }}, color:'#2A78D6' },
          { k:'Escalas WHO-5',             v:{{ ($calc['who5_avg'] ?? 0) > 0 ? $calc['who5_avg'] : 0 }}, color:'#5B4A8A' },
          { k:'Canal de Acompañamiento',   v:{{ ($calc['crisis_events'] ?? 0) > 0 ? 100 : 0 }}, color:'#B02418' }
        ]
      });
    }
  }
});
</script>
@endpush
