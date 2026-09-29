@extends('layouts.guest')

@section('title', 'Página no encontrada — A tu lado')

@section('content')
<x-marca.hero eyebrow="Error 404" icono="fa-solid fa-compass" style="min-height: 100svh; display: flex; align-items: center; justify-content: center;">
  <div data-aparecer style="display: flex; justify-content: center;">
    <x-logo :size="140" data-flotar style="filter: drop-shadow(0 18px 26px rgba(0, 0, 0, 0.5));" />
  </div>
  <h1 class="m-titulo" data-aparecer="palabras">No encontramos <em>esta página</em></h1>
  <p class="m-bajada" data-aparecer>El recurso que buscas puede haber sido movido o ya no estar disponible. Te acompañamos de regreso a un lugar seguro.</p>
  <div class="m-acciones" data-aparecer-grupo style="margin-top: 2rem;">
    <a href="{{ route('home') }}" class="m-btn m-btn-menta"><i class="fa-solid fa-house"></i><span>Ir al Inicio</span></a>
    <a href="{{ route('crisis') }}" class="m-btn m-btn-vidrio"><i class="fa-solid fa-life-ring"></i><span>Líneas de Crisis</span></a>
  </div>
</x-marca.hero>
@endsection
