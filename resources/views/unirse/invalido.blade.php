@extends('layouts.guest')

@section('title', 'Enlace no válido — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 80vh; background: #080C0A !important; display: flex; align-items: center; justify-content: center; padding: 2.5rem 1.5rem;">
  <div style="width: 100%; max-width: 440px; background: #FFFFFF; border-radius: 24px; padding: 2.25rem 2rem; border: 1.5px solid #DCE8E0; color: #1A2620; text-align: center;">
    <h1 style="font-family: var(--font-display); font-size: 1.45rem; font-weight: 700; margin: 0 0 0.6rem;">Este enlace ya no funciona</h1>
    <p style="font-size: 0.9rem; color: #556860; line-height: 1.55; margin: 0 0 1.25rem;">
      Puede que tu institución lo haya cambiado o desactivado. Pide el enlace nuevo a quien te lo compartió.
      Mientras tanto puedes crear tu cuenta por tu cuenta o entrar si ya tienes una.
    </p>
    <div style="display: flex; gap: 0.6rem; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('login') }}" class="btn btn-primary" style="border-radius: 12px; background: #2E5D4B !important; color: #FFFFFF !important;">Iniciar sesión</a>
      <a href="{{ route('home') }}" class="btn" style="border-radius: 12px; border: 1.5px solid #C2D6CA; color: #1A2620;">Ir al inicio</a>
    </div>
  </div>
</div>
@endsection
