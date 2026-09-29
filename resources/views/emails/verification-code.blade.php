@extends('emails.layout')

@section('title', ($acceso ?? false) ? 'Confirma este nuevo acceso — A tu lado' : 'Código de verificación — A tu lado')
@section('preheader', (($acceso ?? false) ? 'Tu código para entrar es: ' : 'Tu código de verificación de 6 dígitos es: ') . $code)

@section('content')
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 48px; height: 48px; background-color: #E8F5E9; border-radius: 50%; line-height: 48px; font-size: 24px; margin-bottom: 12px;">
      🛡️
    </div>
    <h1 style="font-size: 22px; font-weight: 700; color: #1A2620; margin: 0 0 8px 0; font-family: 'Georgia', serif;">
      {{ ($acceso ?? false) ? 'Confirma este nuevo acceso' : 'Confirma tu correo electrónico' }}
    </h1>
    <p style="font-size: 15px; color: #4A5B53; margin: 0; line-height: 1.5;">
      @if($acceso ?? false)
        Hola, <strong>{{ $name }}</strong>. Alguien escribió tu contraseña para entrar a A Tu Lado desde un dispositivo, navegador o red que tu cuenta no había usado.
      @else
        Hola, <strong>{{ $name }}</strong>. Gracias por dar este paso hacia tu bienestar emocional con nosotros.
      @endif
    </p>
  </div>

  @if(($acceso ?? false) && ($dispositivo ?? null))
    <p style="font-size: 13px; color: #4A5B53; line-height: 1.5; margin: 0 0 16px 0; text-align: center; background-color: #F3EFE6; border-radius: 10px; padding: 10px 14px;">
      Desde: <strong>{{ $dispositivo }}</strong>@if($ip ?? null) · IP <strong>{{ $ip }}</strong>@endif
    </p>
  @endif

  <p style="font-size: 14px; color: #4A5B53; line-height: 1.6; margin: 0 0 20px 0; text-align: center;">
    @if($acceso ?? false)
      Si fuiste tú, escribe este código de 6 dígitos en la pantalla de acceso. Desde ese dispositivo ya no te lo volveremos a pedir:
    @else
      Para activar tu cuenta y acceder a tu espacio seguro, ingresa el siguiente código de 6 dígitos en la pantalla de verificación:
    @endif
  </p>

  <!-- Highlighted Code Box -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
    <tr>
      <td align="center">
        <div style="background-color: #F3EFE6; border: 2px dashed #8FC9A6; border-radius: 18px; padding: 18px 24px; display: inline-block; text-align: center;">
          <span class="code-digit-box" style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #1B5E20;">
            {{ $code }}
          </span>
        </div>
      </td>
    </tr>
  </table>

  <!-- Expiration Warning Pill -->
  <div style="background-color: #FFF9E6; border-left: 4px solid #E6A23C; border-radius: 8px; padding: 12px 14px; margin-bottom: 24px;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td style="vertical-align: top; width: 22px; font-size: 14px;">
          ⏱️
        </td>
        <td style="font-size: 13px; color: #8A6D1C; line-height: 1.4;">
          @if($acceso ?? false)
            <strong>Vigencia:</strong> Este código es válido durante <strong>10 minutos</strong> y sirve una sola vez.
          @else
            <strong>Vigencia:</strong> Este código es válido durante los próximos <strong>15 minutos</strong>. Si expira, podrás solicitar uno nuevo directamente desde la pantalla de registro.
          @endif
        </td>
      </tr>
    </table>
  </div>

  <!-- Safety Advice -->
  <p style="font-size: 12px; color: #556860; line-height: 1.5; margin: 0; border-top: 1px solid #EEF3F0; padding-top: 16px; text-align: center;">
    @if($acceso ?? false)
      <strong>Si no fuiste tú, alguien conoce tu contraseña.</strong> No compartas este código con nadie y cambia tu contraseña de inmediato desde tu perfil.
    @else
      Si no solicitaste crear una cuenta en <strong>A Tu Lado</strong>, puedes ignorar este correo con total tranquilidad; ninguna cuenta será activada sin este código.
    @endif
  </p>
@endsection
