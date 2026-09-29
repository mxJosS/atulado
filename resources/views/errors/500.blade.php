@extends('layouts.guest')

@section('title', 'Error del Sistema — A tu lado')

@section('content')
<x-marca.hero eyebrow="Estado 500" icono="fa-solid fa-triangle-exclamation" style="min-height: 100svh; display: flex; align-items: center; justify-content: center;">
  <div data-aparecer style="display: flex; justify-content: center;">
    <x-logo :size="140" data-flotar style="filter: drop-shadow(0 18px 26px rgba(0, 0, 0, 0.5));" />
  </div>
  <h1 class="m-titulo" data-aparecer="palabras">Algo no salió <em>como esperábamos</em></h1>
  <p class="m-bajada" data-aparecer>Hemos registrado este incidente para resolverlo a la brevedad. Tu bienestar es nuestra prioridad; si te encuentras en un momento difícil, puedes consultar las líneas de ayuda directa.</p>
  <div class="m-acciones" data-aparecer-grupo style="margin-top: 2rem;">
    <a href="{{ route('home') }}" class="m-btn m-btn-menta"><i class="fa-solid fa-house"></i><span>Volver al Inicio</span></a>
    <a href="{{ route('crisis') }}" class="m-btn m-btn-vidrio"><i class="fa-solid fa-phone-volume"></i><span>Líneas 24/7</span></a>
  </div>
</x-marca.hero>
@endsection
