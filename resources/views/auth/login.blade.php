@extends('layouts.guest')

@section('title', 'Iniciar Sesión — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">

  <!-- Ambient Halos -->
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>
  <div style="position: absolute; width: 450px; height: 450px; border-radius: 50%; background: radial-gradient(circle, rgba(91, 74, 138, 0.2) 0%, transparent 68%); bottom: -10%; right: -5%; pointer-events: none;"></div>

  <div style="width: 100%; max-width: 440px; position: relative; z-index: 2;">

    <!-- Back to Home Link -->
    <a href="{{ route('home') }}" class="auth-back-link" style="display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: #8EADA4; text-decoration: none; margin-bottom: 1.5rem; transition: all 0.2s;">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Volver al inicio</span>
    </a>

    <!-- Main Card -->
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.25rem 2rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 1.75rem;">
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; margin-bottom: 1rem;">
          <svg class="ptree" viewBox="0 0 16 16" width="30" height="30" xmlns="http://www.w3.org/2000/svg">
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

        <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin-bottom: 0.35rem;">
          Bienvenido de vuelta
        </h1>
        <p style="font-size: 0.88rem; color: #556860; font-style: italic; line-height: 1.5;">
          "No tienes que pasar por esto solo."
        </p>
      </div>

      <!-- Demo Account Quick-Fill Pill -->
      <div style="background: #D4EDE2; border: 1px solid #5AB56E; border-radius: 12px; padding: 0.75rem 0.95rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
        <div style="font-size: 0.8rem; color: #2E5D4B;">
          <div style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-seedling" style="color: #3D7A5F;"></i>
            <span>Cuenta Demo:</span>
          </div>
          <div style="font-family: var(--font-mono); font-size: 0.74rem; color: #2E5D4B;">demo@atulado.com.mx</div>
        </div>
        <button type="button" onclick="fillDemoCredentials()" class="btn btn-sm btn-primary demo-autofill-btn" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; border-radius: 8px; white-space: nowrap; gap: 6px; background: #2E5D4B !important; color: #FFFFFF !important;">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
          <span>Auto-llenar</span>
        </button>
      </div>

      <!-- Login Form -->
      <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="form-group" style="margin-bottom: 1.15rem;">
          <label for="email" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Correo electrónico
          </label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email') }}" 
            placeholder="tu@correo.com" 
            required 
            autocomplete="email"
            autofocus
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620;"
          >
          @error('email')
            <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
            <label for="password" style="font-size: 0.86rem; font-weight: 600; color: #1A2620;">
              Contraseña
            </label>
          </div>
          <div style="position: relative;">
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control @error('password') is-invalid @enderror" 
              placeholder="••••••••" 
              required 
              autocomplete="current-password"
              style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 2.5rem 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%;"
            >
            <button 
              type="button" 
              id="togglePasswordBtn" 
              onclick="togglePasswordVisibility()"
              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #556860; padding: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.2s;"
              aria-label="Mostrar u ocultar contraseña"
            >
              <i id="eyeIcon" class="fa-solid fa-eye" style="font-size: 0.95rem;"></i>
            </button>
          </div>
          @error('password')
            <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
          @enderror
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.35rem; font-size: 0.84rem;">
          <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #556860;">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="accent-color: #2E5D4B; width: 16px; height: 16px; border-radius: 4px;">
            <span>Recordarme</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; border-radius: 12px; background: #2E5D4B !important; color: #FFFFFF !important; box-shadow: 0 4px 14px rgba(46, 93, 75, 0.35); gap: 8px;">
          <span>Iniciar sesión</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </form>

      <!-- Footer Link -->
      <div style="text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: #556860;">
        ¿No tienes cuenta? 
        <a href="{{ route('register') }}" style="color: #2E5D4B; font-weight: 700; text-decoration: underline;">
          Crear cuenta gratis
        </a>
      </div>
    </div>

    <!-- Crisis Hotline Footer -->
    <div style="text-align: center; margin-top: 1.5rem; font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; color: #8EADA4;">
      Si estás en crisis: <a href="tel:8002900024" style="color: #FFA59C; text-decoration: none; font-weight: 700;"><i class="fa-solid fa-phone"></i> 800 290 0024</a>
    </div>

  </div>
</div>
@endsection
