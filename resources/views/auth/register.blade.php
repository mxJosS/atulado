@extends('layouts.guest')

@section('title', 'Crear Cuenta — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">

  <!-- Ambient Halos -->
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>
  <div style="position: absolute; width: 450px; height: 450px; border-radius: 50%; background: radial-gradient(circle, rgba(91, 74, 138, 0.2) 0%, transparent 68%); bottom: -10%; right: -5%; pointer-events: none;"></div>

  <div style="width: 100%; max-width: 480px; position: relative; z-index: 2;">

    <!-- Back Link -->
    <a href="{{ route('home') }}" class="auth-back-link" style="display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: #8EADA4; text-decoration: none; margin-bottom: 1.5rem; transition: all 0.2s;">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Volver al inicio</span>
    </a>

    <!-- Main Card -->
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.5rem 2.25rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">

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
          Crea tu espacio seguro
        </h1>
        <p style="font-size: 0.88rem; color: #556860; font-style: italic;">
          Un refugio personal para cultivar tu bienestar diario.
        </p>
      </div>

      <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="form-group" style="margin-bottom: 1.1rem;">
          <label for="name" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            ¿Cómo te gustaría que te llamemos?
          </label>
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
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620;"
          >
          @error('name') <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1.1rem;">
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
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620;"
          >
          @error('email') <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1.1rem;">
          <label for="password" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Contraseña (mínimo 6 caracteres)
          </label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-control @error('password') is-invalid @enderror" 
            placeholder="••••••••" 
            required 
            autocomplete="new-password"
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620;"
          >
          @error('password') <div class="form-error" style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 1.25rem;">
          <label for="password_confirmation" style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.35rem; display: block;">
            Confirma tu contraseña
          </label>
          <input 
            type="password" 
            name="password_confirmation" 
            id="password_confirmation" 
            class="form-control" 
            placeholder="••••••••" 
            required 
            autocomplete="new-password"
            style="background: #FFFFFF; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620;"
          >
        </div>

        <!-- Avatar Color Selector -->
        <div style="margin-bottom: 1.5rem;">
          <label style="font-size: 0.86rem; font-weight: 600; color: #1A2620; margin-bottom: 0.5rem; display: block;">
            Tono de tu árbol avatar:
          </label>
          <div style="display: flex; gap: 0.85rem;">
            @php
              $colors = [
                'sage' => ['label' => 'Sage', 'bg' => '#2E5D4B'],
                'mint' => ['label' => 'Menta', 'bg' => '#5AB56E'],
                'lav' => ['label' => 'Lavanda', 'bg' => '#5B4A8A'],
                'amber' => ['label' => 'Dorado', 'bg' => '#C8B87A'],
                'terra' => ['label' => 'Tierra', 'bg' => '#6B3A1F'],
              ];
            @endphp
            @foreach($colors as $val => $c)
              <label style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                <input type="radio" name="avatar_color" value="{{ $val }}" {{ old('avatar_color', 'sage') === $val ? 'checked' : '' }} style="display: none;" class="avatar-radio">
                <span class="avatar-swatch" style="width: 34px; height: 34px; border-radius: 50%; background: {{ $c['bg'] }}; display: flex; align-items: center; justify-content: center; border: 2px solid {{ old('avatar_color', 'sage') === $val ? '#2E5D4B' : 'transparent' }};">
                  <i class="fa-solid fa-check check-mark" style="color: #FFFFFF; font-size: 0.75rem; display: {{ old('avatar_color', 'sage') === $val ? 'block' : 'none' }};"></i>
                </span>
                <span style="font-size: 0.7rem; color: #556860; font-family: var(--font-mono);">{{ $c['label'] }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; border-radius: 12px; background: #2E5D4B !important; color: #FFFFFF !important; box-shadow: 0 4px 14px rgba(46, 93, 75, 0.35); gap: 8px;">
          <span>Crear mi cuenta gratis</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </form>

      <div style="text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: #556860;">
        ¿Ya tienes cuenta? 
        <a href="{{ route('login') }}" style="color: #2E5D4B; font-weight: 700; text-decoration: underline;">
          Iniciar sesión aquí
        </a>
      </div>
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
      selectedSwatch.style.borderColor = '#2E5D4B';
      selectedSwatch.querySelector('.check-mark').style.display = 'block';
    });
  });
</script>
@endpush
