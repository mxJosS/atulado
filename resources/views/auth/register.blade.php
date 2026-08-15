@extends('layouts.guest')

@section('title', 'Crear Cuenta — A tu lado')

@section('content')
<div class="auth-page-wrapper">
  <div class="hero-halo" style="top: 25%; left: 50%;"></div>

  <div class="auth-card" style="max-width: 500px;">
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
      <h1 class="auth-title">Comienza tu viaje</h1>
      <p class="auth-subtitle">Un refugio seguro para cultivar tu paz interior.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" novalidate>
      @csrf

      <div class="form-group">
        <label for="name" class="form-label">¿Cómo te gustaría que te llamemos?</label>
        <input 
          type="text" 
          name="name" 
          id="name" 
          class="form-control @error('name') is-invalid @enderror" 
          value="{{ old('name') }}" 
          placeholder="Tu nombre o apodo" 
          required 
          autocomplete="name"
          autofocus
        >
        @error('name')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

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
        >
        @error('email')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Contraseña (mínimo 6 caracteres)</label>
        <input 
          type="password" 
          name="password" 
          id="password" 
          class="form-control @error('password') is-invalid @enderror" 
          placeholder="Crea una contraseña segura" 
          required 
          autocomplete="new-password"
        >
        @error('password')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirma tu contraseña</label>
        <input 
          type="password" 
          name="password_confirmation" 
          id="password_confirmation" 
          class="form-control" 
          placeholder="Repite tu contraseña" 
          required 
          autocomplete="new-password"
        >
      </div>

      <!-- Avatar Color Choice -->
      <div class="form-group">
        <label class="form-label">Elige el color de tu árbol avatar:</label>
        <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
          @php
            $colors = [
              'sage' => ['label' => 'Menta', 'bg' => '#4d7c5f'],
              'terra' => ['label' => 'Terra', 'bg' => '#b86b4a'],
              'lav' => ['label' => 'Lavanda', 'bg' => '#7a6faa'],
              'sky' => ['label' => 'Cielo', 'bg' => '#4a7fa5'],
              'amber' => ['label' => 'Ámbar', 'bg' => '#c4901a'],
            ];
          @endphp
          @foreach($colors as $val => $c)
            <label style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
              <input type="radio" name="avatar_color" value="{{ $val }}" {{ old('avatar_color', 'sage') === $val ? 'checked' : '' }} style="display: none;" class="avatar-radio">
              <span class="avatar-swatch" style="width: 32px; height: 32px; border-radius: 50%; background: {{ $c['bg'] }}; display: flex; align-items: center; justify-content: center; border: 2px solid transparent; transition: all 0.2s;">
                <span class="check-mark" style="color: #ffffff; font-size: 0.8rem; display: {{ old('avatar_color', 'sage') === $val ? 'block' : 'none' }};">✓</span>
              </span>
              <span style="font-size: 0.72rem; color: var(--ink-600); font-family: var(--font-mono);">{{ $c['label'] }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.95rem; font-size: 1rem; margin-top: 1rem;">
        <span>Crear mi cuenta gratuita</span>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
    </form>

    <div style="text-align: center; margin-top: 1.75rem; font-size: 0.88rem; color: var(--ink-600);">
      ¿Ya tienes cuenta? 
      <a href="{{ route('login') }}" style="color: var(--sage-600); font-weight: 700; text-decoration: underline;">
        Inicia sesión aquí
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.avatar-radio').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.avatar-swatch').forEach(sw => {
        sw.style.borderColor = 'transparent';
        sw.querySelector('.check-mark').style.display = 'none';
      });
      const selectedSwatch = radio.closest('label').querySelector('.avatar-swatch');
      selectedSwatch.style.borderColor = 'var(--ink-900)';
      selectedSwatch.querySelector('.check-mark').style.display = 'block';
    });
  });
</script>
@endpush
