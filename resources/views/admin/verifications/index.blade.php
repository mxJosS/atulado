@extends('layouts.admin')

@section('title', 'Bandeja de Acreditación Profesional — Dirección Clínica')

@section('content')
<div style="max-width: 1080px; margin: 0 auto;">

  <!-- HEADER -->
  <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— DIRECCIÓN CLÍNICA & AUDITORÍA</span>
      <h1 style="font-size: 1.95rem; margin-top: 0.2rem; color: #1A2620; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-id-card-clip" style="color: #2E5D4B;"></i>
        <span>Acreditaciones Profesionales</span>
      </h1>
      <p style="color: #556860; font-size: 0.92rem; margin-top: 0.25rem;">
        Cotejo, revisión y dictamen oficial de cédulas y grados académicos para especialistas de la salud mental.
      </p>
    </div>

    <a href="https://www.cedulaprofesional.sep.gob.mx/" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm" style="gap: 8px; font-size: 0.82rem; padding: 0.55rem 0.95rem; border-radius: 9px; box-shadow: var(--shadow-sm);">
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
      <span>Consultar Registro Oficial SEP</span>
    </a>
  </div>

  <!-- STATS CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.15rem; margin-bottom: 2rem;">
    <!-- Pendientes -->
    <a href="{{ route('admin.verifications.index', ['filtro' => 'pendiente']) }}" style="text-decoration: none;">
      <div class="card" style="border-radius: 14px; padding: 1.25rem; border-left: 4px solid #F39C12; {{ $filter === 'pendiente' ? 'box-shadow: 0 0 0 2px #F39C12;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 0.74rem; font-family: var(--font-mono); color: #B7791F; font-weight: 700; text-transform: uppercase;">Por Auditar</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #8A5300; margin-top: 0.2rem;">{{ $counts['pendiente'] }}</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(243, 156, 18, 0.15); color: #B7791F; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-hourglass-half"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Aprobadas -->
    <a href="{{ route('admin.verifications.index', ['filtro' => 'aprobada']) }}" style="text-decoration: none;">
      <div class="card" style="border-radius: 14px; padding: 1.25rem; border-left: 4px solid #2E5D4B; {{ $filter === 'aprobada' ? 'box-shadow: 0 0 0 2px #2E5D4B;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 0.74rem; font-family: var(--font-mono); color: #2E5D4B; font-weight: 700; text-transform: uppercase;">Acreditadas</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #1E4A25; margin-top: 0.2rem;">{{ $counts['aprobada'] }}</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(46, 93, 75, 0.12); color: #2E5D4B; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-circle-check"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Rechazadas -->
    <a href="{{ route('admin.verifications.index', ['filtro' => 'rechazada']) }}" style="text-decoration: none;">
      <div class="card" style="border-radius: 14px; padding: 1.25rem; border-left: 4px solid #E74C3C; {{ $filter === 'rechazada' ? 'box-shadow: 0 0 0 2px #E74C3C;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 0.74rem; font-family: var(--font-mono); color: #C0392B; font-weight: 700; text-transform: uppercase;">Rechazadas</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #962D22; margin-top: 0.2rem;">{{ $counts['rechazada'] }}</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(231, 76, 60, 0.12); color: #E74C3C; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-circle-xmark"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Total -->
    <a href="{{ route('admin.verifications.index', ['filtro' => 'todos']) }}" style="text-decoration: none;">
      <div class="card" style="border-radius: 14px; padding: 1.25rem; border-left: 4px solid #556860; {{ $filter === 'todos' ? 'box-shadow: 0 0 0 2px #556860;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 0.74rem; font-family: var(--font-mono); color: #556860; font-weight: 700; text-transform: uppercase;">Total Histórico</div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #1A2620; margin-top: 0.2rem;">{{ $counts['todos'] }}</div>
          </div>
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(85, 104, 96, 0.12); color: #556860; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-layer-group"></i>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- FILTER PILLS -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="{{ route('admin.verifications.index', ['filtro' => 'pendiente']) }}" class="btn btn-sm {{ $filter === 'pendiente' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px; {{ $filter === 'pendiente' ? 'background: #F39C12; border-color: #F39C12;' : '' }}">
      <i class="fa-solid fa-clock"></i>
      <span>Pendientes ({{ $counts['pendiente'] }})</span>
    </a>
    <a href="{{ route('admin.verifications.index', ['filtro' => 'aprobada']) }}" class="btn btn-sm {{ $filter === 'aprobada' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px; {{ $filter === 'aprobada' ? 'background: #2E5D4B; border-color: #2E5D4B;' : '' }}">
      <i class="fa-solid fa-circle-check"></i>
      <span>Aprobadas ({{ $counts['aprobada'] }})</span>
    </a>
    <a href="{{ route('admin.verifications.index', ['filtro' => 'rechazada']) }}" class="btn btn-sm {{ $filter === 'rechazada' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px; {{ $filter === 'rechazada' ? 'background: #E74C3C; border-color: #E74C3C;' : '' }}">
      <i class="fa-solid fa-circle-xmark"></i>
      <span>Rechazadas ({{ $counts['rechazada'] }})</span>
    </a>
    <a href="{{ route('admin.verifications.index', ['filtro' => 'todos']) }}" class="btn btn-sm {{ $filter === 'todos' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
      <span>Todas las solicitudes</span>
    </a>
  </div>

  <!-- VERIFICATIONS LIST -->
  @if($verifications->isEmpty())
    <div class="card" style="text-align: center; padding: 4rem 1.5rem; border-radius: 16px;">
      <i class="fa-solid fa-clipboard-check" style="font-size: 3rem; color: #8EADA4; margin-bottom: 1rem;"></i>
      <h3 style="font-size: 1.25rem; color: #1A2620; margin: 0 0 0.5rem 0;">No hay solicitudes en esta sección</h3>
      <p style="color: #556860; font-size: 0.9rem; max-width: 460px; margin: 0 auto;">
        @if($filter === 'pendiente')
          No tienes ninguna cédula pendiente por auditar en este momento. Todas las solicitudes han sido resueltas.
        @else
          No se encontraron registros bajo el filtro seleccionado.
        @endif
      </p>
    </div>
  @else
    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2.5rem;">
      @foreach($verifications as $v)
        <div class="card" style="border-radius: 16px; overflow: hidden; border: 1.5px solid #DCE8E0;">
          <!-- Card Top Bar -->
          <div style="padding: 0.85rem 1.5rem; background: #F8FAF9; border-bottom: 1px solid #E8EFEA; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <span class="mono-tag" style="font-size: 0.72rem; color: #556860;">ID #{{ $v->id }}</span>
              <span style="color: #DCE8E0;">·</span>
              <span style="font-size: 0.78rem; color: #556860;">Enviado el {{ $v->created_at->format('d/m/Y \a \l\a\s H:i') }}</span>
            </div>

            <!-- STATUS BADGE -->
            <div>
              @if($v->status === 'pendiente')
                <span class="mono-tag" style="background: #FDE68A; color: #92400E; font-size: 0.72rem; font-weight: 700; padding: 3px 9px; border-radius: 9999px;">
                  <i class="fa-solid fa-hourglass-half"></i> PENDIENTE DE AUDITORÍA
                </span>
              @elseif($v->status === 'aprobada')
                <span class="mono-tag" style="background: #D1FAE5; color: #065F46; font-size: 0.72rem; font-weight: 700; padding: 3px 9px; border-radius: 9999px;">
                  <i class="fa-solid fa-circle-check"></i> ACREDITADO OFICIAL
                </span>
              @else
                <span class="mono-tag" style="background: #FEE2E2; color: #991B1B; font-size: 0.72rem; font-weight: 700; padding: 3px 9px; border-radius: 9999px;">
                  <i class="fa-solid fa-circle-xmark"></i> RECHAZADA
                </span>
              @endif
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
              
              <!-- Left: User & Identity info -->
              <div style="flex: 1; min-width: 280px;">
                <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1rem;">
                  <div style="width: 46px; height: 46px; border-radius: 50%; overflow: hidden; background: #2E5D4B; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;">
                    @if($v->user?->avatar_url)
                      <img src="{{ $v->user->avatar_url }}" alt="{{ $v->user->name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                    @else
                      {{ strtoupper(substr($v->user?->name ?? 'U', 0, 1)) }}
                    @endif
                  </div>
                  <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0;">
                      {{ $v->full_name }}
                    </h3>
                    <div style="font-size: 0.8rem; color: #556860; display: flex; align-items: center; gap: 6px;">
                      <span>Cuenta: <strong>{{ $v->user?->name }}</strong></span>
                      <span>·</span>
                      <span style="font-family: var(--font-mono);">{{ $v->user?->email }}</span>
                    </div>
                  </div>
                </div>

                <!-- Details Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: #F8FAF9; padding: 1rem 1.2rem; border-radius: 12px; border: 1px solid #E8EFEA;">
                  <div>
                    <span style="font-size: 0.72rem; font-family: var(--font-mono); color: #556860; text-transform: uppercase; font-weight: 700;">Cédula Profesional:</span>
                    <div style="font-size: 1.05rem; font-weight: 700; font-family: var(--font-mono); color: #1A2620; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                      <span>{{ $v->license_number }}</span>
                      <a href="https://www.cedulaprofesional.sep.gob.mx/" target="_blank" rel="noopener noreferrer" title="Cotejar en la SEP" style="font-size: 0.8rem; color: #0077CC;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </a>
                    </div>
                  </div>

                  <div>
                    <span style="font-size: 0.72rem; font-family: var(--font-mono); color: #556860; text-transform: uppercase; font-weight: 700;">Grado Escolar:</span>
                    <div style="font-size: 0.94rem; font-weight: 600; color: #2E5D4B; margin-top: 2px;">
                      {{ $v->education_level_label }}
                    </div>
                  </div>

                  <div>
                    <span style="font-size: 0.72rem; font-family: var(--font-mono); color: #556860; text-transform: uppercase; font-weight: 700;">Documento Probatorio:</span>
                    <div style="margin-top: 4px;">
                      @if($v->document_path)
                        <a href="{{ route('admin.verifications.document', $v) }}" target="_blank" class="btn btn-sm btn-secondary" style="font-size: 0.78rem; padding: 0.3rem 0.7rem; gap: 6px; border-radius: 7px; color: #2E5D4B;">
                          <i class="fa-solid fa-file-pdf"></i>
                          <span>Ver Comprobante</span>
                        </a>
                      @else
                        <span style="font-size: 0.8rem; color: #8EADA4; font-style: italic;">Sin archivo adjunto</span>
                      @endif
                    </div>
                  </div>
                </div>

                @if($v->admin_notes)
                  <div style="margin-top: 0.85rem; padding: 0.75rem 1rem; background: #FFF5F5; border-radius: 8px; border: 1px solid #FEB2B2; font-size: 0.82rem; color: #9B2C2C;">
                    <strong>Nota de auditoría:</strong> {{ $v->admin_notes }}
                  </div>
                @endif

                @if($v->reviewed_by && $v->reviewer)
                  <div style="margin-top: 0.65rem; font-size: 0.75rem; color: #556860;">
                    Dictaminado por: <strong>{{ $v->reviewer->name }}</strong> el {{ $v->reviewed_at?->format('d/m/Y H:i') }}
                  </div>
                @endif
              </div>

              <!-- Right: Actions (Icon-only buttons) -->
              <div style="display: flex; flex-direction: column; gap: 0.65rem; align-items: flex-end;">
                @if($v->status === 'pendiente')
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- APROBAR (Icon-only) -->
                    <form method="POST" action="{{ route('admin.verification.approve', $v) }}" style="margin: 0;" onsubmit="return confirm('¿Confirmas que la cédula y credenciales de {{ $v->full_name }} han sido validadas oficialmente? El usuario recibirá la insignia y el rol de Profesional.');">
                      @csrf
                      <button type="submit" class="btn btn-primary" title="Aprobar y Acreditar Oficialmente" style="width: 40px; height: 40px; padding: 0; border-radius: 10px; background: #2E5D4B; border-color: #2E5D4B; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(46,93,75,0.25); transition: all 0.2s ease;">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    </form>

                    <!-- RECHAZAR (Icon-only) -->
                    <button type="button" onclick="toggleRejectBox({{ $v->id }})" title="Rechazar Solicitud" class="btn" style="width: 40px; height: 40px; padding: 0; border-radius: 10px; background: rgba(192, 57, 43, 0.1); color: #C0392B; border: 1.5px solid rgba(192, 57, 43, 0.3); display: flex; align-items: center; justify-content: center; font-size: 1.05rem; transition: all 0.2s ease;">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>

                  <!-- REJECT FORM DRAWER -->
                  <div id="rejectBox-{{ $v->id }}" style="display: none; width: 100%; margin-top: 0.5rem; background: #FFF5F5; border: 1px solid #FEB2B2; border-radius: 10px; padding: 0.85rem;">
                    <form method="POST" action="{{ route('admin.verification.reject', $v) }}">
                      @csrf
                      <label style="font-size: 0.76rem; font-weight: 700; color: #9B2C2C; display: block; margin-bottom: 0.35rem;">
                        Motivo del rechazo (visible para el usuario):
                      </label>
                      <textarea name="admin_notes" rows="2" class="form-control" style="font-size: 0.8rem; padding: 0.4rem 0.6rem; margin-bottom: 0.5rem;" placeholder="Ej. El número de cédula no se encuentra en el registro o el nombre no coincide." required></textarea>
                      <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="button" onclick="toggleRejectBox({{ $v->id }})" class="btn btn-sm btn-secondary" style="font-size: 0.74rem; padding: 0.25rem 0.55rem;">Cancelar</button>
                        <button type="submit" class="btn btn-sm" style="background: #C0392B; color: #FFFFFF; font-size: 0.74rem; padding: 0.25rem 0.65rem;">Confirmar Rechazo</button>
                      </div>
                    </form>
                  </div>
                @else
                  <div style="font-size: 0.8rem; color: #8EADA4; font-style: italic; text-align: right;">
                    Caso dictaminado
                  </div>
                @endif
              </div>

            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div style="margin-top: 2rem;">
      {{ $verifications->links() }}
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
  function toggleRejectBox(id) {
    const box = document.getElementById('rejectBox-' + id);
    if (box) {
      box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
  }
</script>
@endpush
