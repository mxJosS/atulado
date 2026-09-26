{{--
  Consentimiento expreso (LFPDPPP): las tres primeras casillas son obligatorias
  y empiezan sin marcar; las dos últimas son finalidades secundarias opcionales.
  Se usa en el registro y en /consentimiento.
--}}
@php
  $casilla = 'display: flex; gap: 9px; align-items: flex-start; font-size: 0.82rem; line-height: 1.45; color: #33413B; margin-bottom: 0.6rem; cursor: pointer;';
  $enlace = 'color: #2E5D4B; font-weight: 600; text-decoration: underline;';
@endphp
<fieldset style="border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.9rem 1rem 0.5rem; margin: 0 0 1.25rem;">
  <legend style="font-size: 0.8rem; font-weight: 700; color: #1A2620; padding: 0 0.35rem;">Tu consentimiento</legend>

  <label style="{{ $casilla }}">
    <input type="checkbox" name="acepto_aviso" value="1" @checked(old('acepto_aviso')) required style="margin-top: 3px; accent-color: #2E5D4B;">
    <span>Leí y acepto el <a href="{{ route('privacidad') }}" target="_blank" rel="noopener" style="{{ $enlace }}">aviso de privacidad</a> y los <a href="{{ route('terminos') }}" target="_blank" rel="noopener" style="{{ $enlace }}">términos del servicio</a>. <b>(obligatorio)</b></span>
  </label>
  @error('acepto_aviso')<div style="color: #C0392B; font-size: 0.78rem; margin: -0.3rem 0 0.6rem 1.6rem;">{{ $message }}</div>@enderror

  <label style="{{ $casilla }}">
    <input type="checkbox" name="acepto_sensibles" value="1" @checked(old('acepto_sensibles')) required style="margin-top: 3px; accent-color: #2E5D4B;">
    <span>Doy mi <b>consentimiento expreso</b> para que A tu lado trate mis datos sensibles sobre mi estado emocional (registro diario, cuestionarios y plan de seguridad) con las finalidades del aviso, incluido el protocolo de crisis. <b>(obligatorio)</b></span>
  </label>
  @error('acepto_sensibles')<div style="color: #C0392B; font-size: 0.78rem; margin: -0.3rem 0 0.6rem 1.6rem;">{{ $message }}</div>@enderror

  <label style="{{ $casilla }}">
    <input type="checkbox" name="mayor_de_edad" value="1" @checked(old('mayor_de_edad')) required style="margin-top: 3px; accent-color: #2E5D4B;">
    <span>Confirmo que soy <b>mayor de edad</b>. <b>(obligatorio)</b></span>
  </label>
  @error('mayor_de_edad')<div style="color: #C0392B; font-size: 0.78rem; margin: -0.3rem 0 0.6rem 1.6rem;">{{ $message }}</div>@enderror

  <div style="font-size: 0.74rem; color: #556860; margin: 0.4rem 0 0.45rem; border-top: 1px solid #EEF4F0; padding-top: 0.55rem;">
    Opcional. Si no las marcas, usas A tu lado exactamente igual; puedes cambiarlas cuando quieras desde tu perfil.
  </div>

  <label style="{{ $casilla }}">
    <input type="checkbox" name="acepta_investigacion" value="1" @checked(old('acepta_investigacion')) style="margin-top: 3px; accent-color: #2E5D4B;">
    <span>Acepto que mi información, <b>anonimizada</b>, se use para mejorar la plataforma y para investigación académica.</span>
  </label>
  <label style="{{ $casilla }}">
    <input type="checkbox" name="acepta_boletin" value="1" @checked(old('acepta_boletin')) style="margin-top: 3px; accent-color: #2E5D4B;">
    <span>Quiero recibir por correo contenido informativo sobre salud emocional.</span>
  </label>
</fieldset>
