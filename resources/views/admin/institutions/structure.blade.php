@extends('layouts.admin')

@section('title', 'Altas y Estructura Organizacional')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="admin-content-canvas" style="padding: 1.75rem 2rem;">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem;">
        Consola A Tu Lado &rsaquo; <b>Altas y Estructura</b>
      </div>
      <h1 style="font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0;">
        Altas y Estructura Organizacional
      </h1>
      <p style="font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; max-width: 820px;">
        Gestión de padrones por empresa, carga masiva por CSV y asignación de departamentos a macro-grupos operativos.
      </p>
    </div>

    <div style="display: flex; gap: 0.75rem;">
      <button class="btn btn-secondary" onclick="alert('Descargando plantilla CSV oficial de A Tu Lado...')" style="padding: 0.55rem 1rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border-radius: 9px;">
        <i class="fa-solid fa-download"></i>
        <span>Descargar Plantilla CSV</span>
      </button>
      <button class="btn btn-primary" onclick="alert('Abriendo asistente de carga masiva...')" style="background: #2E5D4B; color: #FFFFFF; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border-radius: 9px; border: none; cursor: pointer;">
        <i class="fa-solid fa-file-arrow-up"></i>
        <span>Cargar Padrón CSV</span>
      </button>
    </div>
  </div>

  @if(empty($institutions) || $institutions->isEmpty())
    <!-- Estado Limpio cuando no hay instituciones -->
    <div style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 3.5rem 2rem; text-align: center; max-width: 680px; margin: 2rem auto;">
      <div style="width: 64px; height: 64px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
        <i class="fa-solid fa-file-arrow-up"></i>
      </div>
      <h2 style="font-family: 'Fraunces', serif; font-size: 1.45rem; font-weight: 700; color: #1A2620; margin: 0 0 0.75rem;">
        No hay instituciones registradas aún
      </h2>
      <p style="color: #6E887E; font-size: 0.92rem; line-height: 1.5; margin: 0 auto 1.5rem;">
        Para cargar padrones de colaboradores mediante CSV y estructurar macro-grupos operativos, primero registra una empresa u organización en la plataforma.
      </p>
      <div style="display: flex; gap: 0.75rem; justify-content: center;">
        <a href="{{ route('admin.institutions.index') }}" class="btn btn-primary" style="background: #2E5D4B; color: #FFFFFF; border-radius: 8px; padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-building-shield"></i> Registrar Primera Institución
        </a>
      </div>
    </div>
  @else
    <!-- Selector de Institución -->
    <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem; margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <label style="font-size: 0.86rem; font-weight: 700; color: #1A2620;">Organización en gestión:</label>
          <select class="form-control" onchange="window.location.href='/admin/altas-estructura?inst=' + this.value" style="background: #F8FAF9; border: 1.5px solid #DCE8E0; border-radius: 8px; padding: 0.45rem 0.85rem; font-size: 0.86rem; font-weight: 600;">
            @foreach($institutions as $instOption)
              <option value="{{ $instOption->slug }}" {{ ($selectedInst && $selectedInst->id === $instOption->id) ? 'selected' : '' }}>
                {{ $instOption->name }} ({{ $instOption->users_count }} colaboradores)
              </option>
            @endforeach
          </select>
        </div>

        <div style="display: flex; gap: 0.5rem; font-size: 0.82rem; font-family: 'IBM Plex Mono', monospace;">
          <span style="background: #E3F3E9; color: #1E8449; padding: 3px 8px; border-radius: 6px; font-weight: 600;">{{ $selectedInst->active_count ?? 0 }} Activos</span>
          <span style="background: #FBF2D4; color: #8A6D00; padding: 3px 8px; border-radius: 6px; font-weight: 600;">{{ max(0, ($selectedInst->users_count ?? 0) - ($selectedInst->active_count ?? 0)) }} Pendientes</span>
        </div>
      </div>
    </div>

    <!-- Estructura de Departamentos y Grupos -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;" class="grid-2-col">
      
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 700; color: #1A2620; margin: 0;">
            Áreas y Macro-Grupos Asignados
          </h3>
          <button class="btn btn-sm" onclick="alert('Formulario para registrar nueva cuadrilla o área')" style="background: #EEF4F0; color: #2E5D4B; border-radius: 6px; font-weight: 600; padding: 5px 10px; border: none; cursor: pointer;">
            <i class="fa-solid fa-plus"></i> Nueva Área
          </button>
        </div>

        <table class="data" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
          <thead>
            <tr style="border-bottom: 1.5px solid #DCE8E0; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; color: #6E887E; text-transform: uppercase;">
              <th style="padding: 0.65rem 0.5rem;">Área / Departamento</th>
              <th style="padding: 0.65rem 0.5rem;">Macro-Grupo</th>
              <th style="padding: 0.65rem 0.5rem;">Contacto</th>
              <th style="padding: 0.65rem 0.5rem; text-align: right;">Padrón</th>
              <th style="padding: 0.65rem 0.5rem;">Turno</th>
            </tr>
          </thead>
          <tbody>
            @php
              $rawGroups = $selectedInst->departments_data ?? [];
              $hasRows = false;
            @endphp
            @foreach($rawGroups as $grp)
              @foreach($grp['departments'] ?? [] as $d)
                @php $hasRows = true; @endphp
                <tr style="border-bottom: 1px solid #EEF4F0;">
                  <td style="padding: 0.75rem 0.5rem;"><b>{{ $d['name'] }}</b></td>
                  <td style="padding: 0.75rem 0.5rem;"><span style="background: #EEF4F0; color: #2E5D4B; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">{{ $grp['name'] }}</span></td>
                  <td style="padding: 0.75rem 0.5rem; color: #556860; font-size: 0.82rem;">{{ $selectedInst->contact_name }}</td>
                  <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600;">{{ $d['active'] ?? 0 }}</td>
                  <td style="padding: 0.75rem 0.5rem; font-size: 0.8rem;">General</td>
                </tr>
              @endforeach
            @endforeach
            @if(!$hasRows)
              <tr>
                <td colspan="5" style="text-align: center; padding: 2rem; color: #6E887E; font-size: 0.85rem;">
                  Sin áreas configuradas en esta institución aún.
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
  @endif

    <!-- Reglas de Privacidad y Agregación -->
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
        <h4 style="font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: #1A2620; margin: 0 0 0.65rem;">
          Reglas de Anonimización NOM-035
        </h4>
        <div style="background: #EBF3EF; padding: 0.85rem; border-radius: 8px; border-left: 3px solid #2E5D4B; font-size: 0.8rem; color: #234739; line-height: 1.5; margin-bottom: 0.85rem;">
          <b>Protección contra ingeniería inversa:</b> Los datos mostrados a la empresa siempre se calculan en porcentajes agregados. Si un área tiene menos de 15 personas, sus resultados se fusionan automáticamente con su Macro-Grupo.
        </div>
        <ul style="padding-left: 1.1rem; margin: 0; font-size: 0.8rem; color: #556860; line-height: 1.5;">
          <li>Sin identificadores nominales en reportes patronales.</li>
          <li>Acceso clínico protegido por secreto profesional.</li>
          <li>Desvinculación laboral automática al cesar el contrato.</li>
        </ul>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
        <h4 style="font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: #1A2620; margin: 0 0 0.5rem;">
          Invitaciones y Activación
        </h4>
        <p style="font-size: 0.8rem; color: #556860; line-height: 1.4; margin-bottom: 0.75rem;">
          Al subir el CSV, el sistema envía un enlace único con token de activación individual al correo de cada colaborador o genera códigos QR para frentes de obra.
        </p>
        <button class="btn btn-secondary" onclick="alert('Reenviando invitaciones a 61 colaboradores pendientes...')" style="width: 100%; justify-content: center; padding: 0.5rem; font-size: 0.82rem; border-radius: 8px;">
          <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Reenviar invitaciones pendientes
        </button>
      </div>
    </div>

  </div>

</div>
@endsection
