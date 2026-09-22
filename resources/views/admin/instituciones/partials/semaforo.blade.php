{{-- $s = ['nivel' => ..., 'etiqueta' => ...] de ExpedienteClinicoService::semaforo() --}}
@php
  $clase = [
    'ROJO_AGUDO' => 'sem-rojo-agudo', 'ROJO' => 'sem-rojo', 'NARANJA' => 'sem-naranja',
    'AMARILLO' => 'sem-amarillo', 'VERDE' => 'sem-verde',
  ][$s['nivel']] ?? null;
@endphp
@if($clase)
  <span class="sem {{ $clase }}" @isset($estilo) style="{{ $estilo }}" @endisset><span class="dot"></span> {{ $s['etiqueta'] }}</span>
@elseif($s['nivel'] === 'SILENCIO')
  <span class="chip"><i class="fa-solid fa-volume-xmark"></i> {{ $s['etiqueta'] }}</span>
@else
  <span class="chip">{{ $s['etiqueta'] }}</span>
@endif
