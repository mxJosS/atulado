@extends('layouts.guest')

@section('title', 'Enlace no válido — A tu lado')

@section('content')
<x-marca.hero eyebrow="Invitación" icono="fa-solid fa-link-slash" style="min-height: 100svh; display: flex; align-items: center; justify-content: center;">
  <div data-aparecer style="display: flex; justify-content: center;">
    <x-logo :size="130" data-flotar style="filter: drop-shadow(0 18px 26px rgba(0, 0, 0, 0.5));" />
  </div>
  <h1 class="m-titulo" data-aparecer="palabras">Este enlace ya no funciona</h1>
  <p class="m-bajada" data-aparecer>
    Puede que tu institución lo haya cambiado o desactivado. Pide el enlace nuevo a quien te lo compartió.
    Mientras tanto puedes crear tu cuenta por tu cuenta o entrar si ya tienes una.
  </p>
  <div class="m-acciones" data-aparecer-grupo style="margin-top: 2rem;">
    <a href="{{ route('login') }}" class="m-btn m-btn-menta"><i class="fa-solid fa-arrow-right-to-bracket"></i><span>Iniciar sesión</span></a>
    <a href="{{ route('home') }}" class="m-btn m-btn-vidrio"><i class="fa-solid fa-house"></i><span>Ir al inicio</span></a>
  </div>
</x-marca.hero>
@endsection
