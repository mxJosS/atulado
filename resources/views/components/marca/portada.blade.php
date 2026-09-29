{{--
  Portada de pantalla para tu espacio y el panel: tarjeta noche redondeada con
  aurora (el lenguaje de Inicio), eyebrow, título y acciones a la derecha.
  Uso:
    <x-marca.portada eyebrow="Mi espacio" icono="fa-solid fa-chart-line">
      <h1 class="m-portada-titulo" data-aparecer="palabras">Historial y <em>racha</em></h1>
      <p class="m-portada-texto">…</p>
      <x-slot:acciones> <a class="m-btn m-btn-menta m-btn-chico" …>…</a> </x-slot:acciones>
    </x-marca.portada>
  Estilos: css/marca.css (sección 5).
--}}
@props(['eyebrow' => null, 'icono' => null])
<header {{ $attributes->class(['m-portada']) }} data-aparecer>
  <div class="m-fondo" aria-hidden="true">
    <span class="m-aurora m-aurora-1"></span>
    <span class="m-aurora m-aurora-2"></span>
  </div>
  <div class="m-portada-contenido">
    @if($eyebrow)
      <span class="m-eyebrow">
        @if($icono)<i class="{{ $icono }}" aria-hidden="true"></i>@else<span class="m-punto-vivo" aria-hidden="true"></span>@endif
        {{ $eyebrow }}
      </span>
    @endif
    {{ $slot }}
  </div>
  @isset($acciones)
    <div class="m-portada-acciones">{{ $acciones }}</div>
  @endisset
</header>
