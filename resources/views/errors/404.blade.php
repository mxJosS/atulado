@extends('layouts.guest')

@section('title', 'Página no encontrada — A tu lado')

@section('content')
<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
  <div style="text-align: center; max-width: 520px;">
    <div style="width: 72px; height: 72px; margin: 0 auto 1.5rem; background: rgba(46,93,75,0.1); color: #2E5D4B; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
      <i class="fa-solid fa-compass"></i>
    </div>
    <span class="mono-tag" style="color: var(--sage-base); margin-bottom: 0.5rem; display: inline-block;">ERROR 404</span>
    <h1 style="font-family: 'Fraunces', serif; font-size: 2.2rem; color: #1A2620; margin-bottom: 0.85rem;">
      No encontramos esta página
    </h1>
    <p style="color: #556860; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
      El recurso que buscas puede haber sido movido o ya no estar disponible. Te acompañamos de regreso a un lugar seguro.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-house"></i>
        <span>Ir al Inicio</span>
      </a>
      <a href="{{ route('crisis') }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-life-ring"></i>
        <span>Líneas de Crisis</span>
      </a>
    </div>
  </div>
</div>
@endsection