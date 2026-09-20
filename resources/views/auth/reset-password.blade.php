@extends('layouts.guest')

@section('title', 'Restablecer Contraseña — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">

  <!-- Ambient Halos -->
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>
  <div style="position: absolute; width: 450px; height: 450px; border-radius: 50%; background: radial-gradient(circle, rgba(91, 74, 138, 0.2) 0%, transparent 68%); bottom: -10%; right: -5%; pointer-events: none;"></div>

  <div style="width: 100%; max-width: 440px; position: relative; z-index: 2;">

    <!-- Back to Login Link -->
    <a href="{{ route('login') }}" class="auth-back-link" style="display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: #8EADA4; text-decoration: none; margin-bottom: 1.5rem; transition: all 0.2s;">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Volver a iniciar sesión</span>
    </a>

    <!-- Main Card -->
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.25rem 2rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 1.5rem;">
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; margin-bottom: 0.85rem;">
          <svg class="ptree" viewBox="0 0 16 16" width="32" height="32" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
            <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
            <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
            <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
            <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
            <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
            <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
            <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
            <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
          </svg>
          <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #5AB56E;">lado</em></span>
        </a>

        <h1 style="font-family: var(--font-display); font-size: 1.65rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin-bottom: 0.45rem;">
          Crea tu nueva contraseña
        </h1>
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5;">
          Ingresa una nueva contraseña segura para tu cuenta.
        </p>
      </div>

      <!-- Reset Form -->
      <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group" style="margin-bottom: 1.15rem;">
          <label for="email" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Correo electrónico
          </label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email', $email) }}" 
            placeholder="tu@correo.com" 
            required 
            autocomplete="email"
            readonly
            style="background: #F8FAF9; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #556860; width: 100%; box-sizing: border-box;"
          >
          @error('email')
            <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1.15rem;">
          <label for="password" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Nueva Contraseña
          </label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-control @error('password') is-invalid @enderror" 
            placeholder="Al menos 6 caracteres" 
            required 
            autocomplete="new-password"
            autofocus
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%; box-sizing: border-box;"
          >
          @error('password')
            <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1.35rem;">
          <label for="password_confirmation" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Confirmar Contraseña
          </label>
          <input 
            type="password" 
            name="password_confirmation" 
            id="password_confirmation" 
            class="form-control" 
            placeholder="Repite la nueva contraseña" 
            required 
            autocomplete="new-password"
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%; box-sizing: border-box;"
          >
        </div>

        <button 
          type="submit" 
          class="btn btn-primary" 
          style="width: 100%; justify-content: center; padding: 0.82rem; font-size: 0.95rem; font-weight: 700; border-radius: 12px; display: flex; align-items: center; gap: 8px; background: #2E5D4B; color: #FFFFFF; border: none; cursor: pointer;"
        >
          <i class="fa-solid fa-key"></i>
          <span>Guardar nueva contraseña</span>
        </button>
      </form>

    </div>

  </div>

</div>
@endsection
