@extends('layouts.guest')

@section('title', 'Acceso Restringido — A tu lado')

@section('content')
<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
  <div style="text-align: center; max-width: 520px;">
    <div style="width: 72px; height: 72px; margin: 0 auto 1.5rem; background: rgba(192,57,43,0.1); color: #C0392B; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <span class="mono-tag" style="color: #C0392B; margin-bottom: 0.5rem; display: inline-block;">ACCESO RESTRINGIDO (403)</span>
    <h1 style="font-family: 'Fraunces', serif; font-size: 2.2rem; color: #1A2620; margin-bottom: 0.85rem;">
      Área protegida
    </h1>
    <p style="color: #556860; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
      Esta sección requiere permisos administrativos o de acreditación clínica específica para ingresar.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-table-cells-large"></i>
        <span>Volver a Mi Espacio</span>
      </a>
      <a href="{{ route('home') }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 10px;">
        <i class="fa-solid fa-house"></i>
        <span>Inicio</span>
      </a>
    </div>
  </div>
</div>
@endsection