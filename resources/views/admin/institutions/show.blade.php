@extends('layouts.admin')

@section('title', 'Semáforo y Detalle Institucional' . (!empty($inst) ? ' — ' . $inst['short'] : ''))

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
<style>
  .group-container {
    background: #FFFFFF;
    border: 1.5px solid #DCE8E0;
    border-radius: 14px;
    margin-bottom: 1.25rem;
    overflow: hidden;
    transition: all 0.2s ease;
  }
  .group-header {
    padding: 1rem 1.25rem;
    background: #F8FAF9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
    border-bottom: 1px solid #E5EFE9;
  }
  .group-header:hover {
    background: #EEF4F0;
  }
  .group-chevron {
    transition: transform 0.2s ease;
  }
  .group-container.collapsed .group-body {
    display: none;
  }
  .group-container.collapsed .group-chevron {
    transform: rotate(-90deg);
  }
  .seg {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    color: #FFFFFF;
    transition: width 0.3s ease;
  }
  .light-text {
    color: #1A2620 !important;
  }
  .bar-track {
    width: 100%;
    height: 16px;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    background: #E5EFE9;
  }
  .dist-bar {
    width: 100%;
    height: 24px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    background: #E5EFE9;
  }
</style>
@endpush

@section('content')
<div class="admin-content-canvas" style="padding: 1.75rem 2rem;">

@if(empty($inst))
  <!-- Estado Limpio cuando no hay instituciones en la base de datos -->
  <div style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 3.5rem 2rem; text-align: center; max-width: 680px; margin: 2rem auto;">
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
      <i class="fa-solid fa-traffic-light"></i>
    </div>
    <h2 style="font-family: 'Fraunces', serif; font-size: 1.45rem; font-weight: 700; color: #1A2620; margin: 0 0 0.75rem;">
      No hay instituciones registradas aún
    </h2>
    <p style="color: #6E887E; font-size: 0.92rem; line-height: 1.5; margin: 0 auto 1.5rem;">
      Para auditar los semáforos clínicos por cuadrilla, el desglose por macro-grupos y las alertas prioritarias, primero registra una organización en el catálogo institucional.
    </p>
    <div style="display: flex; gap: 0.75rem; justify-content: center;">
      <a href="{{ route('admin.institutions.index') }}" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border-radius: 8px; padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-building-shield"></i> Ir a Organizaciones / Alta Nueva
      </a>
    </div>
  </div>
@else
  <!-- Breadcrumbs & Quick Selector -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        Consola A Tu Lado &rsaquo; Instituciones &rsaquo; <b>{{ $inst['short'] }}</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        {{ $inst['name'] }}
      </h1>
      <p style="font-size: 0.86rem; color: #556860; margin: 0.25rem 0 0;">
        {{ $inst['giro'] }} · <b>{{ $inst['padron_activo'] }}</b> activos de <b>{{ $inst['padron_total'] }}</b> registrados
      </p>
    </div>

    <!-- Organization Switcher -->
    <div style="display: flex; gap: 0.65rem; align-items: center;">
      <select class="form-control" onchange="window.location.href='/admin/semaforo/' + this.value" style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 9px; padding: 0.5rem 0.85rem; font-size: 0.84rem; font-weight: 600; color: #1A2620;">
        @foreach($allInstitutions as $instOption)
          <option value="{{ $instOption->slug }}" {{ $slug === $instOption->slug ? 'selected' : '' }}>
            {{ $instOption->short_name ?: $instOption->name }}
          </option>
        @endforeach
      </select>

      <a href="{{ route('admin.reports.index') }}?inst={{ $slug }}" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border-radius: 9px; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; text-decoration: none;">
        <i class="fa-solid fa-file-pdf"></i>
        <span>Emitir Reporte</span>
      </a>
    </div>
  </div>

  <!-- Organization Overview Badges -->
  <div class="grid g-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-heart-pulse"></i> Índice de Bienestar (IBI)</div>
      <div class="kpi-value">
        @if($inst['ibi'] > 0)
          {{ $inst['ibi'] }} <span class="unit">/ 100</span>
        @else
          --
        @endif
      </div>
      <div class="kpi-foot">
        @if($inst['ibi'] > 0)
          <span class="delta {{ $inst['ibi'] >= 60 ? 'up' : 'down' }}">{{ $inst['ibi_delta'] }} pts</span> vs periodo anterior
        @else
          <span class="delta">Sin índice</span> esperando evaluaciones
        @endif
      </div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-users"></i> Padrón Activo</div>
      <div class="kpi-value">{{ $inst['padron_activo'] }} <span class="unit">personas</span></div>
      <div class="kpi-foot">
        @if($inst['padron_total'] > 0)
          <span class="delta up">{{ $inst['adoption_rate'] }}%</span> adopción en la empresa
        @else
          <span class="delta">0%</span> sin usuarios asignados
        @endif
      </div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-calendar-check"></i> Constancia de Uso</div>
      <div class="kpi-value">
        @if($inst['padron_activo'] > 0)
          {{ $inst['adherencia'] }} <span class="unit">%</span>
        @else
          --
        @endif
      </div>
      <div class="kpi-foot">
        @if($inst['padron_activo'] > 0)
          Registros emocionales diarios
        @else
          Sin registros emocionales aún
        @endif
      </div>
    </div>
    <div class="kpi accent-red">
      <div class="kpi-label"><i class="fa-solid fa-user-doctor"></i> Profesional Clínico</div>
      <div class="kpi-value" style="font-size: 1.1rem; font-weight: 700; margin-top: 4px;">{{ $inst['profesional'] ?: 'Sin asignar' }}</div>
      <div class="kpi-foot">Cédula: {{ $inst['cedula'] ?: 'En trámite' }} · Protocolo Activo</div>
    </div>
  </div>

  <!-- ══════════ REQUERIMIENTO 1: SEMÁFORO POR GRUPOS ══════════ -->
  <div style="display: grid; grid-template-columns: 2fr 1.1fr; gap: 1.5rem; margin-bottom: 2rem;" class="grid-main-detail">
    
    <!-- LEFT: Semáforo Agrupado -->
    <div>
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.5rem; margin-bottom: 1.25rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
          <div>
            <h3 style="font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: #1A2620; margin: 0;">
              Semáforo Clínico por Departamento
            </h3>
            <p style="font-size: 0.84rem; color: #6E887E; margin: 0.2rem 0 0;">
              Organizado por grupos operativos y macro-áreas funcionales.
            </p>
          </div>

          <!-- View Toggle: Grupos vs Plano -->
          <div style="display: flex; background: #EEF4F0; padding: 3px; border-radius: 8px; font-size: 0.78rem;">
            <button id="btnViewGroups" onclick="setSemaforoView('groups')" style="padding: 4px 10px; border-radius: 6px; border: none; background: #2E5D4B; color: #FFFFFF; font-weight: 600; cursor: pointer;">
              <i class="fa-solid fa-layer-group" style="margin-right: 4px;"></i> Por Grupos
            </button>
            <button id="btnViewFlat" onclick="setSemaforoView('flat')" style="padding: 4px 10px; border-radius: 6px; border: none; background: transparent; color: #556860; font-weight: 600; cursor: pointer;">
              <i class="fa-solid fa-list" style="margin-right: 4px;"></i> Lista Plana
            </button>
          </div>
        </div>

        <!-- Global Distribution Bar -->
        <div style="margin-bottom: 1.5rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-family: 'IBM Plex Mono', monospace; margin-bottom: 0.35rem; color: #6E887E;">
            <span>Distribución General Institucional</span>
            <span>Total: {{ $inst['padron_activo'] }} Colaboradores</span>
          </div>
          <div class="dist-bar">
            @if(($distribution['verde'] ?? 0) > 0)
              <div class="seg" style="background: #1E8449; width: {{ $distribution['verde'] }}%;">{{ $distribution['verde'] }}% Verde</div>
            @endif
            @if(($distribution['amarillo'] ?? 0) > 0)
              <div class="seg light-text" style="background: #DCAF00; width: {{ $distribution['amarillo'] }}%;">{{ $distribution['amarillo'] }}% Amarillo</div>
            @endif
            @if(($distribution['naranja'] ?? 0) > 0)
              <div class="seg" style="background: #D9660F; width: {{ $distribution['naranja'] }}%;">{{ $distribution['naranja'] }}% Naranja</div>
            @endif
            @if(($distribution['rojo'] ?? 0) > 0)
              <div class="seg" style="background: #B02418; width: {{ $distribution['rojo'] }}%;">{{ $distribution['rojo'] }}% Rojo</div>
            @endif
            @if(($distribution['total_evaluados'] ?? 0) === 0)
              <div class="seg" style="background: #EEF4F0; width: 100%; color: #6E887E; font-weight: 600;">Sin evaluaciones registradas aún (0 colaboradores)</div>
            @endif
          </div>
        </div>

        <!-- ── VISTA POR GRUPOS (Acordeón de Macro-Áreas) ── -->
        <div id="viewGroupsContainer">
          @foreach($groups as $index => $grp)
            <div class="group-container" id="group-card-{{ $grp['id'] }}">
              
              <!-- Group Accordion Header -->
              <div class="group-header" onclick="toggleGroup('{{ $grp['id'] }}')">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <i class="fa-solid fa-chevron-down group-chevron" id="chevron-{{ $grp['id'] }}" style="color: #6E887E; font-size: 0.85rem;"></i>
                  <div>
                    <span style="font-weight: 700; color: #1A2620; font-size: 0.95rem;">
                      {{ $grp['name'] }}
                    </span>
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; margin-left: 8px;">
                      ({{ $grp['active_count'] }} activos)
                    </span>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                  <!-- Aggregate Group Mini-Bar -->
                  <div style="width: 140px; display: flex; height: 12px; border-radius: 4px; overflow: hidden; background: #E5EFE9;" title="Distribución acumulada del grupo">
                    <div style="background: #1E8449; width: {{ $grp['distribution']['verde'] }}%;"></div>
                    <div style="background: #DCAF00; width: {{ $grp['distribution']['amarillo'] }}%;"></div>
                    <div style="background: #D9660F; width: {{ $grp['distribution']['naranja'] }}%;"></div>
                    <div style="background: #B02418; width: {{ $grp['distribution']['rojo'] }}%;"></div>
                  </div>

                  <!-- Status Badge -->
                  @if($grp['alerts_count'] > 0)
                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: #F8E2DF; color: #B02418;">
                      {{ $grp['alerts_count'] }} alertas activas
                    </span>
                  @else
                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: #E3F3E9; color: #1E8449;">
                      Estable
                    </span>
                  @endif
                </div>
              </div>

              <!-- Group Sub-Departments Table -->
              <div class="group-body" id="body-{{ $grp['id'] }}">
                <table class="data" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                  <thead>
                    <tr style="background: #FAFCFB; border-bottom: 1px solid #E5EFE9; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; color: #6E887E; text-transform: uppercase;">
                      <th style="padding: 0.65rem 1rem;">Sub-Departamento / Cuadrilla</th>
                      <th style="padding: 0.65rem 0.5rem; text-align: right;">Activos</th>
                      <th style="padding: 0.65rem 1rem; width: 170px;">Distribución</th>
                      <th style="padding: 0.65rem 0.5rem; text-align: right;">WHO-5</th>
                      <th style="padding: 0.65rem 0.5rem; text-align: right;">Adherencia</th>
                      <th style="padding: 0.65rem 1rem; text-align: center;">Alertas</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($grp['departments'] as $dept)
                      <tr style="border-bottom: 1px solid #EEF4F0; {{ $dept['alert_type'] === 'rojo' ? 'background: #FFF9F9;' : '' }}">
                        <td style="padding: 0.75rem 1rem;">
                          <div style="font-weight: 600; color: #1A2620;">{{ $dept['name'] }}</div>
                          <div style="font-size: 0.72rem; color: #7C908A;">{{ $dept['note'] }}</div>
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600;">
                          {{ $dept['active'] }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                          <div class="bar-track">
                            @if(($dept['dist']['verde'] ?? 0) > 0)
                              <div class="seg" style="background: #1E8449; width: {{ $dept['dist']['verde'] }}%;"></div>
                            @endif
                            @if(($dept['dist']['amarillo'] ?? 0) > 0)
                              <div class="seg" style="background: #DCAF00; width: {{ $dept['dist']['amarillo'] }}%;"></div>
                            @endif
                            @if(($dept['dist']['naranja'] ?? 0) > 0)
                              <div class="seg" style="background: #D9660F; width: {{ $dept['dist']['naranja'] }}%;"></div>
                            @endif
                            @if(($dept['dist']['rojo'] ?? 0) > 0)
                              <div class="seg" style="background: #B02418; width: {{ $dept['dist']['rojo'] }}%;"></div>
                            @endif
                            @if(($dept['active'] ?? 0) === 0)
                              <div class="seg" style="background: #EEF4F0; width: 100%; color: #8EADA4; font-size: 0.68rem; font-weight: 500;">Sin datos</div>
                            @endif
                          </div>
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: {{ ($dept['who5'] ?? 0) > 0 ? (($dept['who5'] < 50) ? '#B02418' : '#1E8449') : '#6E887E' }};">
                          {{ ($dept['who5'] ?? 0) > 0 ? $dept['who5'] : '--' }}
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; color: #556860;">
                          {{ ($dept['active'] ?? 0) > 0 ? $dept['adherence'] : '--' }}
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                          @if($dept['alerts'] > 0)
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: {{ $dept['alert_type'] === 'rojo' ? '#F8E2DF' : '#FBEADC' }}; color: {{ $dept['alert_type'] === 'rojo' ? '#B02418' : '#D9660F' }};">
                              <i class="fa-solid fa-triangle-exclamation"></i> {{ $dept['alerts'] }}
                            </span>
                          @else
                            <span style="color: #6E887E; font-size: 0.8rem;">0</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

            </div>
          @endforeach
        </div>

        <!-- ── VISTA PLANA (Todos juntos) ── -->
        <div id="viewFlatContainer" style="display: none;">
          <table class="data" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
              <tr style="background: #FAFCFB; border-bottom: 1.5px solid #DCE8E0; font-size: 0.74rem; font-family: 'IBM Plex Mono', monospace; color: #6E887E; text-transform: uppercase;">
                <th style="padding: 0.75rem 1rem;">Departamento</th>
                <th style="padding: 0.75rem 0.5rem; text-align: right;">Activos</th>
                <th style="padding: 0.75rem 1rem; width: 170px;">Distribución</th>
                <th style="padding: 0.75rem 0.5rem; text-align: right;">WHO-5</th>
                <th style="padding: 0.75rem 0.5rem; text-align: right;">Adherencia</th>
                <th style="padding: 0.75rem 1rem; text-align: center;">Alertas</th>
              </tr>
            </thead>
            <tbody>
              @foreach($groups as $grp)
                @foreach($grp['departments'] as $dept)
                  <tr style="border-bottom: 1px solid #EEF4F0; {{ $dept['alert_type'] === 'rojo' ? 'background: #FFF9F9;' : '' }}">
                    <td style="padding: 0.75rem 1rem;">
                      <div style="font-weight: 600; color: #1A2620;">{{ $dept['name'] }}</div>
                      <div style="font-size: 0.72rem; color: #6E887E; font-family: 'IBM Plex Mono', monospace;">Grupo: {{ $grp['name'] }}</div>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600;">
                      {{ $dept['active'] }}
                    </td>
                    <td style="padding: 0.75rem 1rem;">
                      <div class="bar-track">
                        @if(($dept['dist']['verde'] ?? 0) > 0)
                          <div class="seg" style="background: #1E8449; width: {{ $dept['dist']['verde'] }}%;"></div>
                        @endif
                        @if(($dept['dist']['amarillo'] ?? 0) > 0)
                          <div class="seg" style="background: #DCAF00; width: {{ $dept['dist']['amarillo'] }}%;"></div>
                        @endif
                        @if(($dept['dist']['naranja'] ?? 0) > 0)
                          <div class="seg" style="background: #D9660F; width: {{ $dept['dist']['naranja'] }}%;"></div>
                        @endif
                        @if(($dept['dist']['rojo'] ?? 0) > 0)
                          <div class="seg" style="background: #B02418; width: {{ $dept['dist']['rojo'] }}%;"></div>
                        @endif
                        @if(($dept['active'] ?? 0) === 0)
                          <div class="seg" style="background: #EEF4F0; width: 100%; color: #8EADA4; font-size: 0.68rem; font-weight: 500;">Sin datos</div>
                        @endif
                      </div>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: {{ ($dept['who5'] ?? 0) > 0 ? (($dept['who5'] < 50) ? '#B02418' : '#1E8449') : '#6E887E' }};">
                      {{ ($dept['who5'] ?? 0) > 0 ? $dept['who5'] : '--' }}
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; color: #556860;">
                      {{ ($dept['active'] ?? 0) > 0 ? $dept['adherence'] : '--' }}
                    </td>
                    <td style="padding: 0.75rem 1rem; text-align: center;">
                      @if($dept['alerts'] > 0)
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: {{ $dept['alert_type'] === 'rojo' ? '#F8E2DF' : '#FBEADC' }}; color: {{ $dept['alert_type'] === 'rojo' ? '#B02418' : '#D9660F' }};">
                          <i class="fa-solid fa-triangle-exclamation"></i> {{ $dept['alerts'] }}
                        </span>
                      @else
                        <span style="color: #6E887E; font-size: 0.8rem;">0</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
        </div>

        @php
          $highestAlertDept = null;
          $maxAlerts = 0;
          foreach($groups as $g) {
            foreach($g['departments'] as $d) {
              if ($d['alerts'] > $maxAlerts) {
                $maxAlerts = $d['alerts'];
                $highestAlertDept = $d;
              }
            }
          }
        @endphp

        @if($highestAlertDept && $maxAlerts > 0)
          <div style="background: #FFF9F9; padding: 0.85rem 1rem; border-radius: 8px; border: 1px solid #F8D7DA; font-size: 0.82rem; color: #721C24; margin-top: 1rem;">
            <i class="fa-solid fa-circle-exclamation" style="color: #B02418; margin-right: 6px;"></i>
            <b>Focalización de Riesgo:</b> El área <i>{{ $highestAlertDept['name'] }}</i> concentra <b>{{ $highestAlertDept['alerts'] }} alertas prioritarias</b> con {{ $highestAlertDept['active'] }} colaboradores evaluados. Recomendación clínica: revisar rotación y acompañamiento presencial con resguardo de identidad.
          </div>
        @else
          <div style="background: #F8FAF9; padding: 0.85rem 1rem; border-radius: 8px; border: 1px solid #E5EFE9; font-size: 0.82rem; color: #556860; margin-top: 1rem;">
            <i class="fa-solid fa-circle-check" style="color: #1E8449; margin-right: 6px;"></i>
            <b>Monitoreo Clínico Preventivo:</b> La institución se mantiene en parámetros estables. No se registran concentraciones críticas de riesgo agudo en los departamentos.
          </div>
        @endif

      </div>
    </div>

    <!-- RIGHT: Cola Clínica & Sedes -->
    <div>
      
      <!-- Cola de Atención Clínica -->
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0;">
            Cola de Atención Clínica
          </h3>
          <span style="background: {{ $crisisQueue->count() > 0 ? '#F8E2DF' : '#E3F3E9' }}; color: {{ $crisisQueue->count() > 0 ? '#B02418' : '#1E8449' }}; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 999px;">
            {{ $crisisQueue->count() }} {{ $crisisQueue->count() === 1 ? 'activo' : 'activos' }}
          </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          @forelse($crisisQueue as $event)
            <div style="padding: 0.85rem; border-radius: 10px; background: #FFF5F5; border-left: 3px solid #B02418;">
              <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-family: 'IBM Plex Mono', monospace; color: #B02418; font-weight: 700; margin-bottom: 3px;">
                <span>{{ $event->user->employee_number ?? ('COL-' . str_pad($event->user_id, 4, '0', STR_PAD_LEFT)) }} · Protocolo de Crisis</span>
                <span>{{ $event->disparado_en ? $event->disparado_en->diffForHumans() : 'Reciente' }}</span>
              </div>
              <div style="font-size: 0.84rem; color: #1A2620; line-height: 1.4;">
                {{ $event->notas_cierre ?: 'Activación de protocolo clínico. Contacto y seguimiento seudonimizado en proceso.' }}
              </div>
            </div>
          @empty
            <div style="padding: 1.25rem 0.85rem; border-radius: 10px; background: #F8FAF9; border: 1px dashed #DCE8E0; text-align: center; color: #6E887E; font-size: 0.82rem;">
              <i class="fa-solid fa-circle-check" style="color: #1E8449; font-size: 1.25rem; margin-bottom: 0.4rem; display: block;"></i>
              No hay alertas críticas pendientes de contacto en esta institución.
            </div>
          @endforelse
        </div>
      </div>

      <!-- Sedes Registradas -->
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
        <h4 style="font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700; color: #1A2620; margin: 0 0 0.75rem;">
          Sedes Físicas y Centros de Trabajo
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.86rem; color: #556860;">
          @foreach($inst['sedes'] as $sede)
            <li style="display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-location-dot" style="color: #2E5D4B; font-size: 0.8rem;"></i>
              <span>{{ $sede }}</span>
            </li>
          @endforeach
        </ul>
      </div>

    </div>

  </div>
@endif

</div>
@endsection

@push('scripts')
<script>
  function toggleGroup(groupId) {
    const card = document.getElementById('group-card-' + groupId);
    if (card) {
      card.classList.toggle('collapsed');
    }
  }

  function setSemaforoView(mode) {
    const groupsContainer = document.getElementById('viewGroupsContainer');
    const flatContainer = document.getElementById('viewFlatContainer');
    const btnGroups = document.getElementById('btnViewGroups');
    const btnFlat = document.getElementById('btnViewFlat');

    if (mode === 'groups') {
      groupsContainer.style.display = 'block';
      flatContainer.style.display = 'none';
      btnGroups.style.background = '#2E5D4B';
      btnGroups.style.color = '#FFFFFF';
      btnFlat.style.background = 'transparent';
      btnFlat.style.color = '#556860';
    } else {
      groupsContainer.style.display = 'none';
      flatContainer.style.display = 'block';
      btnFlat.style.background = '#2E5D4B';
      btnFlat.style.color = '#FFFFFF';
      btnGroups.style.background = 'transparent';
      btnGroups.style.color = '#556860';
    }
  }
</script>
@endpush
