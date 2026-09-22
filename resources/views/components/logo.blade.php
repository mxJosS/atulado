{{--
  Logo oficial de A Tu Lado (árbol de píxeles con flores).
  Uso: <x-logo :size="32" />   ·   las imágenes están en public/images/marca/
--}}
@props(['size' => 32, 'alt' => 'A tu lado'])
@php
  $archivos = [32, 64, 128, 256, 512];
  $base = collect($archivos)->first(fn ($n) => $n >= $size) ?? 512;
  $doble = collect($archivos)->first(fn ($n) => $n >= $size * 2) ?? 512;
@endphp
<img src="{{ asset("images/marca/logo-atulado-{$base}.png") }}"
     srcset="{{ asset("images/marca/logo-atulado-{$base}.png") }} 1x, {{ asset("images/marca/logo-atulado-{$doble}.png") }} 2x"
     width="{{ $size }}" height="{{ $size }}" alt="{{ $alt }}" decoding="async"
     {{ $attributes->merge(['class' => 'logo-atulado', 'style' => 'display:inline-block; flex-shrink:0; object-fit:contain;']) }}>
