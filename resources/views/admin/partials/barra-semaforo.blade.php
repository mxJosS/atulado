{{--
  Barra del semáforo sobre el padrón activo.
  $conteos: nivel → personas (incluye SIN_CLASIFICAR); $base: total de la barra.
  Opcional: $alto (px, 8 por defecto); $grande = true para la barra ancha con porcentajes.
--}}
@php
  $coloresBarra = [
      'VERDE' => 'var(--sem-verde)', 'AMARILLO' => 'var(--sem-amarillo)', 'NARANJA' => 'var(--sem-naranja)',
      'ROJO' => 'var(--sem-rojo)', 'ROJO_AGUDO' => 'var(--sem-rojo-agudo)', 'SIN_CLASIFICAR' => '#C9D6CF',
  ];
  $etiquetasBarra = ['VERDE' => 'Verde', 'AMARILLO' => 'Amarillo', 'NARANJA' => 'Naranja', 'ROJO' => 'Rojo', 'ROJO_AGUDO' => 'Rojo agudo', 'SIN_CLASIFICAR' => 'Sin clasificar'];
  $resumenBarra = collect($conteos)->filter()->map(fn ($n, $nivel) => $etiquetasBarra[$nivel] . ' ' . $n)->implode(' · ');
  $grande = $grande ?? false;
@endphp
<div class="{{ $grande ? 'dist-bar' : 'bar-track' }}" @unless($grande) style="height: {{ $alto ?? 8 }}px;" @endunless
     title="{{ $base > 0 ? $base . ($base === 1 ? ' persona' : ' personas') . ': ' . $resumenBarra : 'Aún sin cuentas activas' }}">
  @if($base > 0)
    @foreach($coloresBarra as $nivel => $color)
      @continue(($conteos[$nivel] ?? 0) === 0)
      @php $pct = round($conteos[$nivel] / $base * 100, 1); @endphp
      <div @class(['seg', 'light-text' => $grande && in_array($nivel, ['AMARILLO', 'SIN_CLASIFICAR'], true)]) style="background: {{ $color }}; width: {{ $pct }}%;"
           title="{{ $etiquetasBarra[$nivel] }}: {{ $conteos[$nivel] }} ({{ $pct }}%)">@if($grande){{ $pct >= 14 ? round($pct) . '% ' . $etiquetasBarra[$nivel] : ($pct >= 5 ? round($pct) . '%' : '') }}@endif</div>
    @endforeach
  @else
    <div class="seg" style="background: var(--bg-subtle); width: 100%;"></div>
  @endif
</div>
