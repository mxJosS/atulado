@extends('layouts.guest')

@section('title', 'Error del Sistema — A tu lado')

@section('content')
<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
  <div style="text-align: center; max-width: 520px;">
    <div style="width: 72px; height: 72px; margin: 0 auto 1.5rem; background: rgba(217,119,6,0.1); color: #D97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <span class="mono-tag" style="color: #D97706; margin-bottom: 0.5rem; display: inline-block;">ESTADO 500</span>
    <h1 style="font-family: 'Fraunces', serif; font-size: 2.2rem; color: #1A2620; margin-bottom: 0.85rem;">
      Algo no salió como esperábamos
    </h1>
    <p style="color: #556860; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
      Hemos registrado este incidente para resolverlo a la brevedad. Tu bienestar es nuestra prioridad; si te encuentras en un momento difícil, puedes consultar las líneas de ayuda directa.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-house"></i>
        <span>Volver al Inicio</span>
      </a>
      <a href="{{ route('crisis') }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-phone-volume"></i>
        <span>Líneas 24/7</span>
      </a>
    </div>
  </div>
</div>
@endsection