@extends('layouts.guest')

@section('title', 'Iniciar Sesión — A tu lado')

@section('content')
<div class="auth-page-wrapper">
  <!-- Subtle ambient halo -->
  <div class="hero-halo" style="top: 30%; left: 50%;"></div>

  <div class="auth-card">
    <div class="auth-header">
      <a href="{{ route('home') }}" class="auth-brand-logo">
        <div class="tree-floating-wrapper">
          <svg class="ptree" viewBox="0 0 16 16" width="32" height="32" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#5a9060"/>
            <rect x="3" y="2" width="10" height="2" fill="#6B8F71"/>
            <rect x="2" y="4" width="12" height="2" fill="#7ab870"/>
            <rect x="3" y="6" width="10" height="2" fill="#6B8F71"/>
            <rect x="5" y="8" width="6" height="2" fill="#5a9060"/>
            <rect x="7" y="10" width="2" height="4" fill="#8B5e30"/>
            <rect x="4" y="1" width="1" height="1" fill="#e84040"/>
            <rect x="11" y="3" width="1" height="1" fill="#e84040"/>
            <rect x="9" y="7" width="1" height="1" fill="#e84040"/>
          </svg>
        </div>
      </a>
      <h1 class="auth-title">Bienvenido de vuelta</h1>
      <p class="auth-subtitle">"No tienes que pasar por esto solo."</p>
    </div>

    <!-- Judge / Evaluation Demo Helper Pill -->
    <div style="background: var(--sage-50); border: 1px dashed var(--sage-300); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.82rem; color: var(--sage-800);">
      <div style="font-weight: 700; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
        <span>🌱</span> <span>Acceso de Demostración (Evaluadores):</span>
      </div>
      <div>Correo: <code>demo@atulado.com.mx</code> · Clave: <code>password123</code></div>
      <button type="button" onclick="fillDemoCredentials()" style="background: none; border: none; color: var(--sage-600); font-weight: 700; text-decoration: underline; cursor: pointer; margin-top: 0.35rem; font-size: 0.78rem;">
        Rellenar credenciales automáticamente →
      </button>
    </div>

    <form method="POST" action="{{ route('login') }}" novalidate>
      @csrf

      <div class="form-group">
        <label for="email" class="form-label">Correo electrónico</label>
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
        >
        @error('email')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label for="password" class="form-label">Contraseña</label>
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
          >
          <button 
            type="button" 
            id="togglePasswordBtn" 
            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--ink-400);"
            aria-label="Ver u ocultar contraseña"
          >
            👁️
          </button>
        </div>
        @error('password')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.85rem;">
        <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: var(--ink-700);">
          <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="accent-color: var(--sage-500);">
          <span>Recordarme en este dispositivo</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.95rem; font-size: 1rem;">
        <span>Entrar a mi espacio</span>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
    </form>

    <div style="text-align: center; margin-top: 1.75rem; font-size: 0.88rem; color: var(--ink-600);">
      ¿Aún no tienes cuenta? 
      <a href="{{ route('register') }}" style="color: var(--sage-600); font-weight: 700; text-decoration: underline;">
        Crear cuenta gratis
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function fillDemoCredentials() {
    document.getElementById('email').value = 'demo@atulado.com.mx';
    document.getElementById('password').value = 'password123';
  }

  const toggleBtn = document.getElementById('togglePasswordBtn');
  const passInput = document.getElementById('password');
  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', () => {
      passInput.type = passInput.type === 'password' ? 'text' : 'password';
    });
  }
</script>
@endpush
