{{-- Invitar por link: un enlace para compartir; quien lo abre crea su cuenta y queda en el padrón.
     Un clínico con permiso lo genera y lo comparte; área, estado y reemplazo son de la administración. --}}
@php
  $url = $institucion->enlace_token ? route('unirse.mostrar', $institucion->enlace_token) : null;
  $porEnlace = $personas->where('origen', 'enlace')->where('estado', '!=', 'baja')->count();
@endphp

<div class="card-atl" style="margin-bottom: 1.5rem;">
  <div style="padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #EEF4F0;">
    <h3 style="font-family: 'Fraunces', serif; font-size: 1.12rem; margin: 0;">Invitar por link</h3>
    <p style="font-size: 0.8rem; color: #5B7369; margin: 0.2rem 0 0; max-width: 760px;">
      Comparte este enlace por WhatsApp, correo o en un cartel. Quien lo abre crea su cuenta <b>con Google o con su correo</b>, a su elección,
      y queda automáticamente en el padrón de {{ $institucion->nombre_corto }}. En esta página el botón de Google <b>siempre</b> está disponible,
      aunque el acceso con Google esté apagado en el panel.
    </p>
    @if($url)
      <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 0.6rem;">
        <span @class(['chip-atl', 'gris' => !$institucion->enlace_activo])>{{ $institucion->enlace_activo ? 'Activo' : 'Desactivado' }}</span>
        <span class="chip-atl gris">{{ $porEnlace }} {{ $porEnlace === 1 ? 'persona se unió' : 'personas se unieron' }} con el link</span>
        @if($institucion->enlace_generado_en)
          <span class="chip-atl gris">Creado el {{ $institucion->enlace_generado_en->format('d/m/Y H:i') }}</span>
        @endif
      </div>
    @endif
  </div>

  @if(!$url)
    <form method="POST" action="{{ route('admin.instituciones.enlace.generar', $institucion) }}">
      @csrf
      <button type="submit" class="btn-atl primario" @disabled($institucion->estaArchivada())><i class="fa-solid fa-link"></i> Generar link de invitación</button>
    </form>
  @else
    <label class="form-group-label" for="enlace-url">Link para compartir</label>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.1rem;">
      <input type="text" id="enlace-url" class="form-input-styled" value="{{ $url }}" readonly style="flex: 1; min-width: 260px; font-family: 'IBM Plex Mono', monospace; font-size: 0.8rem;" onclick="this.select()">
      <button type="button" class="btn-atl primario" id="enlace-copiar"
              onclick="navigator.clipboard.writeText(document.getElementById('enlace-url').value).then(() => { this.textContent = 'Copiado'; setTimeout(() => this.textContent = 'Copiar link', 2000); })">Copiar link</button>
      <a href="https://wa.me/?text={{ rawurlencode('Te invitamos a A tu lado, el espacio de acompañamiento emocional de ' . $institucion->nombre_corto . ': ' . $url) }}"
         target="_blank" rel="noopener" class="btn-atl linea"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
    </div>

    @if($esAdmin)
    <form method="POST" action="{{ route('admin.instituciones.enlace.actualizar', $institucion) }}"
          style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.9rem; align-items: end; margin-bottom: 1rem;">
      @csrf
      @method('PUT')
      <div>
        <label class="form-group-label" for="enlace-departamento">Área a la que entra quien se une</label>
        <select name="departamento_id" id="enlace-departamento" class="form-input-styled">
          <option value="">Sin área (la asignas después en el padrón)</option>
          @foreach($opcionesArea as [$idArea, $nombreArea])
            <option value="{{ $idArea }}" @selected((int) $institucion->enlace_departamento_id === (int) $idArea)>{{ $nombreArea }}</option>
          @endforeach
        </select>
        @error('departamento_id')<span class="form-error">{{ $message }}</span>@enderror
      </div>
      <div>
        <label class="form-group-label" for="enlace-activo">Estado del link</label>
        <select name="activo" id="enlace-activo" class="form-input-styled">
          <option value="1" @selected($institucion->enlace_activo)>Activo: se puede usar</option>
          <option value="0" @selected(!$institucion->enlace_activo)>Desactivado: nadie puede unirse con él</option>
        </select>
      </div>
      <div>
        <button type="submit" class="btn-atl primario"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      </div>
    </form>

    <form method="POST" action="{{ route('admin.instituciones.enlace.generar', $institucion) }}"
          onsubmit="return confirm('Se creará un link nuevo y el actual dejará de funcionar para quien no lo haya usado. ¿Continuar?')">
      @csrf
      <button type="submit" class="btn-atl linea sm" style="color: #B02418;"><i class="fa-solid fa-rotate"></i> Cambiar por un link nuevo</button>
      <span style="font-size: 0.76rem; color: #5B7369; margin-left: 0.4rem;">Úsalo si el link se compartió donde no debía.</span>
    </form>
    @elseif(!$institucion->enlace_activo)
      <div class="nota-protocolo" style="margin-bottom: 0.4rem;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Este link está desactivado: nadie puede unirse con él. Pide a la administración de A tu lado que lo active.</span>
      </div>
    @else
      <p style="font-size: 0.78rem; color: #5B7369; margin: 0;">El área de entrada, el estado del link y cambiarlo por uno nuevo los ajusta la administración de A tu lado.</p>
    @endif
  @endif

  <div class="nota-protocolo" style="margin-top: 1.1rem;">
    <i class="fa-solid fa-circle-info"></i>
    <span>Quien entra por el link aparece en el padrón con su nombre y correo, sin número de empleado ni puesto: complétalos en «Padrón de Colaboradores» si los necesitas.
      Una persona que ya pertenece a otra institución no se cambia sola; las cuentas de administración y del equipo clínico no se unen al padrón.</span>
  </div>
</div>
