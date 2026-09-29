@extends('layouts.guest')

@section('title', 'Restablecer Contraseña — A tu lado')

@section('content')
<div class="acceso">
  <x-marca.acceso-panel eyebrow="Contraseña nueva">
    <h2>Un nuevo <em>comienzo</em></h2>
    <p>Elige una contraseña que sólo tú conozcas.</p>
  </x-marca.acceso-panel>

  <div class="acceso-formulario">
  <div class="acceso-tarjeta" data-aparecer>
    <div class="m-tarjeta" style="padding: clamp(1.75rem, 3vw, 2.5rem); color: #14201A;">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 1.5rem;">

        <h1 class="acceso-tarjeta-titulo">
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
            autocapitalize="none"
            spellcheck="false"
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email', $email) }}" 
            placeholder="tu@correo.com" 
            required 
            autocomplete="email"
            readonly
            style="background: #F8FAF9; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #556860; width: 100%; box-sizing: border-box;"
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
            placeholder="Al menos 8 caracteres" 
            required 
            autocomplete="new-password"
            autofocus
            style="background: #FFFFFF; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%; box-sizing: border-box;"
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
            style="background: #FFFFFF; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; font-size: 0.92rem; color: #1A2620; width: 100%; box-sizing: border-box;"
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
</div>
@endsection
