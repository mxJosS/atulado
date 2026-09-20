@extends('layouts.admin')

@section('title', 'Analítica e Índices de Bienestar')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="admin-content-canvas" style="padding: 1.75rem 2rem;">

  <!-- Breadcrumbs & Header -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        Consola A Tu Lado &rsaquo; <b>Analítica e Índices</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        Analítica e Índices de Bienestar
      </h1>
      <p style="font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; max-width: 820px;">
        Evaluación multidimensional basada en evidencia: <b>¿la población está mejor?</b> (clínico), <b>¿se usa la plataforma?</b> (adopción) y <b>¿respondemos a tiempo?</b> (SLA de crisis).
      </p>
    </div>

    <div>
      <a href="{{ route('admin.reports.index') }}" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border-radius: 9px; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; text-decoration: none;">
        <i class="fa-solid fa-file-pdf"></i>
        <span>Generar Reporte Analítico</span>
      </a>
    </div>
  </div>

  <!-- ══════════ ÍNDICES CABECERA ══════════ -->
  <!-- ══════════ ÍNDICES CABECERA ══════════ -->
  <div class="grid g-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    
    <!-- 1. IBI -->
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Índice de Bienestar (IBI)</h3>
        <span class="chip mono" style="background: #E3F3E9; color: #1E8449; font-weight: 700; padding: 2px 7px; border-radius: 5px; font-size: 0.72rem;">IBI</span>
      </div>
      <div class="card-body row" style="display: flex; gap: 1.25rem; align-items: center;">
        <div id="g-ibi"></div>
        <div style="flex: 1;">
          @if(($moduleStats['total_users'] ?? 0) > 0 && ($globalIbi ?? 0) > 0)
            <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 0.5rem;">
              <span class="delta up" style="color: #1E8449; font-weight: 700; font-size: 0.82rem;">{{ $globalIbi }} pts</span>
              <span style="color: #6E887E; font-size: 0.78rem;">promedio global</span>
            </div>
            <p style="font-size: 0.78rem; color: #556860; line-height: 1.4; margin: 0;">
              Ponderación de estados emocionales, constancia de check-in y herramientas activas en la plataforma.
            </p>
          @else
            <div style="font-size: 0.82rem; color: #6E887E; padding: 0.5rem 0;">
              <i class="fa-solid fa-circle-info" style="color: #2E5D4B; margin-right: 4px;"></i>
              Esperando registros y check-ins de colaboradores para consolidar el índice IBI.
            </div>
          @endif
        </div>
      </div>
      <div class="card-foot" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #EEF4F0; font-size: 0.75rem; color: #6E887E;">
        <b>IBI:</b> Escala 0–100. Pondera estado emocional validado y adherencia al acompañamiento.
      </div>
    </div>

    <!-- 2. IRC -->
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Riesgo Concentrado (IRC)</h3>
        <span class="chip mono" style="background: #F8E2DF; color: #B02418; font-weight: 700; padding: 2px 7px; border-radius: 5px; font-size: 0.72rem;">IRC</span>
      </div>
      <div class="card-body row" style="display: flex; gap: 1.25rem; align-items: center;">
        <div id="g-irc"></div>
        <div style="flex: 1;">
          @if(($moduleStats['open_crisis'] ?? 0) > 0 || ($globalIrc ?? 0) > 0)
            <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 0.5rem;">
              <span class="delta down" style="color: #B02418; font-weight: 700; font-size: 0.82rem;">{{ $globalIrc }} pts</span>
              <span style="color: #6E887E; font-size: 0.78rem;">concentración focalizada</span>
            </div>
            <p style="font-size: 0.78rem; color: #556860; line-height: 1.4; margin: 0;">
              Mide si las alertas de riesgo están dispersas o agrupadas en cuadrillas específicas.
            </p>
          @else
            <div style="font-size: 0.82rem; color: #6E887E; padding: 0.5rem 0;">
              <i class="fa-solid fa-circle-check" style="color: #1E8449; margin-right: 4px;"></i>
              Sin concentración de riesgo agudo o alertas activas detectadas.
            </div>
          @endif
        </div>
      </div>
      <div class="card-foot" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #EEF4F0; font-size: 0.75rem; color: #6E887E;">
        <b>IRC:</b> Coeficiente de concentración que detecta si el malestar es departamental o generalizado.
      </div>
    </div>

    <!-- 3. IRO -->
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Respuesta Operativa (IRO)</h3>
        <span class="chip mono" style="background: #E3F3E9; color: #1E8449; font-weight: 700; padding: 2px 7px; border-radius: 5px; font-size: 0.72rem;">IRO</span>
      </div>
      <div class="card-body row" style="display: flex; gap: 1.25rem; align-items: center;">
        <div id="g-iro"></div>
        <div style="flex: 1;">
          @if(($moduleStats['crisis_calls'] ?? 0) > 0)
            <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 0.5rem;">
              <span class="delta up" style="color: #1E8449; font-weight: 700; font-size: 0.82rem;">{{ $globalIro }}%</span>
              <span style="color: #6E887E; font-size: 0.78rem;">SLA de respuesta</span>
            </div>
            <p style="font-size: 0.78rem; color: #556860; line-height: 1.4; margin: 0;">
              Porcentaje de activaciones de crisis con contacto clínico efectuado en tiempo normativo.
            </p>
          @else
            <div style="font-size: 0.82rem; color: #6E887E; padding: 0.5rem 0;">
              <i class="fa-solid fa-shield-halved" style="color: #2E5D4B; margin-right: 4px;"></i>
              Protocolo de crisis listo. No se han disparado alertas en el periodo actual.
            </div>
          @endif
        </div>
      </div>
      <div class="card-foot" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #EEF4F0; font-size: 0.75rem; color: #6E887E;">
        <b>IRO:</b> Eficacia y velocidad del protocolo de contención y acompañamiento humano.
      </div>
    </div>

  </div>

  <!-- ══════════ TENDENCIAS Y MIGRACIONES ══════════ -->
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="grid-2-col">
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Migración entre Niveles del Semáforo</h3>
      </div>
      <div class="card-body">
        @if(($moduleStats['total_users'] ?? 0) > 0 && ($moduleStats['mood_logs'] ?? 0) > 0)
          <div id="ch-semaforo" style="min-height: 200px;"></div>
        @else
          <div style="display: flex; height: 190px; align-items: center; justify-content: center; color: #8EADA4; font-size: 0.84rem; border: 1px dashed #DCE8E0; border-radius: 8px; text-align: center; padding: 1rem;">
            <div>
              <i class="fa-solid fa-chart-area" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block; color: #6E887E;"></i>
              Sin histórico acumulado<br>
              <span style="font-size: 0.76rem; color: #A0B5AC;">Se generará conforme los colaboradores envíen registros emocionales.</span>
            </div>
          </div>
        @endif
      </div>
      <div class="card-foot" style="margin-top: 0.75rem; font-size: 0.76rem; color: #6E887E;">
        Evolución de bandas clínicas a lo largo de las semanas de acompañamiento.
      </div>
    </div>

    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">WHO-5 Institucional vs. Corte de Riesgo</h3>
      </div>
      <div class="card-body">
        @if(($moduleStats['total_users'] ?? 0) > 0 && ($moduleStats['who5_tests'] ?? 0) > 0)
          <div id="ch-who5-inst" style="min-height: 200px;"></div>
        @else
          <div style="display: flex; height: 190px; align-items: center; justify-content: center; color: #8EADA4; font-size: 0.84rem; border: 1px dashed #DCE8E0; border-radius: 8px; text-align: center; padding: 1rem;">
            <div>
              <i class="fa-solid fa-chart-line" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block; color: #6E887E;"></i>
              Sin evaluaciones de bienestar WHO-5 registradas<br>
              <span style="font-size: 0.76rem; color: #A0B5AC;">El corte clínico se trazará automáticamente al contestarse los cuestionarios.</span>
            </div>
          </div>
        @endif
      </div>
      <div class="card-foot" style="margin-top: 0.75rem; font-size: 0.76rem; color: #6E887E;">
        Seguimiento del índice OMS (corte en 50 puntos de tamizaje preventivo).
      </div>
    </div>
  </div>

  <!-- ══════════ EMBUDO CLÍNICO Y USO DE MÓDULOS ══════════ -->
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;" class="grid-2-col">
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Embudo Clínico de Acompañamiento</h3>
      </div>
      <div class="card-body">
        <div id="ch-embudo"></div>
      </div>
    </div>

    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
      <div class="card-head" style="margin-bottom: 1rem;">
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; margin: 0;">Uso de Módulos y Herramientas Terapéuticas</h3>
      </div>
      <div class="card-body">
        <div id="ch-modulos"></div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ time() }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof dial === 'function') {
    dial(document.getElementById('g-ibi'), {{ $globalIbi ?? 0 }}, { color: '{{ ($globalIbi ?? 0) >= 60 ? "#1E8449" : (($globalIbi ?? 0) > 0 ? "#D9660F" : "#6E887E") }}', max: 100, label: 'pts' });
    dial(document.getElementById('g-irc'), {{ $globalIrc ?? 0 }}, { color: '{{ ($globalIrc ?? 0) > 60 ? "#B02418" : "#1E8449" }}', max: 100, label: 'Gini' });
    dial(document.getElementById('g-iro'), {{ $globalIro ?? 0 }}, { color: '{{ ($globalIro ?? 0) > 0 ? "#1E8449" : "#6E887E" }}', max: 100, label: '%' });
  }

  @if(($moduleStats['total_users'] ?? 0) > 0 && ($moduleStats['mood_logs'] ?? 0) > 0)
  if (typeof stackArea === 'function' && document.getElementById('ch-semaforo')) {
    stackArea(document.getElementById('ch-semaforo'), {
      height: 190,
      series: [
        { label: 'Rojo', color: '#B02418', data: [0, 0, 0, 0] },
        { label: 'Naranja', color: '#D9660F', data: [0, 0, 0, 0] },
        { label: 'Amarillo', color: '#DCAF00', data: [0, 0, 0, 0] },
        { label: 'Verde', color: '#1E8449', data: [100, 100, 100, 100] }
      ]
    });
  }
  @endif

  @if(($moduleStats['total_users'] ?? 0) > 0 && ($moduleStats['who5_tests'] ?? 0) > 0)
  if (typeof lineChart === 'function' && document.getElementById('ch-who5-inst')) {
    lineChart(document.getElementById('ch-who5-inst'), {
      height: 190,
      min: 30, max: 80,
      threshold: 50,
      lines: [
        { color: '#2A78D6', data: [{{ $globalIbi ?? 50 }}] }
      ]
    });
  }
  @endif

  if (typeof barChart === 'function') {
    barChart(document.getElementById('ch-embudo'), {
      labelW: 190, unit: '', max: {{ max(10, ($moduleStats['total_users'] ?? 0)) }}, rowH: 30,
      data: [
        { k: 'Padrón total registrado', v: {{ $moduleStats['total_users'] ?? 0 }}, color: '#2E5D4B' },
        { k: 'Usuarios activos (30d)', v: {{ $moduleStats['active_users'] ?? 0 }}, color: '#3D7A5F' },
        { k: 'Formularios de ánimo', v: {{ $moduleStats['mood_logs'] ?? 0 }}, color: '#2A78D6' },
        { k: 'Cuestionarios WHO-5', v: {{ $moduleStats['who5_tests'] ?? 0 }}, color: '#1BAF7A' },
        { k: 'Protocolos de crisis', v: {{ $moduleStats['crisis_calls'] ?? 0 }}, color: '#D9660F' },
        { k: 'Alertas activas', v: {{ $moduleStats['open_crisis'] ?? 0 }}, color: '#B02418' }
      ]
    });

    barChart(document.getElementById('ch-modulos'), {
      labelW: 190, unit: '', max: {{ max(10, ($moduleStats['mood_logs'] ?? 0) + 5) }}, rowH: 30,
      data: [
        { k: 'Registro diario de ánimo', v: {{ $moduleStats['mood_logs'] ?? 0 }}, color: '#2A78D6' },
        { k: 'Cuestionarios clínicos WHO-5', v: {{ $moduleStats['who5_tests'] ?? 0 }}, color: '#1BAF7A' },
        { k: 'Protocolos de crisis atendidos', v: {{ $moduleStats['crisis_calls'] ?? 0 }}, color: '#B02418' },
        { k: 'Padrón activo evaluado', v: {{ $moduleStats['active_users'] ?? 0 }}, color: '#5B4A8A' }
      ]
    });
  }
});
</script>
@endpush
