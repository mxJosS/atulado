@extends('layouts.guest')

@section('title', 'Activa tu cuenta — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>

  <div style="width: 100%; max-width: 440px; position: relative; z-index: 2;">
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.25rem 2rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">

      <div style="text-align: center; margin-bottom: 1.5rem;">
        <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #5AB56E;">lado</em></span>
        <h1 style="font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin: 0.6rem 0 0.45rem;">
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
            <input type="text" value="{{ $membresia->user->name }}" readonly class="form-control" style="background: #F8FAF9; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box; color: #556860;">
          </div>
          <div style="margin-bottom: 1rem;">
            <label style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Correo electrónico</label>
            <input type="email" value="{{ $membresia->user->email }}" readonly class="form-control" style="background: #F8FAF9; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box; color: #556860;">
            <span style="font-size: 0.75rem; color: #6E887E;">Con este correo iniciarás sesión.</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <label for="password" style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Contraseña</label>
            <input type="password" name="password" id="password" required autocomplete="new-password" autofocus placeholder="Al menos 8 caracteres"
                   class="form-control" style="border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box;">
            @error('password')<div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>@enderror
          </div>
          <div style="margin-bottom: 1rem;">
            <label for="password_confirmation" style="font-size: 0.86rem; font-weight: 600; margin-bottom: 0.35rem; display: block;">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                   class="form-control" style="border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; box-sizing: border-box;">
          </div>
          <label style="display: flex; gap: 8px; align-items: flex-start; font-size: 0.8rem; color: #556860; margin-bottom: 1.25rem; cursor: pointer;">
            <input type="checkbox" name="acepto" value="1" @checked(old('acepto')) style="margin-top: 2px;">
            <span>Leí y acepto el <a href="{{ route('privacidad') }}" target="_blank" style="color: #2E5D4B;">aviso de privacidad</a> y los <a href="{{ route('terminos') }}" target="_blank" style="color: #2E5D4B;">términos de uso</a>, incluido el tratamiento de datos sobre mi bienestar emocional.</span>
          </label>
          @error('acepto')<div style="color: #C0392B; font-size: 0.8rem; margin: -0.8rem 0 1rem;">{{ $message }}</div>@enderror
          <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; border-radius: 12px;">Activar mi cuenta</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; border-radius: 12px;">Ir a iniciar sesión</a>
      @endif
    </div>
  </div>
</div>
@endsection
