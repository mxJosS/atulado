@extends('layouts.admin')

@section('title', 'Instituciones y Operación B2B')

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
  .form-group-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: #1A2620;
    margin-bottom: 0.35rem;
  }
  .form-input-styled {
    width: 100%;
    padding: 0.55rem 0.8rem;
    border: 1.5px solid #DCE8E0;
    border-radius: 8px;
    font-size: 0.86rem;
    color: #1A2620;
    background: #FFFFFF;
    box-sizing: border-box;
    transition: border-color 0.2s;
  }
  .form-input-styled:focus {
    border-color: #2E5D4B;
    outline: none;
    box-shadow: 0 0 0 3px rgba(46, 93, 75, 0.12);
  }
  .form-hint-styled {
    display: block;
    font-size: 0.73rem;
    color: #6E887E;
    margin-top: 0.3rem;
    line-height: 1.35;
  }
</style>
@endpush

@section('content')
<div class="admin-content-canvas">

  <!-- Breadcrumbs & Quick Bar -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        Consola A Tu Lado &rsaquo; <b>Instituciones</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        Estado de la plataforma
      </h1>
      <p style="font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; max-width: 800px;">
        Vista de operación general de A Tu Lado sobre todas las organizaciones y empresas contratantes.
      </p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <button type="button" class="btn btn-primary" onclick="openModal('m-alta-institucion')" style="background: #2E5D4B; color: #FFFFFF; border-radius: 9px; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; cursor: pointer; border: none; font-weight: 600;">
        <i class="fa-solid fa-plus"></i>
        <span>Nueva Institución</span>
      </button>
      <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary" style="border-radius: 9px; padding: 0.55rem 1rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-file-pdf" style="color: #2E5D4B;"></i>
        <span>Centro de Reportes</span>
      </a>
      <a href="{{ route('admin.institutions.show') }}" class="btn" style="background: #EEF4F0; color: #2E5D4B; border-radius: 9px; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
        <i class="fa-solid fa-traffic-light"></i>
        <span>Ver Semáforos Clínicos</span>
      </a>
    </div>
  </div>

  <!-- ══════════ KPI DE PLATAFORMA ══════════ -->
  <div class="section-head" style="margin: 1.5rem 0 0.85rem; display: flex; justify-content: space-between; align-items: center;">
    <h2 class="section-title" style="font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 600; margin: 0; color: #1A2620;">
      Pulso Operativo Global
    </h2>
    <span class="section-note" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.74rem; color: #6E887E;">
      Últimos 7 días · comparado con el periodo anterior
    </span>
  </div>

  <div class="grid g-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-building-shield"></i> Instituciones activas</div>
      <div class="kpi-value">{{ $platformMetrics['total_institutions'] ?? count($institutions) }}</div>
      <div class="kpi-foot">
        @if(($platformMetrics['total_institutions'] ?? count($institutions)) > 0)
          <span class="delta up">+{{ $platformMetrics['total_institutions'] ?? count($institutions) }}</span> activas en B2B
        @else
          <span class="delta">0 activas</span> en la plataforma
        @endif
      </div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-users"></i> Cuentas activadas</div>
      <div class="kpi-value">
        {{ number_format($platformMetrics['total_active'] ?? 0) }}
        <span class="unit">/ {{ number_format($platformMetrics['total_registered'] ?? 0) }}</span>
      </div>
      <div class="kpi-foot">
        @if(($platformMetrics['total_registered'] ?? 0) > 0)
          <span class="delta up">{{ $platformMetrics['adoption_rate'] ?? 0 }}%</span> de adopción global
        @else
          <span class="delta">0%</span> sin cuentas registradas
        @endif
      </div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-wave-square"></i> Arraigo (DAU/MAU)</div>
      <div class="kpi-value">
        @if(($platformMetrics['total_active'] ?? 0) > 0)
          {{ $platformMetrics['arraigo'] ?? 0 }}
        @else
          --
        @endif
      </div>
      <div class="kpi-foot">
        @if(($platformMetrics['total_active'] ?? 0) > 0)
          <span class="delta up">Ratio activo</span> participación sostenida
        @else
          <span class="delta">Sin actividad</span> esperando registros
        @endif
      </div>
    </div>

    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-calendar-check"></i> Adherencia Diaria</div>
      <div class="kpi-value">
        @if(($platformMetrics['total_active'] ?? 0) > 0)
          {{ $platformMetrics['adherence_rate'] ?? 0 }} <span class="unit">%</span>
        @else
          --
        @endif
      </div>
      <div class="kpi-foot">
        @if(($platformMetrics['total_active'] ?? 0) > 0)
          <span class="delta up">En base a check-ins</span> últimos 14 días
        @else
          <span class="delta">Sin check-ins</span> en periodo actual
        @endif
      </div>
    </div>
  </div>

  <!-- ══════════ LISTADO DE INSTITUCIONES ══════════ -->
  <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.5rem; margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: #1A2620; margin: 0;">
          Organizaciones e Instituciones Activas
        </h3>
        <p style="font-size: 0.84rem; color: #6E887E; margin: 0.2rem 0 0;">
          Selecciona una organización para auditar su desglose departamental, semáforo clínico o emitir sus reportes oficiales.
        </p>
      </div>
      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="chip" style="background: #EEF4F0; color: #2E5D4B; font-weight: 600; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem;">
          {{ count($institutions) }} organizaciones registradas
        </span>
        <button type="button" class="btn btn-sm btn-primary" onclick="openModal('m-alta-institucion')" style="background: #2E5D4B; color: #FFFFFF; border-radius: 7px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600;">
          <i class="fa-solid fa-plus"></i> Nueva
        </button>
      </div>
    </div>

    <div class="table-wrap" style="overflow-x: auto;">
      <table class="data" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="border-bottom: 1.5px solid #DCE8E0; font-family: 'IBM Plex Mono', monospace; font-size: 0.74rem; color: #6E887E; text-transform: uppercase;">
            <th style="padding: 0.75rem 1rem;">Organización</th>
            <th style="padding: 0.75rem 1rem;">Giro / Sector</th>
            <th style="padding: 0.75rem 1rem;">Contacto Enlace</th>
            <th style="padding: 0.75rem 1rem;" class="num">Cuentas</th>
            <th style="padding: 0.75rem 1rem;" class="num">Adopción</th>
            <th style="padding: 0.75rem 1rem;" class="center">Semáforo</th>
            <th style="padding: 0.75rem 1rem;" class="actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($institutions as $inst)
            <tr style="border-bottom: 1px solid #EEF4F0; font-size: 0.9rem; transition: background 0.15s;" onmouseover="this.style.background='#F8FAF9'" onmouseout="this.style.background='transparent'">
              <td style="padding: 1rem;">
                <div style="font-weight: 700; color: #1A2620;">{{ $inst['name'] }}</div>
                <div style="font-size: 0.76rem; color: #6E887E; font-family: 'IBM Plex Mono', monospace;">{{ $inst['plan'] }} &middot; {{ $inst['city'] ?? 'Mérida, Yuc.' }}</div>
              </td>
              <td style="padding: 1rem; color: #556860; font-size: 0.85rem;">
                {{ $inst['category'] }}
              </td>
              <td style="padding: 1rem;">
                <div style="font-weight: 500; color: #1A2620;">{{ $inst['contact'] }}</div>
                <div style="font-size: 0.76rem; color: #6E887E;">{{ $inst['email'] }}</div>
              </td>
              <td style="padding: 1rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600;">
                {{ $inst['active_count'] }} <span style="font-size: 0.76rem; color: #8EADA4;">/ {{ $inst['users_count'] }}</span>
              </td>
              <td style="padding: 1rem; text-align: right;">
                <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: #2E5D4B;">{{ $inst['adoption_rate'] }}%</span>
              </td>
              <td style="padding: 1rem; text-align: center;">
                @if($inst['alert_level'] === 'rojo')
                  <span class="sem sem-rojo" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; background: #F8E2DF; color: #B02418; font-size: 0.76rem; font-weight: 700;">
                    <span class="dot" style="width: 7px; height: 7px; border-radius: 50%; background: #B02418;"></span> Alerta ({{ $inst['critical_count'] }})
                  </span>
                @elseif($inst['alert_level'] === 'naranja')
                  <span class="sem sem-naranja" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; background: #FBEADC; color: #D9660F; font-size: 0.76rem; font-weight: 700;">
                    <span class="dot" style="width: 7px; height: 7px; border-radius: 50%; background: #D9660F;"></span> En observación
                  </span>
                @elseif($inst['alert_level'] === 'amarillo')
                  <span class="sem sem-amarillo" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; background: #FBF2D4; color: #8A6D00; font-size: 0.76rem; font-weight: 700;">
                    <span class="dot" style="width: 7px; height: 7px; border-radius: 50%; background: #DCAF00;"></span> Preventivo
                  </span>
                @else
                  <span class="sem sem-verde" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; background: #E3F3E9; color: #1E8449; font-size: 0.76rem; font-weight: 700;">
                    <span class="dot" style="width: 7px; height: 7px; border-radius: 50%; background: #1E8449;"></span> Óptimo
                  </span>
                @endif
              </td>
              <td style="padding: 1rem; text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                  <a href="{{ route('admin.institutions.show', $inst['id']) }}" class="btn btn-sm" style="padding: 5px 10px; font-size: 0.8rem; background: #EEF4F0; color: #2E5D4B; border-radius: 6px; text-decoration: none; font-weight: 600;" title="Ver semáforo y detalle">
                    <i class="fa-solid fa-chart-pie"></i> Detalle
                  </a>
                  <a href="{{ route('admin.reports.index') }}?inst={{ $inst['id'] }}" class="btn btn-sm" style="padding: 5px 10px; font-size: 0.8rem; background: #FFFFFF; border: 1px solid #DCE8E0; color: #556860; border-radius: 6px; text-decoration: none;" title="Emitir reporte">
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align: center; padding: 3rem 1.5rem;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                  <i class="fa-solid fa-building-circle-check"></i>
                </div>
                <h4 style="font-family: 'Fraunces', serif; font-size: 1.15rem; color: #1A2620; margin: 0 0 0.5rem;">No hay instituciones registradas aún</h4>
                <p style="color: #6E887E; font-size: 0.88rem; max-width: 480px; margin: 0 auto 1.25rem;">
                  Comienza dando de alta la primera empresa u organización para monitorear su semáforo de riesgo, macro-grupos y generar reportes clínicos en vivo conforme sus colaboradores utilicen la plataforma.
                </p>
                <button type="button" class="btn btn-primary" onclick="openModal('m-alta-institucion')" style="background: #2E5D4B; color: #FFFFFF; border: none; border-radius: 8px; padding: 8px 18px; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                  <i class="fa-solid fa-plus"></i> Registrar Primera Institución
                </button>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ══════════════ MODAL DE ALTA DE INSTITUCIÓN ══════════════ -->
<div class="modal-backdrop" id="m-alta-institucion">
  <div class="modal" style="max-width: 680px; width: 100%;">
    <div class="modal-head" style="background: #2E5D4B; color: #FFFFFF; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: flex-start;">
      <div>
        <h2 style="font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; margin: 0; color: #FFFFFF;">
          Nueva Institución / Organización
        </h2>
        <div class="sub" style="color: #A8E6C0; font-size: 0.8rem; margin-top: 3px;">
          Alta oficial de empresa contratante, enlace de RRHH y profesional clínico designado.
        </div>
      </div>
      <button class="x" type="button" onclick="closeModal('m-alta-institucion')" style="background: transparent; border: none; color: #FFFFFF; font-size: 1.2rem; cursor: pointer;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.institutions.store') }}">
      @csrf
      <div class="modal-body" style="padding: 1.5rem; max-height: 72vh; overflow-y: auto;">
        
        <!-- Bloque 1: Datos de la Empresa -->
        <div style="margin-bottom: 1.35rem; padding-bottom: 1.15rem; border-bottom: 1px solid #EEF4F0;">
          <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.75rem;">
            1. Datos de la Organización (Empresa o Colegio)
          </div>

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 0.85rem;">
            <div>
              <label class="form-group-label">Razón Social o Nombre Oficial *</label>
              <input type="text" name="name" class="form-input-styled" required placeholder="Ej. Organización Ejemplo S.A. de C.V.">
            </div>
            <div>
              <label class="form-group-label">Nombre Corto *</label>
              <input type="text" name="short_name" class="form-input-styled" placeholder="Ej. Organización Ejemplo">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-group-label">Sector / Giro *</label>
              <select name="category" class="form-input-styled" required>
                <option value="Construcción y Obras">Construcción y Obras</option>
                <option value="Automotriz y Retail">Automotriz y Retail</option>
                <option value="Tecnología y BPO">Tecnología y BPO</option>
                <option value="Educación y Colegios">Educación y Colegios</option>
                <option value="Manufactura e Industria">Manufactura e Industria</option>
                <option value="Servicios Corporativos">Servicios Corporativos</option>
                <option value="Salud y Hospitales">Salud y Hospitales</option>
                <option value="Hotelería y Turismo">Hotelería y Turismo</option>
              </select>
            </div>
            <div>
              <label class="form-group-label">RFC (Opcional)</label>
              <input type="text" name="rfc" class="form-input-styled" placeholder="CMA210415H21">
            </div>
            <div>
              <label class="form-group-label">Ciudad / Ubicación</label>
              <input type="text" name="city" class="form-input-styled" value="Mérida, Yucatán">
            </div>
          </div>
        </div>

        <!-- Bloque 2: Contacto Administrativo / RRHH -->
        <div style="margin-bottom: 1.35rem; padding-bottom: 1.15rem; border-bottom: 1px solid #EEF4F0;">
          <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.75rem;">
            2. Contacto Administrativo / Recursos Humanos
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.85rem;">
            <div>
              <label class="form-group-label">Nombre del Contacto *</label>
              <input type="text" name="contact_name" class="form-input-styled" required placeholder="Ej. Lic. Roberto Pech">
            </div>
            <div>
              <label class="form-group-label">Cargo o Puesto</label>
              <input type="text" name="contact_position" class="form-input-styled" value="Gerente de Recursos Humanos">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-group-label">Correo Electrónico *</label>
              <input type="email" name="contact_email" class="form-input-styled" required placeholder="rpech@empresa.com">
            </div>
            <div>
              <label class="form-group-label">Teléfono de Enlace</label>
              <input type="text" name="contact_phone" class="form-input-styled" placeholder="Ej. 999 412 8830">
            </div>
          </div>
        </div>

        <!-- Bloque 3: Profesional Clínico Designado -->
        <div style="margin-bottom: 1.35rem; padding-bottom: 1.15rem; border-bottom: 1px solid #EEF4F0;">
          <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.75rem;">
            3. Profesional Clínico Designado (NOM-035 y Crisis)
          </div>

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
            <div>
              <label class="form-group-label">Nombre del Psicólogo / Especialista *</label>
              <input type="text" name="professional_name" class="form-input-styled" required value="Psic. Rodrigo Ancona Rosado" placeholder="Nombre completo">
            </div>
            <div>
              <label class="form-group-label">Cédula Profesional</label>
              <input type="text" name="professional_license" class="form-input-styled" value="9412034" placeholder="Ej. 7712045">
            </div>
          </div>

          <div style="background: #F8FAF9; border: 1px solid #DCE8E0; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; gap: 8px; align-items: flex-start;">
            <i class="fa-solid fa-user-shield" style="color: #2E5D4B; margin-top: 2px;"></i>
            <span style="font-size: 0.75rem; color: #556860; line-height: 1.35;">
              Este profesional es el único facultado legalmente para recibir el expediente clínico nominativo en situaciones de crisis activa, protegiendo la confidencialidad ante la dirección.
            </span>
          </div>
        </div>

        <!-- Bloque 4: Contrato y Macro-Áreas de Operación -->
        <div>
          <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.75rem;">
            4. Estructura de Macro-Grupos y Contrato
          </div>

          <div style="margin-bottom: 0.85rem;">
            <label class="form-group-label">Macro-Áreas Operativas (Separadas por comas) *</label>
            <input type="text" name="macro_groups" class="form-input-styled" value="Operaciones y Frente de Obra, Corporativo y Dirección, Servicios Generales" placeholder="Ej. Operaciones, Corporativo, Logística">
            <span class="form-hint-styled">
              Se crearán automáticamente las macro-áreas del <b>Semáforo por Grupos</b>. Dentro de cada una se podrán agregar cuadrillas o salones específicos.
            </span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div>
              <label class="form-group-label">Plan Institucional *</label>
              <select name="plan" class="form-input-styled" required>
                <option value="Institucional Anual">Institucional Anual</option>
                <option value="Institucional Premium">Institucional Premium</option>
                <option value="Pyme Salud">Pyme Salud</option>
                <option value="Comunidad Educativa">Comunidad Educativa</option>
              </select>
            </div>
            <div>
              <label class="form-group-label">Padrón Estimado</label>
              <input type="number" name="users_count" class="form-input-styled" min="0" value="100" placeholder="Número de personas">
            </div>
            <div>
              <label class="form-group-label">Fecha de Renovación</label>
              <input type="date" name="renewal_date" class="form-input-styled" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
            </div>
          </div>
        </div>

      </div>

      <div class="modal-foot" style="padding: 1rem 1.5rem; background: #F8FAF9; border-top: 1px solid #EEF4F0; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.76rem; color: #6E887E;">
          <i class="fa-solid fa-shield-halved"></i> Privacidad y NOM-035 garantizada
        </span>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn" onclick="closeModal('m-alta-institucion')" style="background: #FFFFFF; border: 1px solid #DCE8E0; color: #556860; border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.84rem;">
            Cancelar
          </button>
          <button type="submit" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border: none; border-radius: 8px; padding: 0.5rem 1.25rem; font-size: 0.84rem; font-weight: 700; cursor: pointer;">
            <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Guardar Institución
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ time() }}"></script>
<script>
  // Handlers para abrir y cerrar el modal
  function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
      el.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(id) {
    const el = id ? document.getElementById(id) : document.querySelector('.modal-backdrop.open');
    if (el) {
      el.classList.remove('open');
      document.body.style.overflow = '';
    }
  }
</script>
@endpush
