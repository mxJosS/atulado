@extends('layouts.guest')

@section('title', 'Acceso Restringido — A tu lado')

@section('content')
<x-marca.hero eyebrow="Acceso restringido (403)" icono="fa-solid fa-shield-halved" style="min-height: 100svh; display: flex; align-items: center; justify-content: center;">
  <div data-aparecer style="display: flex; justify-content: center;">
    <x-logo :size="140" data-flotar style="filter: drop-shadow(0 18px 26px rgba(0, 0, 0, 0.5));" />
  </div>
  <h1 class="m-titulo" data-aparecer="palabras">Área <em>protegida</em></h1>
  <p class="m-bajada" data-aparecer>Esta sección requiere permisos administrativos o de acreditación clínica específica para ingresar.</p>
  <div class="m-acciones" data-aparecer-grupo style="margin-top: 2rem;">
    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="m-btn m-btn-menta"><i class="fa-solid fa-table-cells-large"></i><span>Volver a Mi Espacio</span></a>
    <a href="{{ route('home') }}" class="m-btn m-btn-vidrio"><i class="fa-solid fa-house"></i><span>Inicio</span></a>
  </div>
</x-marca.hero>
@endsection
