@extends('layouts.admin')

@section('title', 'Cola de atención')

@push('styles')
  @include('admin.instituciones.partials.estilos')
  <style>
    .caso { border: 1.5px solid #DCE8E0; border-radius: 14px; padding: 1rem 1.15rem; background: #FFFFFF; margin-bottom: 0.85rem; }
    .caso.agudo { border-color: #E9B7B0; box-shadow: inset 4px 0 0 #6E140C; }
    .caso.rojo { box-shadow: inset 4px 0 0 #B02418; }
    .caso-cab { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
    .caso-meta { display: flex; gap: 1.25rem; flex-wrap: wrap; font-size: 0.8rem; color: #556860; margin-top: 0.6rem; }
    .caso-meta b { color: #1A2620; }
    .caso details { margin-top: 0.75rem; }
    .caso summary { cursor: pointer; font-size: 0.82rem; font-weight: 700; color: #2E5D4B; }
    .caso form { margin-top: 0.6rem; display: grid; gap: 0.5rem; max-width: 560px; }
    .espera-larga { color: #B02418; font-weight: 700; }
  </style>
@endpush

@section('content')
<div class="admin-content-canvas">
  <div style="margin-bottom: 1.25rem;">
    <div class="crumbs-atl">Consola A Tu Lado &rsaquo; <b>Cola de atención</b></div>
    <h1 class="titulo-atl">Cola de atención</h1>
    <p class="sub-atl">Casos de crisis abiertos, del más grave al más reciente. <b>No se envían avisos automáticos:</b> revisen esta pantalla con la frecuencia que acordó el equipo.</p>
  </div>

  @unless($esClinico)
    <div class="nota-protocolo" style="margin-bottom: 1rem;">
      <i class="fa-solid fa-user-lock"></i>
      <span>Ves los casos por folio. Nombres, contactos y acciones son sólo para cuentas con acreditación clínica.</span>
    </div>
  @endunless

  @forelse($abiertos as $caso)
    @php
      $codigo = \App\Services\ExpedienteClinicoService::codigoCaso($caso);
      $agudo = $caso->nivel === 'ROJO_AGUDO';
      $minutos = $caso->minutosHastaContacto();
      $membresia = $membresias->get($caso->user_id . '-' . $caso->institucion_id);
      $limite = $agudo ? (int) config('clinical.asq.tiempo_contacto_agudo_min', 5) : 24 * 60;
    @endphp
    <div class="caso {{ $agudo ? 'agudo' : 'rojo' }}">
      <div class="caso-cab">
        <div>
          <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <span class="mono" style="font-family: 'IBM Plex Mono', monospace; font-weight: 700;">{{ $codigo }}</span>
            @include('admin.instituciones.partials.semaforo', ['s' => ['nivel' => $agudo ? 'ROJO_AGUDO' : 'ROJO', 'etiqueta' => $agudo ? 'Rojo agudo' : 'Rojo']])
            <span class="chip-atl {{ $caso->contactado_en ? '' : 'rojo' }}">{{ $caso->contactado_en ? 'En atención' : 'Sin contacto' }}</span>
          </div>
          <div style="margin-top: 0.4rem; font-weight: 700; color: #1A2620;">
            @if($esClinico)
              {{ $caso->user?->name }}
            @else
              {{ $membresia?->folio ?? 'Usuario #' . $caso->user_id }}
            @endif
            <span style="font-weight: 400; color: #6E887E;">· {{ $caso->institucion?->nombre_corto ?? 'Usuario independiente' }}</span>
          </div>
        </div>
        @if($esClinico && $membresia && $caso->institucion)
          <a class="btn-atl linea sm" href="{{ route('admin.instituciones.show', ['institucion' => $caso->institucion, 'q' => $membresia->folio]) }}#colaboradores">
            <i class="fa-solid fa-folder-open"></i> Ir a la ficha
          </a>
        @endif
      </div>

      <div class="caso-meta">
        <span>Disparado: <b>{{ $caso->disparado_en?->format('d/m/Y H:i') }}</b> · origen {{ strtoupper($caso->origen ?? 'motor') }}</span>
        @if($caso->contactado_en)
          <span>Contacto: <b>{{ $caso->contactado_en->format('d/m/Y H:i') }}</b> ({{ $minutos }} min){{ $caso->contactadoPor ? ' · ' . $caso->contactadoPor->name : '' }}</span>
        @else
          <span class="{{ $minutos > $limite ? 'espera-larga' : '' }}">Esperando: <b>{{ $minutos < 120 ? $minutos . ' min' : round($minutos / 60) . ' h' }}</b></span>
        @endif
        @if($caso->estoy_con_alguien)<span><i class="fa-solid fa-user-group"></i> Indicó que está acompañado(a)</span>@endif
        @if($caso->salida_sin_contacto)<span class="espera-larga"><i class="fa-solid fa-door-open"></i> Salió sin pedir ayuda</span>@endif
      </div>

      @if($esClinico)
        @if($contactos = $caso->user?->contactosEmergencia)
          <div class="caso-meta">
            @forelse($contactos as $c)
              <span><i class="fa-solid fa-phone"></i> {{ $c->nombre }}{{ $c->relacion ? ' (' . $c->relacion . ')' : '' }}: <b style="font-family: 'IBM Plex Mono', monospace;">{{ $c->telefono }}</b></span>
            @empty
              <span>Sin contacto de emergencia registrado · correo: <b>{{ $caso->user?->email }}</b></span>
            @endforelse
          </div>
        @endif

        @if(!$caso->contactado_en)
          <details>
            <summary><i class="fa-solid fa-phone"></i> Registrar contacto humano</summary>
            <form method="POST" action="{{ route('admin.cola.contacto', $caso) }}">
              @csrf
              <textarea class="form-input-styled" name="nota" rows="2" maxlength="1000" placeholder="Cómo fue el contacto (opcional)"></textarea>
              <div><button class="btn-atl primario sm" type="submit"><i class="fa-solid fa-check"></i> Registrar contacto</button></div>
            </form>
          </details>
        @else
          <details>
            <summary><i class="fa-solid fa-lock"></i> Cerrar caso</summary>
            <form method="POST" action="{{ route('admin.cola.cerrar', $caso) }}">
              @csrf
              <textarea class="form-input-styled" name="notas" rows="3" minlength="10" maxlength="2000" required placeholder="Cómo se verificó que la persona está a salvo y qué seguimiento queda"></textarea>
              <div><button class="btn-atl primario sm" type="submit"><i class="fa-solid fa-lock"></i> Cerrar con contacto verificado</button></div>
            </form>
          </details>
        @endif
      @endif
    </div>
  @empty
    <div class="card-atl" style="text-align: center; color: #6E887E; padding: 2rem;">
      <i class="fa-solid fa-circle-check" style="font-size: 1.6rem; color: #1E8449; display: block; margin-bottom: 0.5rem;"></i>
      No hay casos abiertos.
    </div>
  @endforelse

  @if($cerrados->isNotEmpty())
    <div class="card-atl" style="margin-top: 1.5rem;">
      <div class="card-atl-head"><div><h3>Cerrados recientemente</h3></div></div>
      <table class="padron-tabla">
        <thead><tr><th>Caso</th><th>Institución</th><th>Disparado</th><th>Cerrado por</th></tr></thead>
        <tbody>
          @foreach($cerrados as $caso)
            <tr>
              <td class="mono">{{ \App\Services\ExpedienteClinicoService::codigoCaso($caso) }}</td>
              <td>{{ $caso->institucion?->nombre_corto ?? 'Independiente' }}</td>
              <td>{{ $caso->disparado_en?->format('d/m/Y H:i') }}</td>
              <td>{{ $caso->verificadoPor?->name ?? '—' }} · {{ $caso->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
