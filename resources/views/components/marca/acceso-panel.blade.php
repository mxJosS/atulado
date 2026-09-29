{{--
  Panel oscuro de las pantallas de acceso (login, registro, invitaciones…):
  aurora, el árbol del logo flotando, una frase y el acceso directo a crisis.
  El contenido (título y texto) va en el slot. Estilos: css/marca.css (sección 7).
--}}
@props(['eyebrow' => null])
<aside {{ $attributes->class(['acceso-panel']) }}>
  <div class="m-fondo" aria-hidden="true">
    <span class="m-aurora m-aurora-1"></span>
    <span class="m-aurora m-aurora-2"></span>
    <span class="m-aurora m-aurora-3"></span>
  </div>
  <img src="{{ asset('images/pixel/destello-1.png') }}" alt="" class="m-hero-deco" data-destello style="left: 12%; top: 46%; width: 18px;">
  <img src="{{ asset('images/pixel/destello-3.png') }}" alt="" class="m-hero-deco" data-destello style="right: 14%; top: 40%; width: 15px;">
  <img src="{{ asset('images/pixel/hojas-cayendo.png') }}" alt="" class="m-hero-deco" data-mecer style="right: 10%; top: 62%; width: 24px;">

  <div class="acceso-panel-texto">
    @if($eyebrow)
      <span class="m-eyebrow" data-aparecer><span class="m-punto-vivo" aria-hidden="true"></span> {{ $eyebrow }}</span>
    @endif
    <div data-aparecer>{{ $slot }}</div>
  </div>

  <div class="acceso-panel-arbol" data-aparecer="suave">
    <x-logo :size="230" data-flotar alt="" />
  </div>

  {{-- Crisis: visible desde el primer instante, sin animación de entrada --}}
  <a href="tel:8009112000" class="acceso-crisis" title="Llamar a Línea de la Vida">
    <span class="acceso-crisis-icono"><i class="fa-solid fa-phone"></i></span>
    <span><strong>800 911 2000</strong><br>¿Estás en crisis? Línea de la Vida · gratuita, 24 h</span>
  </a>
</aside>
