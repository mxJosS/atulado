@extends('layouts.admin')

@section('title', 'Altas y Estructura Organizacional')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ time() }}">
<style>
  .structure-tab-btn {
    padding: 0.65rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 600;
    border: none;
    background: transparent;
    color: #556860;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
  }
  .structure-tab-btn.active {
    color: #2E5D4B;
    border-bottom-color: #2E5D4B;
  }
  .modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(10, 20, 15, 0.65);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1.5rem;
  }
  .modal-card-custom {
    background: #FFFFFF;
    border-radius: 20px;
    width: 100%;
    max-width: 540px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    border: 1.5px solid #DCE8E0;
    overflow: hidden;
    animation: modalIn 0.2s ease-out;
  }
  @keyframes modalIn {
    from { opacity: 0; transform: scale(0.96) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
  }
</style>
@endpush

@section('content')
<div class="admin-content-canvas" style="padding: 1.75rem 2rem;">

  <!-- Header Section -->
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

    <!-- Actions (Descargar CSV, Nuevo Colaborador y Cargar Padrón) -->
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <a 
        href="{{ route('admin.structure.template') }}" 
        class="btn btn-secondary" 
        style="padding: 0.55rem 1rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border-radius: 9px; text-decoration: none; border: 1.5px solid #DCE8E0; background: #FFFFFF; color: #1A2620; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.2s;"
        onmouseover="this.style.background='#F8FAF9'; this.style.borderColor='#2E5D4B';"
        onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#DCE8E0';"
      >
        <i class="fa-solid fa-download" style="color: #2E5D4B;"></i>
        <span>Descargar Plantilla CSV</span>
      </a>

      @if(!empty($selectedInst))
        <button 
          type="button" 
          onclick="openModal('newCollaboratorModal')" 
          class="btn btn-primary" 
          style="background: #1B5E20; color: #FFFFFF; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border-radius: 9px; border: none; cursor: pointer; font-weight: 600; box-shadow: 0 3px 8px rgba(27,94,32,0.25); transition: all 0.2s;"
          onmouseover="this.style.background='#144718';"
          onmouseout="this.style.background='#1B5E20';"
        >
          <i class="fa-solid fa-user-plus"></i>
          <span>+ Nuevo Colaborador</span>
        </button>

        <button 
          type="button" 
          onclick="openModal('uploadCsvModal')" 
          class="btn btn-primary" 
          style="background: #2E5D4B; color: #FFFFFF; padding: 0.55rem 1.15rem; font-size: 0.84rem; display: flex; align-items: center; gap: 6px; border-radius: 9px; border: none; cursor: pointer; font-weight: 600; box-shadow: 0 3px 8px rgba(46,93,75,0.25); transition: all 0.2s;"
          onmouseover="this.style.background='#244A3C';"
          onmouseout="this.style.background='#2E5D4B';"
        >
          <i class="fa-solid fa-file-arrow-up"></i>
          <span>Cargar Padrón CSV</span>
        </button>
      @endif
    </div>
  </div>

  <!-- Status / Feedback Alerts -->
  @if(session('success'))
    <div style="background: #E8F5E9; border-left: 4px solid #2E5D4B; border-radius: 8px; padding: 0.95rem 1.15rem; margin-bottom: 1.25rem; font-size: 0.88rem; color: #1B5E20; display: flex; align-items: center; gap: 10px;">
      <i class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div style="background: #FFEBEE; border-left: 4px solid #D32F2F; border-radius: 8px; padding: 0.95rem 1.15rem; margin-bottom: 1.25rem; font-size: 0.88rem; color: #C62828;">
      <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; margin-bottom: 4px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Ocurrieron los siguientes inconvenientes:</span>
      </div>
      <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.84rem;">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

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
          <select class="form-control" onchange="window.location.href='/admin/altas-estructura?inst=' + this.value" style="background: #F8FAF9; border: 1.5px solid #DCE8E0; border-radius: 8px; padding: 0.45rem 0.85rem; font-size: 0.86rem; font-weight: 600; color: #1A2620;">
            @foreach($institutions as $instOption)
              <option value="{{ $instOption->slug }}" {{ ($selectedInst && $selectedInst->id === $instOption->id) ? 'selected' : '' }}>
                {{ $instOption->name }} ({{ $instOption->users_count }} colaboradores)
              </option>
            @endforeach
          </select>
        </div>

        <div style="display: flex; gap: 0.5rem; font-size: 0.82rem; font-family: 'IBM Plex Mono', monospace;">
          <span style="background: #E3F3E9; color: #1E8449; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
            <i class="fa-solid fa-users" style="margin-right: 4px;"></i> {{ $collaborators->count() }} Colaboradores en Padrón
          </span>
          <span style="background: #EEF4F0; color: #2E5D4B; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
            <i class="fa-solid fa-building" style="margin-right: 4px;"></i> {{ $selectedInst->category }}
          </span>
        </div>
      </div>
    </div>

    <!-- Contenido Principal: Áreas y Padrón de Listas -->
    <div style="display: grid; grid-template-columns: 2.1fr 1fr; gap: 1.5rem;" class="grid-2-col">
      
      <!-- Panel de Pestañas: Áreas vs Padrón de Colaboradores -->
      <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.5rem;">
        
        <!-- Navigation Tabs -->
        <div style="display: flex; border-bottom: 1.5px solid #E5EFE9; margin-bottom: 1.25rem; gap: 0.5rem;">
          <button type="button" class="structure-tab-btn active" id="tabBtnAreas" onclick="switchStructureTab('areas')">
            <i class="fa-solid fa-network-wired" style="margin-right: 6px;"></i> Áreas y Macro-Grupos
          </button>
          <button type="button" class="structure-tab-btn" id="tabBtnColabs" onclick="switchStructureTab('colaboradores')">
            <i class="fa-solid fa-id-card-clip" style="margin-right: 6px;"></i> Padrón de Colaboradores ({{ $collaborators->count() }})
          </button>
        </div>

        <!-- ══════════ PESTAÑA 1: ÁREAS Y MACRO-GRUPOS ══════════ -->
        <div id="tabContentAreas">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
              <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0;">
                Áreas y Macro-Grupos Asignados
              </h3>
              <p style="font-size: 0.8rem; color: #6E887E; margin: 2px 0 0;">
                Estructura operativa para la segmentación del Semáforo y reportes NOM-035.
              </p>
            </div>
            
            <button 
              type="button" 
              onclick="openModal('newAreaModal')" 
              class="btn btn-sm" 
              style="background: #EEF4F0; color: #2E5D4B; border-radius: 8px; font-weight: 600; padding: 6px 12px; border: 1px solid #DCE8E0; cursor: pointer; display: flex; align-items: center; gap: 6px;"
              onmouseover="this.style.background='#E1ECE6';"
              onmouseout="this.style.background='#EEF4F0';"
            >
              <i class="fa-solid fa-plus"></i>
              <span>Nueva Área</span>
            </button>
          </div>

          <div style="overflow-x: auto;">
            <table class="data" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
              <thead>
                <tr style="border-bottom: 1.5px solid #DCE8E0; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; color: #6E887E; text-transform: uppercase;">
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Área / Departamento</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Macro-Grupo</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Turno</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: right;">Padrón</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: center;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $rawGroups = $selectedInst->departments_data ?? [];
                  $hasRows = false;
                @endphp
                @foreach($rawGroups as $grp)
                  @foreach($grp['departments'] ?? [] as $d)
                    @php 
                      $hasRows = true; 
                      // Contar colaboradores reales asignados a esta área
                      $deptCount = $collaborators->filter(function($u) use ($d) {
                        return strcasecmp(trim($u->department ?? ''), trim($d['name'])) === 0;
                      })->count();
                    @endphp
                    <tr style="border-bottom: 1px solid #EEF4F0;">
                      <td style="padding: 0.75rem 0.5rem;">
                        <b style="color: #1A2620;">{{ $d['name'] }}</b>
                        @if(!empty($d['note']) && $d['note'] !== 'Área configurada manualmente.')
                          <div style="font-size: 0.75rem; color: #7A8E85;">{{ $d['note'] }}</div>
                        @endif
                      </td>
                      <td style="padding: 0.75rem 0.5rem;">
                        <span style="background: #EEF4F0; color: #2E5D4B; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem; font-weight: 600;">
                          {{ $grp['name'] }}
                        </span>
                      </td>
                      <td style="padding: 0.75rem 0.5rem; font-size: 0.82rem; color: #556860;">
                        {{ $d['shift'] ?? 'General' }}
                      </td>
                      <td style="padding: 0.75rem 0.5rem; text-align: right; font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: #1E8449;">
                        {{ $deptCount > 0 ? $deptCount : ($d['active'] ?? 0) }}
                      </td>
                      <td style="padding: 0.75rem 0.5rem; text-align: center;">
                        <form 
                          method="POST" 
                          action="{{ route('admin.structure.area.destroy') }}" 
                          onsubmit="return confirm('¿Confirmas que deseas eliminar el área \'{{ $d['name'] }}\' de la estructura de {{ $selectedInst->name }}?');" 
                          style="display: inline-block; margin: 0;"
                        >
                          @csrf
                          <input type="hidden" name="institution_id" value="{{ $selectedInst->id }}">
                          <input type="hidden" name="department_name" value="{{ $d['name'] }}">
                          <input type="hidden" name="macro_group_name" value="{{ $grp['name'] }}">
                          <button 
                            type="submit" 
                            title="Eliminar área" 
                            style="background: transparent; border: 1px solid #FFCDD2; color: #D32F2F; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='#FFEBEE'; this.style.borderColor='#EF5350';"
                            onmouseout="this.style.background='transparent'; this.style.borderColor='#FFCDD2';"
                          >
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                @endforeach

                @if(!$hasRows)
                  <tr>
                    <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: #6E887E; font-size: 0.88rem;">
                      <i class="fa-solid fa-folder-open" style="font-size: 1.5rem; color: #A8C2B5; display: block; margin-bottom: 0.5rem;"></i>
                      No hay áreas configuradas en esta institución aún.<br>
                      <button onclick="openModal('newAreaModal')" class="btn btn-sm" style="margin-top: 0.75rem; background: #2E5D4B; color: #FFFFFF; border-radius: 6px; padding: 4px 10px;">
                        + Agregar la primera área
                      </button>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

        <!-- ══════════ PESTAÑA 2: PADRÓN DE COLABORADORES ══════════ -->
        <div id="tabContentColabs" style="display: none;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
              <h3 style="font-family: 'Fraunces', serif; font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0;">
                Listado de Colaboradores
              </h3>
              <p style="font-size: 0.8rem; color: #6E887E; margin: 2px 0 0;">
                Personal dado de alta para <b>{{ $selectedInst->name }}</b>.
              </p>
            </div>

            <div style="display: flex; gap: 8px;">
              <button 
                type="button" 
                onclick="openModal('newCollaboratorModal')" 
                class="btn btn-sm" 
                style="background: #1B5E20; color: #FFFFFF; border-radius: 8px; font-weight: 600; padding: 6px 14px; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(27,94,32,0.25);"
              >
                <i class="fa-solid fa-user-plus"></i>
                <span>+ Agregar Colaborador</span>
              </button>

              <button 
                type="button" 
                onclick="openModal('uploadCsvModal')" 
                class="btn btn-sm" 
                style="background: #EEF4F0; color: #2E5D4B; border-radius: 8px; font-weight: 600; padding: 6px 12px; border: 1px solid #DCE8E0; cursor: pointer; display: flex; align-items: center; gap: 6px;"
              >
                <i class="fa-solid fa-file-arrow-up"></i>
                <span>Cargar Más por CSV</span>
              </button>
            </div>
          </div>

          <div style="overflow-x: auto;">
            <table class="data" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
              <thead>
                <tr style="border-bottom: 1.5px solid #DCE8E0; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; color: #6E887E; text-transform: uppercase;">
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Colaborador</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Área / Departamento</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Macro-Grupo</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Turno</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: left;">Puesto</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: center;">Estado</th>
                  <th style="padding: 0.65rem 0.5rem; text-align: center;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @forelse($collaborators as $colab)
                  <tr style="border-bottom: 1px solid #EEF4F0;">
                    <td style="padding: 0.75rem 0.5rem;">
                      <div style="font-weight: 600; color: #1A2620;">{{ $colab->name }}</div>
                      <div style="font-size: 0.76rem; color: #6E887E;">{{ $colab->email }}</div>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; font-size: 0.84rem; color: #1A2620;">
                      {{ $colab->department ?: 'Sin asignar' }}
                    </td>
                    <td style="padding: 0.75rem 0.5rem;">
                      <span style="background: #EEF4F0; color: #2E5D4B; padding: 2px 7px; border-radius: 4px; font-size: 0.74rem; font-weight: 600;">
                        {{ $colab->macro_group ?: 'General' }}
                      </span>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; font-size: 0.8rem; color: #556860;">
                      {{ $colab->shift ?: 'General' }}
                    </td>
                    <td style="padding: 0.75rem 0.5rem; font-size: 0.8rem; color: #556860;">
                      {{ $colab->position ?: 'Colaborador' }}
                      @if($colab->employee_number)
                        <div style="font-family: monospace; font-size: 0.72rem; color: #8A9E95;">#{{ $colab->employee_number }}</div>
                      @endif
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: center;">
                      <span style="background: #E8F5E9; color: #1B5E20; padding: 2px 8px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-check" style="font-size: 0.65rem;"></i> Activo
                      </span>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: center;">
                      <form 
                        method="POST" 
                        action="{{ route('admin.structure.collaborator.destroy') }}" 
                        onsubmit="return confirm('¿Confirmas que deseas desvincular al colaborador {{ $colab->name }} del padrón de {{ $selectedInst->name }}?');" 
                        style="display: inline-block; margin: 0;"
                      >
                        @csrf
                        <input type="hidden" name="institution_id" value="{{ $selectedInst->id }}">
                        <input type="hidden" name="user_id" value="{{ $colab->id }}">
                        <button 
                          type="submit" 
                          title="Remover de la institución" 
                          style="background: transparent; border: 1px solid #FFCDD2; color: #D32F2F; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem; cursor: pointer; transition: all 0.2s;"
                          onmouseover="this.style.background='#FFEBEE'; this.style.borderColor='#EF5350';"
                          onmouseout="this.style.background='transparent'; this.style.borderColor='#FFCDD2';"
                        >
                          <i class="fa-solid fa-user-minus"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #6E887E; font-size: 0.88rem;">
                      <i class="fa-solid fa-users-slash" style="font-size: 1.5rem; color: #A8C2B5; display: block; margin-bottom: 0.5rem;"></i>
                      No hay colaboradores registrados en el padrón de <b>{{ $selectedInst->name }}</b> aún.<br>
                      <div style="display: flex; gap: 8px; justify-content: center; margin-top: 0.75rem;">
                        <button onclick="openModal('newCollaboratorModal')" class="btn btn-sm" style="background: #1B5E20; color: #FFFFFF; border-radius: 6px; padding: 5px 12px; font-weight: 600; border: none; cursor: pointer;">
                          <i class="fa-solid fa-user-plus"></i> + Agregar Colaborador
                        </button>
                        <button onclick="openModal('uploadCsvModal')" class="btn btn-sm" style="background: #EEF4F0; color: #2E5D4B; border-radius: 6px; padding: 5px 12px; font-weight: 600; border: 1px solid #DCE8E0; cursor: pointer;">
                          <i class="fa-solid fa-file-arrow-up"></i> Cargar Padrón CSV
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Barra Lateral Informativa -->
      <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        
        <!-- Tarjeta NOM-035 -->
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

        <!-- Tarjeta de Instrucciones para Carga CSV -->
        <div class="card" style="background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.25rem;">
          <h4 style="font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: #1A2620; margin: 0 0 0.5rem;">
            Guía de Carga Masiva (CSV)
          </h4>
          <p style="font-size: 0.8rem; color: #556860; line-height: 1.4; margin-bottom: 0.75rem;">
            Sube el archivo exportado desde Recursos Humanos o nómina. Las columnas requeridas son <b>nombre</b> y <b>email</b>.
          </p>
          <div style="background: #F8FAF9; border-radius: 8px; padding: 0.65rem 0.85rem; font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #2E5D4B; margin-bottom: 0.85rem; word-break: break-all;">
            nombre,email,departamento,macro_grupo,turno,numero_empleado,puesto
          </div>
          <a href="{{ route('admin.structure.template') }}" style="font-size: 0.82rem; color: #2E5D4B; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-download"></i> Descargar plantilla de ejemplo
          </a>
        </div>

      </div>

    </div>
  @endif

</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL 1: CARGAR PADRÓN CSV                                  -->
<!-- ═══════════════════════════════════════════════════════════ -->
@if(!empty($selectedInst))
<div id="uploadCsvModal" class="modal-backdrop-custom" onclick="if(event.target === this) closeModal('uploadCsvModal')">
  <div class="modal-card-custom">
    
    <!-- Modal Header -->
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #EEF3F0; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: #1A2620; margin: 0;">
          Cargar Padrón CSV
        </h3>
        <p style="font-size: 0.8rem; color: #6E887E; margin: 2px 0 0;">
          Institución destino: <strong style="color: #2E5D4B;">{{ $selectedInst->name }}</strong>
        </p>
      </div>
      <button type="button" onclick="closeModal('uploadCsvModal')" style="background: none; border: none; font-size: 1.25rem; color: #8A9E95; cursor: pointer;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <form action="{{ route('admin.structure.import') }}" method="POST" enctype="multipart/form-data" style="padding: 1.5rem;">
      @csrf
      <input type="hidden" name="institution_id" value="{{ $selectedInst->id }}">

      <div style="margin-bottom: 1.25rem;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.5rem;">
          Selecciona tu archivo CSV (.csv):
        </label>
        <input 
          type="file" 
          name="file" 
          accept=".csv, text/csv, text/plain" 
          required
          style="width: 100%; padding: 0.75rem; border: 2px dashed #99C5A8; border-radius: 12px; background: #F8FAF9; font-size: 0.86rem; color: #1A2620; box-sizing: border-box; cursor: pointer;"
        />
        <div style="font-size: 0.74rem; color: #6E887E; margin-top: 6px;">
          Compatible con delimitador por comas (,) o punto y coma (;). Máximo 5MB.
        </div>
      </div>

      <div style="background: #F0F7F2; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.78rem; color: #2E5D4B; line-height: 1.5;">
        <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i>
        Si las filas contienen áreas o macro-grupos que aún no existían, el sistema los creará automáticamente en la estructura de la empresa.
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button 
          type="button" 
          onclick="closeModal('uploadCsvModal')" 
          style="background: #F3F7F5; border: 1px solid #DCE8E0; color: #556860; border-radius: 10px; padding: 0.6rem 1.15rem; font-size: 0.86rem; font-weight: 600; cursor: pointer;"
        >
          Cancelar
        </button>
        <button 
          type="submit" 
          style="background: #2E5D4B; border: none; color: #FFFFFF; border-radius: 10px; padding: 0.6rem 1.35rem; font-size: 0.86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"
        >
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Subir e Importar</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL 2: REGISTRAR NUEVA ÁREA                               -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="newAreaModal" class="modal-backdrop-custom" onclick="if(event.target === this) closeModal('newAreaModal')">
  <div class="modal-card-custom">
    
    <!-- Modal Header -->
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #EEF3F0; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 700; color: #1A2620; margin: 0;">
          Nueva Área / Cuadrilla
        </h3>
        <p style="font-size: 0.8rem; color: #6E887E; margin: 2px 0 0;">
          Agregar a: <strong style="color: #2E5D4B;">{{ $selectedInst->name }}</strong>
        </p>
      </div>
      <button type="button" onclick="closeModal('newAreaModal')" style="background: none; border: none; font-size: 1.25rem; color: #8A9E95; cursor: pointer;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form action="{{ route('admin.structure.area.store') }}" method="POST" style="padding: 1.5rem;">
      @csrf
      <input type="hidden" name="institution_id" value="{{ $selectedInst->id }}">

      <!-- Nombre del Área -->
      <div style="margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.4rem;">
          Nombre del Área o Departamento: *
        </label>
        <input 
          type="text" 
          name="name" 
          placeholder="Ej. Cuadrilla Nocturna - Mantenimiento" 
          required 
          style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
        />
      </div>

      <!-- Macro-Grupo -->
      <div style="margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.4rem;">
          Macro-Grupo Operativo: *
        </label>
        <input 
          type="text" 
          name="macro_group" 
          list="existingGroupsList"
          placeholder="Ej. Operaciones y Frente de Obra" 
          required 
          style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
        />
        <datalist id="existingGroupsList">
          @foreach($rawGroups as $grp)
            <option value="{{ $grp['name'] }}"></option>
          @endforeach
        </datalist>
        <div style="font-size: 0.72rem; color: #7A8E85; margin-top: 4px;">
          Escribe uno nuevo o selecciona uno de los existentes.
        </div>
      </div>

      <!-- Turno -->
      <div style="margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.4rem;">
          Turno Predeterminado:
        </label>
        <select name="shift" style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box; background: #FFFFFF;">
          <option value="General">General</option>
          <option value="Matutino">Matutino</option>
          <option value="Vespertino">Vespertino</option>
          <option value="Nocturno">Nocturno</option>
          <option value="Mixto / Rotativo">Mixto / Rotativo</option>
        </select>
      </div>

      <!-- Nota / Descripción -->
      <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.4rem;">
          Nota Interna (Opcional):
        </label>
        <input 
          type="text" 
          name="note" 
          placeholder="Ej. Cuadrilla en frente de obra Mérida-Cancún" 
          style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
        />
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button 
          type="button" 
          onclick="closeModal('newAreaModal')" 
          style="background: #F3F7F5; border: 1px solid #DCE8E0; color: #556860; border-radius: 10px; padding: 0.6rem 1.15rem; font-size: 0.86rem; font-weight: 600; cursor: pointer;"
        >
          Cancelar
        </button>
        <button 
          type="submit" 
          style="background: #2E5D4B; border: none; color: #FFFFFF; border-radius: 10px; padding: 0.6rem 1.35rem; font-size: 0.86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"
        >
          <i class="fa-solid fa-check"></i>
          <span>Guardar Área</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ══════════ MODAL: NUEVO COLABORADOR INDIVIDUAL ══════════ -->
<div 
  id="newCollaboratorModal" 
  style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(10, 20, 15, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;"
>
  <div style="background: #FFFFFF; border-radius: 18px; width: 100%; max-width: 600px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); border: 1.5px solid #DCE8E0; padding: 2rem; position: relative; max-height: 90vh; overflow-y: auto;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
      <div>
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #E8F5E9; color: #1B5E20; padding: 3px 10px; border-radius: 6px; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">
          <i class="fa-solid fa-user-plus"></i> Alta Individual
        </div>
        <h3 style="font-family: 'Fraunces', serif; font-size: 1.35rem; font-weight: 700; color: #1A2620; margin: 0;">
          Nuevo Colaborador
        </h3>
        <p style="font-size: 0.84rem; color: #556860; margin: 0.25rem 0 0;">
          Padrón de <b>{{ $selectedInst->name }}</b>
        </p>
      </div>
      <button 
        type="button" 
        onclick="closeModal('newCollaboratorModal')" 
        style="background: #F0F4F2; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #556860; cursor: pointer;"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.structure.collaborator.store') }}">
      @csrf
      <input type="hidden" name="institution_id" value="{{ $selectedInst->id }}">

      <!-- Grid 2 Columnas -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1rem;">
        <!-- Nombre -->
        <div style="grid-column: span 2;">
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Nombre Completo <span style="color: #C0392B;">*</span>:
          </label>
          <input 
            type="text" 
            name="name" 
            required 
            placeholder="Ej. Ing. Roberto Morales Mendoza" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
        </div>

        <!-- Correo Electrónico -->
        <div style="grid-column: span 2;">
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Correo Electrónico Institucional <span style="color: #C0392B;">*</span>:
          </label>
          <input 
            type="email" 
            name="email" 
            required 
            placeholder="ejemplo@itsoportecancun.com" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
        </div>

        <!-- Macro-Grupo -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Macro-Grupo <span style="color: #C0392B;">*</span>:
          </label>
          <input 
            type="text" 
            name="macro_group" 
            list="collaboratorMacroList" 
            required 
            placeholder="Ej. Operaciones y Frente de Obra" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
          <datalist id="collaboratorMacroList">
            @foreach($rawGroups as $grp)
              <option value="{{ $grp['macro_group'] ?? ($grp['name'] ?? '') }}">
            @endforeach
          </datalist>
        </div>

        <!-- Área / Departamento -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Área / Departamento <span style="color: #C0392B;">*</span>:
          </label>
          <input 
            type="text" 
            name="department" 
            list="collaboratorDeptList" 
            required 
            placeholder="Ej. Soporte Técnico en Sitio" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
          <datalist id="collaboratorDeptList">
            @foreach($rawGroups as $grp)
              @foreach($grp['departments'] ?? [] as $d)
                <option value="{{ $d['name'] ?? '' }}">
              @endforeach
            @endforeach
          </datalist>
        </div>

        <!-- Turno -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Turno:
          </label>
          <select name="shift" style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box; background: #FFFFFF;">
            <option value="Turno General">Turno General</option>
            <option value="Matutino">Matutino</option>
            <option value="Vespertino">Vespertino</option>
            <option value="Nocturno">Nocturno</option>
            <option value="Mixto / Rotativo">Mixto / Rotativo</option>
            <option value="Completo">Tiempo Completo</option>
          </select>
        </div>

        <!-- Puesto / Cargo -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Puesto / Cargo:
          </label>
          <input 
            type="text" 
            name="position" 
            placeholder="Ej. Auxiliar de Sistemas" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
        </div>

        <!-- Nº Empleado -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Nº Empleado / Nómina:
          </label>
          <input 
            type="text" 
            name="employee_number" 
            placeholder="Ej. EMP-088" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
        </div>

        <!-- Contraseña Inicial -->
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem;">
            Contraseña Inicial:
          </label>
          <input 
            type="text" 
            name="password" 
            placeholder="Colaborador_2026 (por defecto)" 
            style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #DCE8E0; border-radius: 9px; font-size: 0.88rem; box-sizing: border-box;"
          />
        </div>
      </div>

      <!-- Notice -->
      <div style="background: #F4F8F5; border-radius: 8px; padding: 0.75rem 0.95rem; font-size: 0.8rem; color: #40574D; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-info" style="color: #2E5D4B; font-size: 0.95rem;"></i>
        <span>El colaborador quedará verificado automáticamente para ingresar de inmediato con su correo y contraseña.</span>
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button 
          type="button" 
          onclick="closeModal('newCollaboratorModal')" 
          style="background: #F3F7F5; border: 1px solid #DCE8E0; color: #556860; border-radius: 10px; padding: 0.6rem 1.15rem; font-size: 0.86rem; font-weight: 600; cursor: pointer;"
        >
          Cancelar
        </button>
        <button 
          type="submit" 
          style="background: #1B5E20; border: none; color: #FFFFFF; border-radius: 10px; padding: 0.6rem 1.35rem; font-size: 0.86rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(27,94,32,0.25);"
        >
          <i class="fa-solid fa-user-check"></i>
          <span>Guardar Colaborador</span>
        </button>
      </div>
    </form>

  </div>
</div>
@endif

<script>
function switchStructureTab(tab) {
  const tabAreas = document.getElementById('tabContentAreas');
  const tabColabs = document.getElementById('tabContentColabs');
  const btnAreas = document.getElementById('tabBtnAreas');
  const btnColabs = document.getElementById('tabBtnColabs');

  if (tab === 'areas') {
    tabAreas.style.display = 'block';
    tabColabs.style.display = 'none';
    btnAreas.classList.add('active');
    btnColabs.classList.remove('active');
  } else {
    tabAreas.style.display = 'none';
    tabColabs.style.display = 'block';
    btnAreas.classList.remove('active');
    btnColabs.classList.add('active');
  }
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'flex';
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'none';
  }
}

// Cerrar con Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal('uploadCsvModal');
    closeModal('newAreaModal');
    closeModal('newCollaboratorModal');
  }
});

// Auto abrir modal o tab por query params
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('tab') === 'colaboradores') {
    switchStructureTab('colaboradores');
  }
  if (urlParams.get('action') === 'new_user') {
    openModal('newCollaboratorModal');
  }
});
</script>
@endsection
