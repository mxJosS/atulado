@extends('layouts.guest')

@section('title', 'Recuperar Contraseña — A tu lado')

@section('content')
<div class="acceso">
  <x-marca.acceso-panel eyebrow="Recupera tu acceso">
    <h2>Todos olvidamos <em>algo</em></h2>
    <p>Te enviamos un enlace a tu correo para que crees una contraseña nueva.</p>
  </x-marca.acceso-panel>

  <div class="acceso-formulario">
  <div class="acceso-tarjeta" data-aparecer>
    <div class="m-tarjeta" style="padding: clamp(1.75rem, 3vw, 2.5rem); color: #14201A;">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 1.5rem;">

        <h1 class="acceso-tarjeta-titulo">
          ¿Olvidaste tu contraseña?
        </h1>
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5;">
          No te preocupes. Escribe el correo electrónico asociado a tu cuenta y te enviaremos un enlace seguro para restablecerla.
        </p>
      </div>

      <!-- Status Alert -->
      @if (session('status'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E5D4B; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.35rem; font-size: 0.86rem; color: #1B5E20; line-height: 1.4;">
          <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
          {{ session('status') }}
        </div>
      @endif

      <!-- Reset Request Form -->
      <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="form-group" style="margin-bottom: 1.35rem;">
          <label for="email" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Correo electrónico
          </label>
          <input 
            type="email" 
            name="email" 
            id="email"
            autocapitalize="none"
            spellcheck="false"
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email') }}" 
            placeholder="tu@correo.com" 
            required 
            autocomplete="email"
            autofocus
            style="background: #FFFFFF; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%; box-sizing: border-box;"
          >
          @error('email')
            <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
          @enderror
        </div>

        <button 
          type="submit" 
          class="btn btn-primary" 
          style="width: 100%; justify-content: center; padding: 0.82rem; font-size: 0.95rem; font-weight: 700; border-radius: 12px; display: flex; align-items: center; gap: 8px; background: #2E5D4B; color: #FFFFFF; border: none; cursor: pointer;"
        >
          <i class="fa-solid fa-paper-plane"></i>
          <span>Enviar enlace de recuperación</span>
        </button>
      </form>

      <!-- Footer Link inside Card -->
      <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #EEF4F0;">
        <span style="font-size: 0.84rem; color: #556860;">¿Recordaste tu clave?</span>
        <a href="{{ route('login') }}" style="font-size: 0.84rem; font-weight: 700; color: #2E5D4B; text-decoration: none; margin-left: 4px;">
          Iniciar sesión
        </a>
      </div>

    </div>

  </div>

</div>
</div>
@endsection
