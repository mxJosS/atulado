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
                  @if(auth()->user()->is_admin)
                  <a href="{{ route('admin.instituciones.show', ['institucion' => $inst, 'editar' => 1]) }}" class="btn-atl linea sm" title="Editar datos de la empresa"><i class="fa-solid fa-pen"></i> Editar</a>
                  <button type="button" class="btn-atl linea sm" style="color: #B02418;" title="Eliminar (pasa a Archivadas)"
                          data-open="m-archivar" data-archivar
                          data-url="{{ route('admin.instituciones.archivar', $inst) }}"
                          data-nombre="{{ $inst->nombre_corto }}"
                          data-padron="{{ $inst->padron_count }}">
                    <i class="fa-solid fa-trash-can"></i> Eliminar
                  </button>
                  @endif
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

  {{-- Archivadas: se ven completas y se restauran; aquí también se eliminan para siempre --}}
  @if(auth()->user()->is_admin && $archivadas->isNotEmpty())
  <div class="card-atl" id="archivadas" style="margin-bottom: 2rem;">
    <div class="card-atl-head">
      <div>
        <h3>Archivadas ({{ $archivadas->count() }})</h3>
        <p>No operan ni cuentan en el tablero, pero toda su información se conserva. Puedes restaurarlas en cualquier momento.</p>
      </div>
    </div>

    <div class="table-wrap" style="overflow-x: auto;">
      <table class="data" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="border-bottom: 1.5px solid #DCE8E0; font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #6E887E; text-transform: uppercase;">
            <th style="padding: 0.75rem 1rem;">Organización</th>
            <th style="padding: 0.75rem 1rem;">Archivada</th>
            <th style="padding: 0.75rem 1rem; text-align: right;">Padrón</th>
            <th style="padding: 0.75rem 1rem; text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($archivadas as $inst)
            <tr style="border-bottom: 1px solid #EEF4F0; font-size: 0.88rem; opacity: .85;">
              <td style="padding: 0.9rem 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                  <span class="logo-inst" style="background: #8EADA4;">{{ $inst->iniciales }}</span>
                  <div>
                    <a href="{{ route('admin.instituciones.show', $inst) }}" style="font-weight: 700; color: #1A2620; text-decoration: none;">{{ $inst->nombre_corto }}</a>
                    <div style="font-size: 0.76rem; color: #6E887E;">{{ $inst->sector ?: 'Sin sector' }} · {{ $inst->ciudad ?: 'Sin ciudad' }}</div>
                  </div>
                </div>
              </td>
              <td style="padding: 0.9rem 1rem; font-size: 0.8rem; color: #556860;">
                {{ $inst->archivada_en->format('d/m/Y H:i') }}{{ $inst->archivadaPor ? ' · ' . $inst->archivadaPor->name : '' }}
              </td>
              <td style="padding: 0.9rem 1rem; text-align: right; font-family: 'IBM Plex Mono', monospace;">
                <b>{{ $inst->activas_count }}</b><span style="color: #8EADA4;"> / {{ $inst->padron_count }}</span>
                @if($inst->casos_abiertos_count > 0)
                  <div><span class="chip-atl rojo" style="margin-top: 4px;">{{ $inst->casos_abiertos_count }} {{ $inst->casos_abiertos_count === 1 ? 'caso abierto' : 'casos abiertos' }}</span></div>
                @endif
              </td>
              <td style="padding: 0.9rem 1rem; text-align: right;">
                <div style="display: inline-flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;">
                  <a href="{{ route('admin.instituciones.show', $inst) }}" class="btn-atl suave sm"><i class="fa-solid fa-eye"></i> Ver</a>
                  <form method="POST" action="{{ route('admin.instituciones.restaurar', $inst) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-atl linea sm"><i class="fa-solid fa-rotate-left"></i> Restaurar</button>
                  </form>
                  <button type="button" class="btn-atl linea sm" style="color: #B02418;"
                          data-open="m-eliminar" data-eliminar
                          data-url="{{ route('admin.instituciones.destroy', $inst) }}"
                          data-nombre="{{ $inst->nombre_corto }}"
                          data-casos="{{ $inst->casos_abiertos_count }}">
                    <i class="fa-solid fa-trash-can"></i> Eliminar para siempre
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>

@if(auth()->user()->is_admin)
{{-- ══════════ ARCHIVAR («Eliminar» desde la lista) ══════════ --}}
<div class="modal-backdrop" id="m-archivar">
  <div class="modal modal-atl" style="max-width: 520px;">
    <div class="modal-head">
      <div>
        <h2>Eliminar institución</h2>
        <div class="sub" data-archivar-nombre></div>
      </div>
      <button class="x" type="button" data-close="m-archivar" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="#" id="form-archivar">
      @csrf
      <div class="modal-body">
        <p style="margin: 0 0 0.75rem; font-size: 0.9rem;">La institución pasa a <b>Archivadas</b>: deja de operar y sale del tablero, pero <b>no se borra nada</b>.
          Su información, su padrón (<span data-archivar-padron></span> personas) y su historial se siguen viendo y puedes restaurarla en cualquier momento.</p>
        <p style="margin: 0; font-size: 0.82rem; color: #6E887E;">Para borrarla para siempre, elimínala después desde «Archivadas».</p>
      </div>
      <div class="modal-foot">
        <span></span>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn-atl linea" data-close="m-archivar">Cancelar</button>
          <button type="submit" class="btn-atl primario" style="background: #B02418;"><i class="fa-solid fa-box-archive"></i> Mover a Archivadas</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══════════ ELIMINAR PARA SIEMPRE (desde Archivadas) ══════════ --}}
<div class="modal-backdrop" id="m-eliminar">
  <div class="modal modal-atl" style="max-width: 560px;">
    <div class="modal-head" style="background: #6E140C;">
      <div>
        <h2>Eliminar para siempre</h2>
        <div class="sub" data-eliminar-nombre></div>
      </div>
      <button class="x" type="button" data-close="m-eliminar" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="#" id="form-eliminar">
      @csrf
      @method('DELETE')
      <div class="modal-body">
        <div class="nota-protocolo" style="margin-bottom: 0.85rem; border-color: #E9B7B0; background: #FCEEEC;">
          <i class="fa-solid fa-triangle-exclamation" style="color: #B02418;"></i>
          <span><b>No se puede deshacer.</b> Se borran la institución, sus áreas, su padrón y las cuentas de sus personas con todo su historial:
            registros diarios, cuestionarios, casos de crisis y bitácora clínica.
            Administración, clínicos, profesionales y quien también esté en otra institución sólo se desvinculan.</span>
        </div>
        <p data-eliminar-casos style="display: none; margin: 0 0 0.75rem; color: #B02418; font-weight: 700; font-size: 0.86rem;"></p>
        <label class="form-group-label" for="eliminar-confirmacion">Escribe <b data-eliminar-nombre-exacto></b> para confirmar</label>
        <input class="form-input-styled" id="eliminar-confirmacion" name="confirmacion" autocomplete="off" required>
      </div>
      <div class="modal-foot">
        <span></span>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn-atl linea" data-close="m-eliminar">Cancelar</button>
          <button type="submit" class="btn-atl primario" id="eliminar-enviar" style="background: #6E140C;" disabled><i class="fa-solid fa-trash-can"></i> Eliminar para siempre</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endif

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
<script>
  // Un solo modal para archivar y otro para eliminar: el botón de cada fila les pasa la institución.
  document.addEventListener('click', (e) => {
    const archivar = e.target.closest('[data-archivar]');
    if (archivar) {
      document.getElementById('form-archivar').action = archivar.dataset.url;
      document.querySelector('[data-archivar-nombre]').textContent = archivar.dataset.nombre;
      document.querySelector('[data-archivar-padron]').textContent = archivar.dataset.padron;
    }

    const eliminar = e.target.closest('[data-eliminar]');
    if (eliminar) {
      const form = document.getElementById('form-eliminar');
      const campo = document.getElementById('eliminar-confirmacion');
      const casos = parseInt(eliminar.dataset.casos || '0', 10);
      const aviso = document.querySelector('[data-eliminar-casos]');
      form.action = eliminar.dataset.url;
      form.dataset.nombre = eliminar.dataset.nombre;
      document.querySelector('[data-eliminar-nombre]').textContent = eliminar.dataset.nombre;
      document.querySelector('[data-eliminar-nombre-exacto]').textContent = eliminar.dataset.nombre;
      campo.value = '';
      document.getElementById('eliminar-enviar').disabled = true;
      aviso.style.display = casos > 0 ? '' : 'none';
      aviso.textContent = casos > 0
        ? `Tiene ${casos} ${casos === 1 ? 'caso de crisis abierto' : 'casos de crisis abiertos'}: ciérralos con contacto humano verificado antes de eliminarla.`
        : '';
    }
  });

  document.getElementById('eliminar-confirmacion')?.addEventListener('input', (e) => {
    const form = document.getElementById('form-eliminar');
    document.getElementById('eliminar-enviar').disabled = e.target.value.trim() !== form.dataset.nombre;
  });
</script>
@if(($errors->any() && old('_form') === 'alta') || request()->boolean('alta'))
<script>
  document.addEventListener('DOMContentLoaded', () => openModal('m-alta-institucion'));
</script>
@endif
@endpush
