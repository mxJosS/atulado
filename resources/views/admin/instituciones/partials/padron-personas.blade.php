{{-- Lista del padrón (operación de administración: sin datos clínicos) --}}
@php
  $estadoCuenta = [
    'activo' => ['Cuenta activa', ''],
    'invitado' => ['Sin activar', 'gris'],
    'suspendido' => ['Suspendida', 'ambar'],
    'baja' => ['Baja', 'rojo'],
  ];
@endphp

<div class="card-atl" style="margin-bottom: 1.5rem;">
  <div class="card-atl-head">
    <div>
      <h3>Personas en el padrón</h3>
      <p>{{ $personas->where('estado', '!=', 'baja')->count() }} en padrón · {{ $personas->where('estado', 'baja')->count() }} de baja. Las bajas conservan su historial.@unless($esAdmin) <b>Sólo lectura.</b>@endunless</p>
    </div>
@if($esAdmin)
    <button type="button" class="btn-atl primario sm" data-open="m-persona-nueva" @disabled($opcionesArea->isEmpty()) title="{{ $opcionesArea->isEmpty() ? 'Primero crea las áreas en «Áreas y Macro-Grupos»' : '' }}">
      <i class="fa-solid fa-user-plus"></i> Agregar persona
    </button>
@endif
  </div>

  @if($personas->isEmpty())
    <p style="color: #6E887E; font-size: 0.88rem; margin: 0;">Aún no hay nadie. Sube el padrón en Excel arriba o agrega personas una por una.</p>
  @else
    <input type="search" class="form-input-styled" placeholder="Buscar por nombre, correo, número de empleado o área…" style="margin-bottom: 0.75rem;"
           oninput="filtrarTabla(this, 'tabla-padron')">
    <div style="overflow-x: auto; max-height: 560px; overflow-y: auto; border: 1px solid #EEF4F0; border-radius: 10px;">
      <table class="padron-tabla" id="tabla-padron">
        <thead><tr><th>Persona</th><th>Núm. empleado</th><th>Área</th><th>Puesto</th><th>Turno</th><th>Cuenta</th><th></th></tr></thead>
        <tbody>
          @foreach($personas as $p)
            @php [$textoEstado, $tono] = $estadoCuenta[$p->estado] ?? [$p->estado, 'gris']; @endphp
            <tr style="{{ $p->estado === 'baja' ? 'opacity: .6;' : '' }}">
              <td>
                <b>{{ $p->user?->name }}</b>
                <div class="mono" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #6E887E;">{{ $p->folio }} · {{ $p->user?->email }}</div>
              </td>
              <td class="mono">{{ $p->numero_empleado ?: '—' }}</td>
              <td>{{ $p->departamento?->nombre ?: '—' }}</td>
              <td>{{ $p->puesto ?: '—' }}</td>
              <td>{{ $p->turno ? ucfirst($p->turno) : '—' }}{{ $p->horario ? ' · ' . $p->horario : '' }}</td>
              <td><span class="chip-atl {{ $tono }}">{{ $textoEstado }}</span></td>
              <td style="white-space: nowrap; text-align: right;">
                @if($esAdmin)
                @if($p->estado === 'baja')
                  @if($p->puedeReactivarse())
                    <form method="POST" action="{{ route('admin.instituciones.personas.reactivar', [$institucion, $p]) }}" style="display: inline;">
                      @csrf
                      <button class="btn-atl linea sm" type="submit"><i class="fa-solid fa-rotate-left"></i> Reactivar</button>
                    </form>
                  @endif
                @else
                  <button type="button" class="btn-atl linea sm" data-editar-persona
                          data-url="{{ route('admin.instituciones.personas.update', [$institucion, $p]) }}"
                          data-nombre="{{ $p->user?->name }}" data-numero="{{ $p->numero_empleado }}"
                          data-area="{{ $p->departamento_id }}" data-puesto="{{ $p->puesto }}"
                          data-turno="{{ $p->turno }}" data-horario="{{ $p->horario }}"
                          data-activa="{{ $p->estado === 'activo' ? '1' : '0' }}">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <form method="POST" action="{{ route('admin.instituciones.personas.baja', [$institucion, $p]) }}" style="display: inline;"
                        onsubmit="return confirm('¿Dar de baja a {{ addslashes($p->user?->name ?? '') }}? Deja de contar en el padrón; su historial se conserva.')">
                    @csrf
                    <button class="btn-atl linea sm" type="submit" style="color: #B02418;" title="Dar de baja"><i class="fa-solid fa-user-minus"></i></button>
                  </form>
                @endif
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

{{-- Alta y edición de una persona --}}
@if($esAdmin)
@foreach(['nueva' => null, 'editar' => true] as $modo => $esEdicion)
  <div class="modal-backdrop" id="m-persona-{{ $modo }}">
    <div class="modal modal-atl">
      <div class="modal-head">
        <div>
          <h2>{{ $esEdicion ? 'Editar persona' : 'Agregar persona al padrón' }}</h2>
          <div class="sub">{{ $esEdicion ? 'Datos laborales en ' . $institucion->nombre_corto : 'Quedará sin activar hasta que acepte su invitación.' }}</div>
        </div>
        <button class="x" type="button" data-close="m-persona-{{ $modo }}" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form method="POST" action="{{ $esEdicion ? (old('_form') === 'persona-editar' ? old('_url') : '#') : route('admin.instituciones.personas.store', $institucion) }}" id="form-persona-{{ $modo }}">
        @csrf
        @if($esEdicion)
          @method('PUT')
          <input type="hidden" name="_url" value="{{ old('_form') === 'persona-editar' ? old('_url') : '' }}">
        @endif
        <input type="hidden" name="_form" value="persona-{{ $modo }}">
        <div class="modal-body">
          <div class="form-fila c2-1">
            <div>
              <label class="form-group-label">Nombre completo *</label>
              <input class="form-input-styled" name="nombre" required maxlength="255" value="{{ old('_form') === 'persona-' . $modo ? old('nombre') : '' }}">
              <span class="form-hint-styled" data-aviso-nombre style="display: none;">La persona ya activó su cuenta: su nombre lo controla ella.</span>
            </div>
            <div>
              <label class="form-group-label">Núm. de empleado *</label>
              <input class="form-input-styled" name="numero_empleado" required maxlength="255" value="{{ old('_form') === 'persona-' . $modo ? old('numero_empleado') : '' }}">
            </div>
          </div>
          @unless($esEdicion)
            <div class="form-fila">
              <div>
                <label class="form-group-label">Correo electrónico *</label>
                <input class="form-input-styled" type="email" name="correo" required maxlength="255" value="{{ old('_form') === 'persona-nueva' ? old('correo') : '' }}">
                <span class="form-hint-styled">Aquí le llegará la invitación y con él iniciará sesión.</span>
              </div>
            </div>
          @endunless
          <div class="form-fila c1-1">
            <div>
              <label class="form-group-label">Área *</label>
              <select class="form-input-styled" name="departamento_id" required>
                <option value="">Elige un área…</option>
                @foreach($opcionesArea as [$id, $nombre])
                  <option value="{{ $id }}" @selected(old('_form') === 'persona-' . $modo && (int) old('departamento_id') === $id)>{{ $nombre }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="form-group-label">Puesto</label>
              <input class="form-input-styled" name="puesto" maxlength="255" value="{{ old('_form') === 'persona-' . $modo ? old('puesto') : '' }}">
            </div>
          </div>
          <div class="form-fila c1-1">
            <div>
              <label class="form-group-label">Turno</label>
              <select class="form-input-styled" name="turno">
                <option value="">—</option>
                @foreach(['matutino' => 'Matutino', 'vespertino' => 'Vespertino', 'nocturno' => 'Nocturno', 'mixto' => 'Mixto'] as $valor => $texto)
                  <option value="{{ $valor }}" @selected(old('_form') === 'persona-' . $modo && old('turno') === $valor)>{{ $texto }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="form-group-label">Horario</label>
              <input class="form-input-styled" name="horario" maxlength="255" placeholder="Ej. 22:00-06:00" value="{{ old('_form') === 'persona-' . $modo ? old('horario') : '' }}">
            </div>
          </div>
        </div>
        <div class="modal-foot">
          <span></span>
          <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-atl linea" data-close="m-persona-{{ $modo }}">Cancelar</button>
            <button type="submit" class="btn-atl primario"><i class="fa-solid fa-check"></i> {{ $esEdicion ? 'Guardar cambios' : 'Agregar' }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endforeach
@endif
