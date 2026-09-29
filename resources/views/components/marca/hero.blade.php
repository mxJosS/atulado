{{--
  Portada pública a todo lo ancho (el lenguaje de Inicio): noche con aurora,
  retícula, destellos pixel art y el contenido centrado.
  Uso:
    <x-marca.hero eyebrow="Un momento para ti">
      <h1 class="m-titulo" data-aparecer="palabras">¿Cómo te sientes <em>hoy?</em></h1>
      <p class="m-bajada" data-aparecer>…</p>
    </x-marca.hero>
  :crisis="true" tiñe la aurora de rojo (líneas de ayuda). Estilos: css/marca.css.
--}}
@props(['eyebrow' => null, 'icono' => null, 'crisis' => false, 'deco' => true])
<section {{ $attributes->class(['m-hero', 'm-hero-crisis' => $crisis]) }}>
  <div class="m-fondo" aria-hidden="true">
    <span @class(['m-aurora', 'm-aurora-1', 'm-aurora-crisis' => $crisis])></span>
    <span class="m-aurora m-aurora-2"></span>
    <span class="m-aurora m-aurora-3"></span>
  </div>
  @if($deco)
    <img src="{{ asset('images/pixel/destello-1.png') }}" alt="" class="m-hero-deco" data-destello style="left: 11%; top: 36%; width: 20px;">
    <img src="{{ asset('images/pixel/destello-3.png') }}" alt="" class="m-hero-deco" data-destello style="right: 12%; top: 30%; width: 16px;">
    <img src="{{ asset('images/pixel/destello-4.png') }}" alt="" class="m-hero-deco" data-destello style="left: 19%; bottom: 20%; width: 14px;">
    <img src="{{ asset('images/pixel/hojas-cayendo.png') }}" alt="" class="m-hero-deco" data-mecer style="right: 8%; bottom: 24%; width: 26px;">
  @endif
  <div class="m-hero-contenido">
    @if($eyebrow)
      <span @class(['m-eyebrow', 'm-eyebrow-crisis' => $crisis]) data-aparecer>
        @if($icono)<i class="{{ $icono }}" aria-hidden="true"></i>@else<span class="m-punto-vivo" aria-hidden="true"></span>@endif
        {{ $eyebrow }}
      </span>
    @endif
    {{ $slot }}
  </div>
</section>
