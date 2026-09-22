@extends('layouts.admin')

@section('title', 'Instituciones')

@push('styles')
  @include('admin.instituciones.partials.estilos')
@endpush

@section('content')
<div class="admin-content-canvas">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div class="crumbs-atl">Consola A Tu Lado &rsaquo; <b>Instituciones</b></div>
      <h1 class="titulo-atl">Instituciones</h1>
      <p class="sub-atl">Empresas y colegios contratantes, su contacto de enlace y su profesional clínico designado.</p>
    </div>
    @if(auth()->user()->is_admin)
    <button type="button" class="btn-atl primario" data-open="m-alta-institucion">
      <i class="fa-solid fa-plus"></i> Nueva Institución
    </button>
    @endif
  </div>

  {{-- Listado --}}
  <div class="card-atl" style="margin-bottom: 2rem;">
    <div class="card-atl-head">
      <div>
        <h3>Organizaciones registradas</h3>
        <p>Abre una organización para ver o editar sus datos y su estructura.</p>
      </div>
    </div>

    <div class="table-wrap" style="overflow-x: auto;">
      <table class="data" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="border-bottom: 1.5px solid #DCE8E0; font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #6E887E; text-transform: uppercase;">
            <th style="padding: 0.75rem 1rem;">Organización</th>
            <th style="padding: 0.75rem 1rem;">Contacto de enlace</th>
            <th style="padding: 0.75rem 1rem;">Profesional designado</th>
            <th style="padding: 0.75rem 1rem; text-align: right;">Padrón</th>
            <th style="padding: 0.75rem 1rem;">Estado</th>
            <th style="padding: 0.75rem 1rem; text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($instituciones as $inst)
            <tr style="border-bottom: 1px solid #EEF4F0; font-size: 0.88rem;">
              <td style="padding: 0.9rem 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                  <span class="logo-inst" style="background: {{ $inst->color ?: '#2E5D4B' }};">{{ $inst->iniciales }}</span>
                  <div>
                    <a href="{{ route('admin.instituciones.show', $inst) }}" style="font-weight: 700; color: #1A2620; text-decoration: none;">{{ $inst->nombre_corto }}</a>
                    <div style="font-size: 0.76rem; color: #6E887E;">
                      {{ $inst->sector ?: 'Sin sector' }} · {{ $inst->ciudad ?: 'Sin ciudad' }} · {{ $inst->departamentos_count }} {{ $inst->departamentos_count === 1 ? 'área' : 'áreas' }}
                    </div>
                  </div>
                </div>
              </td>
              <td style="padding: 0.9rem 1rem;">
                <div style="font-weight: 500; color: #1A2620;">{{ $inst->contacto_nombre ?: '—' }}</div>
                <div style="font-size: 0.76rem; color: #6E887E;">{{ $inst->contacto_email }}</div>
              </td>
              <td style="padding: 0.9rem 1rem;">
                @if($inst->profesional_nombre)
                  <div style="font-weight: 500; color: #1A2620;">{{ $inst->profesional_nombre }}</div>
                  <div style="font-size: 0.76rem; color: #6E887E;">Céd. {{ $inst->profesional_cedula ?: 'sin registrar' }}</div>
                @else
                  <span class="chip-atl ambar"><i class="fa-solid fa-triangle-exclamation"></i> Falta designar</span>
                @endif
              </td>
              <td style="padding: 0.9rem 1rem; text-align: right; font-family: 'IBM Plex Mono', monospace;">
                <b>{{ $inst->activas_count }}</b><span style="color: #8EADA4;"> / {{ $inst->padron_count }}</span>
                @if($inst->casos_abiertos_count > 0)
                  <div><span class="chip-atl rojo" style="margin-top: 4px;">{{ $inst->casos_abiertos_count }} {{ $inst->casos_abiertos_count === 1 ? 'caso abierto' : 'casos abiertos' }}</span></div>
                @endif
              </td>
              <td style="padding: 0.9rem 1rem;">
                <span class="chip-atl {{ $inst->estado === 'activa' ? '' : ($inst->estado === 'onboarding' ? 'gris' : 'ambar') }}">{{ $inst->estado_legible }}</span>
                @if($inst->vigencia_fin)
                  <div style="font-size: 0.74rem; color: #6E887E; margin-top: 4px;">Renueva {{ $inst->vigencia_fin->format('d/m/Y') }}</div>
                @endif
              </td>
              <td style="padding: 0.9rem 1rem; text-align: right;">
                <div style="display: inline-flex; gap: 6px;">
                  <a href="{{ route('admin.instituciones.show', $inst) }}" class="btn-atl suave sm"><i class="fa-solid fa-eye"></i> Ver</a>
                  <a href="{{ route('admin.instituciones.show', ['institucion' => $inst, 'editar' => 1]) }}" class="btn-atl linea sm" title="Editar datos de la empresa"><i class="fa-solid fa-pen"></i> Editar</a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align: center; padding: 3rem 1.5rem;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #EEF4F0; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                  <i class="fa-solid fa-building-circle-check"></i>
                </div>
                @if(auth()->user()->is_admin)
                <h4 style="font-family: 'Fraunces', serif; font-size: 1.15rem; color: #1A2620; margin: 0 0 0.5rem;">No hay instituciones registradas aún</h4>
                <p style="color: #6E887E; font-size: 0.88rem; max-width: 480px; margin: 0 auto 1.25rem;">
                  Da de alta la primera empresa u organización con su contacto de enlace, su profesional designado y sus macro-áreas.
                </p>
                <button type="button" class="btn-atl primario" data-open="m-alta-institucion">
                  <i class="fa-solid fa-plus"></i> Registrar primera institución
                </button>
                @else
                <h4 style="font-family: 'Fraunces', serif; font-size: 1.15rem; margin: 0 0 0.4rem; color: #1A2620;">Aún no tienes instituciones asignadas</h4>
                <p style="color: #6E887E; font-size: 0.88rem; margin: 0;">La administración te asigna las instituciones que atiendes desde «Usuarios». Mientras tanto no verás colaboradores ni casos.</p>
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══════════ MODAL DE ALTA ══════════ --}}
<div class="modal-backdrop" id="m-alta-institucion">
  <div class="modal modal-atl">
    <div class="modal-head">
      <div>
        <h2>Nueva Institución / Organización</h2>
        <div class="sub">Alta oficial de empresa contratante, enlace de RRHH y profesional clínico designado.</div>
      </div>
      <button class="x" type="button" data-close="m-alta-institucion" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form method="POST" action="{{ route('admin.instituciones.store') }}">
      @csrf
      <input type="hidden" name="_form" value="alta">

      <div class="modal-body">
        @include('admin.instituciones.partials.campos', ['i' => null, 'prefijo' => 'alta'])

        <div class="form-bloque">
          <div class="form-bloque-titulo">4. Estructura de Macro-Grupos y Contrato</div>

          <div style="margin-bottom: 0.85rem;">
            <label class="form-group-label" for="alta-macro_areas">Macro-Áreas Operativas (Separadas por comas) *</label>
            <input type="text" id="alta-macro_areas" name="macro_areas" class="form-input-styled @error('macro_areas') con-error @enderror" required
                   value="{{ old('macro_areas', 'Operaciones y Frente de Obra, Corporativo y Dirección, Servicios Generales') }}"
                   placeholder="Ej. Operaciones, Corporativo, Logística">
            @error('macro_areas') <span class="form-error">{{ $message }}</span> @enderror
            <span class="form-hint-styled">
              Se crearán automáticamente las macro-áreas del <b>Semáforo por Grupos</b>. Después podrás renombrarlas y agregar cuadrillas o salones dentro de cada una.
            </span>
          </div>

          @include('admin.instituciones.partials.contrato', ['i' => null, 'prefijo' => 'alta'])
        </div>
      </div>

      <div class="modal-foot">
        <span style="font-size: 0.76rem; color: #6E887E;"><i class="fa-solid fa-shield-halved"></i> Todos los datos se pueden editar después</span>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn-atl linea" data-close="m-alta-institucion">Cancelar</button>
          <button type="submit" class="btn-atl primario"><i class="fa-solid fa-check"></i> Guardar Institución</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ filemtime(public_path('vendor/paneles/panel.js')) }}"></script>
@if(($errors->any() && old('_form') === 'alta') || request()->boolean('alta'))
<script>
  document.addEventListener('DOMContentLoaded', () => openModal('m-alta-institucion'));
</script>
@endif
@endpush
