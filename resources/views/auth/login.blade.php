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

        <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin-bottom: 0.35rem;">
          Bienvenido de vuelta
        </h1>
        <p style="font-size: 0.88rem; color: #556860; font-style: italic; line-height: 1.5;">
          "No tienes que pasar por esto solo."
        </p>
      </div>

      <!-- GOOGLE SIGN IN BUTTON (CON LOGO OFICIAL A COLOR) -->
      <button 
        type="button" 
        id="googleLoginBtn"
        onclick="loginWithGoogleDemo()" 
        style="width: 100%; justify-content: center; background: #FFFFFF; color: #374151; border: 1.5px solid #D1D5DB; border-radius: 12px; padding: 0.75rem 1rem; font-weight: 600; font-size: 0.92rem; display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem; transition: all 0.2s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.06);"
        onmouseover="this.style.background='#F9FAFB'; this.style.borderColor='#9CA3AF';"
        onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#D1D5DB';"
      >
        <!-- Official Google Multicolored SVG Logo -->
        <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
        <span>Acceder con Google</span>
      </button>

      <!-- Divider -->
      <div style="display: flex; align-items: center; margin-bottom: 1.25rem; color: #8EADA4; font-size: 0.75rem; text-transform: uppercase; font-family: var(--font-mono); letter-spacing: 0.08em;">
        <div style="flex: 1; height: 1px; background: #DCE8E0;"></div>
        <span style="padding: 0 0.75rem;">o con tu correo</span>
        <div style="flex: 1; height: 1px; background: #DCE8E0;"></div>
      </div>

      <!-- Demo Account Quick-Fill Pill -->
      <div style="background: #D4EDE2; border: 1px solid #5AB56E; border-radius: 12px; padding: 0.75rem 0.95rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
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
      <form id="mainLoginForm" method="POST" action="{{ route('login') }}" novalidate>
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
      SI ESTÁS EN CRISIS: <a href="tel:8002900024" style="color: #FFA59C; text-decoration: none; font-weight: 700;"><i class="fa-solid fa-phone"></i> 800 290 0024</a>
    </div>

  </div>
</div>

@push('scripts')
<script>
  function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    if (pwdInput.type === 'password') {
      pwdInput.type = 'text';
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      pwdInput.type = 'password';
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  }

  function fillDemoCredentials() {
    document.getElementById('email').value = 'demo@atulado.com.mx';
    document.getElementById('password').value = 'demo1234';
  }

  function loginWithGoogleDemo() {
    // Autofill demo and submit for frictionless Google one-click access demo
    document.getElementById('email').value = 'demo@atulado.com.mx';
    document.getElementById('password').value = 'demo1234';
    document.getElementById('mainLoginForm').submit();
  }
</script>
@endpush
@endsection
