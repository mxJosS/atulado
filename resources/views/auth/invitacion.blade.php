@extends('layouts.guest')

@section('title', 'Activa tu cuenta — A tu lado')

@section('content')
<div class="acceso">
  <x-marca.acceso-panel eyebrow="Invitación">
    <h2>Te estábamos <em>esperando</em></h2>
    <p>Activa tu cuenta para empezar a usar tu espacio confidencial.</p>
  </x-marca.acceso-panel>

  <div class="acceso-formulario">
  <div class="acceso-tarjeta" data-aparecer>
    <div class="m-tarjeta" style="padding: clamp(1.75rem, 3vw, 2.5rem); color: #14201A;">

      <div style="text-align: center; margin-bottom: 1.5rem;">
        <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #5AB56E;">lado</em></span>
        <h1 class="acceso-tarjeta-titulo">
          @if($vigente) Activa tu cuenta @else Esta invitación ya no es válida @endif
        </h1>
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5; margin: 0;">
          @if($vigente)
            <b>{{ $membresia->institucion->nombre_corto }}</b> te invitó a A Tu Lado. Elige una contraseña para entrar.
          @else
            Puede que ya hayas creado tu contraseña, o que el enlace haya vencido.
          @endif
        </p>
      </div>

      @if($vigente)
        <form method="POST" action="{{ request()->fullUrl() }}" novalidate>
          @csrf
          <div style="margin-bottom: 1rem;">
            <label style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Nombre</label>
            <input type="text" value="{{ $membresia->user->name }}" readonly class="form-control" style="background: #F8FAF9; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box; color: #556860;">
          </div>
          <div style="margin-bottom: 1rem;">
            <label style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Correo electrónico</label>
            <input type="email" value="{{ $membresia->user->email }}" readonly class="form-control" style="background: #F8FAF9; border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box; color: #556860;">
            <span style="font-size: 0.75rem; color: #5B7369;">Con este correo iniciarás sesión.</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <label for="password" style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Contraseña</label>
            <input type="password" name="password" id="password" required autocomplete="new-password" autofocus placeholder="Al menos 8 caracteres"
                   class="form-control" style="border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box;">
            @error('password')<div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>@enderror
          </div>
          <div style="margin-bottom: 1rem;">
            <label for="password_confirmation" style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                   class="form-control" style="border: 1px solid rgba(20, 32, 26, 0.12); border-radius: 14px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box;">
          </div>
          <label style="display: flex; gap: 8px; align-items: flex-start; font-size: 0.8rem; color: #556860; margin-bottom: 1.25rem; cursor: pointer;">
            <input type="checkbox" name="acepto" value="1" @checked(old('acepto')) style="margin-top: 2px;">
            <span>Leí y acepto el <a href="{{ route('privacidad') }}" target="_blank" style="color: #2E5D4B;">aviso de privacidad</a> y los <a href="{{ route('terminos') }}" target="_blank" style="color: #2E5D4B;">términos de uso</a>, incluido el tratamiento de datos sobre mi bienestar emocional.</span>
          </label>
          @error('acepto')<div style="color: #C0392B; font-size: 0.8rem; margin: -0.8rem 0 1rem;">{{ $message }}</div>@enderror
          <button type="submit" class="m-btn m-btn-verde m-btn-ancho">Activar mi cuenta</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="m-btn m-btn-verde m-btn-ancho">Ir a iniciar sesión</a>
      @endif
    </div>
  </div>
</div>
</div>
@endsection
