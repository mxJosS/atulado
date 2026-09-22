{{-- Invitaciones por correo a las personas del padrón --}}
@php
  $invitables = $personas->filter(fn ($p) => \App\Services\InvitacionService::invitable($p));
  $pendientes = $invitaciones['enviadas'] + $invitaciones['sin_enviar'];
@endphp

<div class="card-atl" style="margin-bottom: 1.5rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #EEF4F0;">
    <div>
      <h3 style="font-family: 'Fraunces', serif; font-size: 1.12rem; margin: 0;">Invitaciones por correo</h3>
      <p style="font-size: 0.8rem; color: #6E887E; margin: 0.2rem 0 0;">
        Cada persona recibe un enlace personal para crear su contraseña. Vence en {{ \App\Services\InvitacionService::DIAS_VIGENCIA }} días y sirve una sola vez; reenviar genera uno nuevo.
      </p>
      <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 0.6rem;">
        <span class="chip-atl">{{ $invitaciones['activas'] }} activaron su cuenta</span>
        <span class="chip-atl gris">{{ $invitaciones['enviadas'] }} invitadas sin activar</span>
        <span class="chip-atl ambar">{{ $invitaciones['sin_enviar'] }} sin invitación</span>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.instituciones.invitaciones.enviar', $institucion) }}"
          onsubmit="return confirm('¿Enviar la invitación a las {{ $pendientes }} personas que aún no activan su cuenta?')">
      @csrf
      <input type="hidden" name="alcance" value="todos">
      <button type="submit" class="btn-atl primario" style="padding: 0.9rem 1.5rem; font-size: 0.95rem;" @disabled($pendientes === 0)>
        <i class="fa-solid fa-paper-plane"></i> Enviar invitación a todos ({{ $pendientes }})
      </button>
    </form>
  </div>

  @if($invitaciones['sin_enviar'] > 0 && $invitaciones['enviadas'] > 0)
    <form method="POST" action="{{ route('admin.instituciones.invitaciones.enviar', $institucion) }}" style="margin-bottom: 0.9rem;">
      @csrf
      <input type="hidden" name="alcance" value="nuevos">
      <button type="submit" class="btn-atl suave sm"><i class="fa-solid fa-envelope"></i> Enviar sólo a quienes nunca la recibieron ({{ $invitaciones['sin_enviar'] }})</button>
    </form>
  @endif

  @if($personas->isEmpty())
    <p style="color: #6E887E; font-size: 0.88rem; margin: 0;">Primero agrega personas en «Padrón de Colaboradores».</p>
  @else
    <form method="POST" action="{{ route('admin.instituciones.invitaciones.enviar', $institucion) }}" id="form-invitar">
      @csrf
      <input type="hidden" name="alcance" value="seleccion">
      <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.75rem;">
        <input type="search" class="form-input-styled" placeholder="Buscar persona…" style="flex: 1; min-width: 200px;" oninput="filtrarTabla(this, 'tabla-invitaciones')">
        <button type="submit" class="btn-atl linea sm" id="btn-invitar-seleccion" disabled>
          <i class="fa-solid fa-paper-plane"></i> Enviar a seleccionadas (<span id="n-seleccion">0</span>)
        </button>
      </div>
      <div style="overflow-x: auto; max-height: 560px; overflow-y: auto; border: 1px solid #EEF4F0; border-radius: 10px;">
        <table class="padron-tabla" id="tabla-invitaciones">
          <thead>
            <tr>
              <th style="width: 32px;"><input type="checkbox" aria-label="Seleccionar todas" onchange="document.querySelectorAll('.chk-invitar').forEach(c => { if (c.closest('tr').style.display !== 'none') c.checked = this.checked; }); contarSeleccion();"></th>
              <th>Persona</th><th>Área</th><th>Estado</th><th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($personas->where('estado', '!=', 'baja') as $p)
              @php $puede = $invitables->contains('id', $p->id); @endphp
              <tr>
                <td>@if($puede)<input type="checkbox" class="chk-invitar" name="personas[]" value="{{ $p->id }}" onchange="contarSeleccion()">@endif</td>
                <td><b>{{ $p->user?->name }}</b><div style="font-size: 0.74rem; color: #6E887E;">{{ $p->user?->email }}</div></td>
                <td>{{ $p->departamento?->nombre ?: '—' }}</td>
                <td>
                  @if($p->estado === 'activo')
                    <span class="chip-atl"><i class="fa-solid fa-circle-check"></i> Activó {{ $p->activado_en?->format('d/m/Y') }}</span>
                  @elseif($p->invitado_en)
                    <span class="chip-atl gris">Enviada {{ $p->invitado_en->format('d/m/Y H:i') }}</span>
                  @else
                    <span class="chip-atl ambar">Sin invitación</span>
                  @endif
                </td>
                <td style="text-align: right;">
                  @if($puede)
                    <button type="submit" class="btn-atl linea sm" name="solo" value="{{ $p->id }}">
                      <i class="fa-solid fa-paper-plane"></i> {{ $p->invitado_en ? 'Reenviar' : 'Enviar' }}
                    </button>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </form>
  @endif
</div>
