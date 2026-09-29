@extends('layouts.guest')

@section('title', 'Únete a ' . $institucion->nombre_corto . ' — A tu lado')

@section('content')
@php
  $campo = 'border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.8rem 1rem; width: 100%; box-sizing: border-box; font-size: 0.92rem; color: #1A2620; background: #FFFFFF;';
  $etiqueta = 'font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1A2620;';
  $error = 'color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;';
@endphp
<div class="acceso">
  <x-marca.acceso-panel eyebrow="Invitación de {{ $institucion->nombre_corto }}">
    <h2>Un espacio que es <em>sólo tuyo</em></h2>
    <p>Tu organización nunca ve lo que escribes: sólo porcentajes del total, para cuidar mejor a todo el equipo.</p>
  </x-marca.acceso-panel>

  <div class="acceso-formulario">
  <div class="acceso-tarjeta" data-aparecer>
    <div class="m-tarjeta" style="padding: clamp(1.75rem, 3vw, 2.5rem); color: #14201A;">

      <div style="text-align: center; margin-bottom: 1.5rem;">
        <span style="display: inline-flex; width: 52px; height: 52px; border-radius: 14px; align-items: center; justify-content: center; font-weight: 800; font-size: 1.15rem; color: #FFFFFF; background: {{ $institucion->color ?: '#2E5D4B' }};">{{ $institucion->iniciales }}</span>
        <h1 style="font-family: var(--font-display); font-size: 1.55rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin: 0.75rem 0 0.45rem;">
          Únete a {{ $institucion->nombre_corto }}
        </h1>
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5; margin: 0;">
          {{ $institucion->nombre_corto }} te invita a <b>A tu lado</b>, un espacio confidencial de acompañamiento emocional.
          Tu organización <b>nunca</b> ve lo que escribes: sólo porcentajes del total.
        </p>
      </div>

      @auth
        <p style="font-size: 0.88rem; color: #33413B; text-align: center; margin: 0 0 1rem;">
          Entraste como <b>{{ auth()->user()->name }}</b> ({{ auth()->user()->email }}).
        </p>
        <form method="POST" action="{{ route('unirse.unir', $token) }}">
          @csrf
          <button type="submit" class="m-btn m-btn-verde m-btn-ancho">
            Unirme a {{ $institucion->nombre_corto }}
          </button>
        </form>
        <form method="POST" action="{{ route('logout') }}" style="text-align: center; margin-top: 0.9rem;">
          @csrf
          <button type="submit" style="background: none; border: none; color: #2E5D4B; text-decoration: underline; font-size: 0.84rem; cursor: pointer;">No soy yo: cerrar sesión y usar otra cuenta</button>
        </form>
      @else
        {{-- Google siempre disponible en el enlace, aunque esté apagado en el resto del sitio --}}
        @if($googleConfigurado)
          <a href="{{ route('unirse.google', $token) }}" id="unirseGoogle"
             style="width: 100%; justify-content: center; background: #FFFFFF; color: #374151; border: 1.5px solid #D1D5DB; border-radius: 12px; padding: 0.75rem 1rem; font-weight: 600; font-size: 0.92rem; display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); text-decoration: none; box-sizing: border-box;">
            <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Continuar con Google</span>
          </a>
          <p style="font-size: 0.74rem; color: #556860; text-align: center; margin: 0 0 1.1rem;">Antes de empezar te pediremos tu consentimiento para el aviso de privacidad.</p>

          <div style="display: flex; align-items: center; margin-bottom: 1.1rem; color: #5B7369; font-size: 0.75rem; text-transform: uppercase; font-family: var(--font-mono); letter-spacing: 0.06em;">
            <span style="flex: 1; height: 1px; background: #DCE8E0;"></span>
            <span style="padding: 0 0.75rem;">o crea tu cuenta con tu correo</span>
            <span style="flex: 1; height: 1px; background: #DCE8E0;"></span>
          </div>
        @endif

        <form method="POST" action="{{ route('register') }}" novalidate>
          @csrf
          <div style="margin-bottom: 0.9rem;">
            <label for="name" style="{{ $etiqueta }}">Nombre o apodo</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name" style="{{ $campo }}">
            @error('name')<div style="{{ $error }}">{{ $message }}</div>@enderror
          </div>
          <div style="margin-bottom: 0.9rem;">
            <label for="email" style="{{ $etiqueta }}">Correo electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" spellcheck="false" style="{{ $campo }}">
            @error('email')<div style="{{ $error }}">{{ $message }}</div>@enderror
          </div>
          <div style="margin-bottom: 0.9rem;">
            <label for="password" style="{{ $etiqueta }}">Contraseña (mínimo 8 caracteres)</label>
            <input type="password" name="password" id="password" required autocomplete="new-password" style="{{ $campo }}">
            @error('password')<div style="{{ $error }}">{{ $message }}</div>@enderror
          </div>
          <div style="margin-bottom: 1.1rem;">
            <label for="password_confirmation" style="{{ $etiqueta }}">Confirma tu contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" style="{{ $campo }}">
          </div>

          @include('auth.partials.consentimiento')

          <button type="submit" class="m-btn m-btn-verde m-btn-ancho">
            Crear mi cuenta y unirme
          </button>
        </form>

        <div style="text-align: center; margin-top: 1.25rem; font-size: 0.86rem; color: #556860;">
          ¿Ya tienes cuenta en A tu lado?
          <a href="{{ route('login') }}" style="color: #2E5D4B; font-weight: 700; text-decoration: underline;">Inicia sesión</a>
          y te unimos a {{ $institucion->nombre_corto }}.
        </div>
      @endauth
    </div>
  </div>
  </div>
</div>
@endsection
