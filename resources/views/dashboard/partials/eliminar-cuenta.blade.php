{{-- Formulario para que la persona elimine su cuenta (perfil y pantalla de consentimiento). --}}
@php $sinContrasena = (bool) auth()->user()->google_id; @endphp
<p style="font-size: 0.84rem; color: #33413B; line-height: 1.55; margin: 0 0 0.75rem;">
  Se borran tu cuenta, tu registro diario, tus cuestionarios, tu plan de seguridad y tus contactos de emergencia, y no se pueden recuperar.
  Si alguna vez se abrió un caso de crisis, se conserva <b>sin tu nombre</b> durante 5 años, como explica el aviso de privacidad.
  Si hoy tienes un caso abierto, primero lo cierra el equipo clínico.
</p>
<form method="POST" action="{{ route('profile.destroy') }}">
  @csrf
  @method('DELETE')
  @unless($sinContrasena)
    <div style="margin-bottom: 0.65rem;">
      <label for="password_eliminar" style="font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 0.3rem;">Tu contraseña</label>
      <input type="password" name="password_eliminar" id="password_eliminar" autocomplete="current-password" required class="form-control" style="max-width: 320px;">
      @error('password_eliminar', 'eliminarCuenta')<div style="color: #C0392B; font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
    </div>
  @endunless
  <div style="margin-bottom: 0.75rem;">
    <label for="confirmacion_eliminar" style="font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 0.3rem;">Escribe ELIMINAR para confirmar</label>
    <input type="text" name="confirmacion" id="confirmacion_eliminar" autocomplete="off" autocapitalize="characters" spellcheck="false" required class="form-control" style="max-width: 320px;">
    @error('confirmacion', 'eliminarCuenta')<div style="color: #C0392B; font-size: 0.78rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
  </div>
  <button type="submit" style="background: #B02418; color: #FFFFFF; border: none; border-radius: 10px; padding: 0.55rem 1rem; font-size: 0.86rem; font-weight: 700; cursor: pointer;">
    Eliminar mi cuenta para siempre
  </button>
</form>
