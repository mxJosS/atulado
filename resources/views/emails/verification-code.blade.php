@extends('emails.layout')

@section('title', 'Código de verificación — A tu lado')
@section('preheader', 'Tu código de verificación de 6 dígitos es: ' . $code)

@section('content')
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 48px; height: 48px; background-color: #E8F5E9; border-radius: 50%; line-height: 48px; font-size: 24px; margin-bottom: 12px;">
      🛡️
    </div>
    <h1 style="font-size: 22px; font-weight: 700; color: #1A2620; margin: 0 0 8px 0; font-family: 'Georgia', serif;">
      Confirma tu correo electrónico
    </h1>
    <p style="font-size: 15px; color: #4A5B53; margin: 0; line-height: 1.5;">
      Hola, <strong>{{ $name }}</strong>. Gracias por dar este paso hacia tu bienestar emocional con nosotros.
    </p>
  </div>

  <p style="font-size: 14px; color: #4A5B53; line-height: 1.6; margin: 0 0 20px 0; text-align: center;">
    Para activar tu cuenta y acceder a tu espacio seguro, ingresa el siguiente código de 6 dígitos en la pantalla de verificación:
  </p>

  <!-- Highlighted Code Box -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
    <tr>
      <td align="center">
        <div style="background-color: #F0F7F2; border: 2px dashed #99C5A8; border-radius: 14px; padding: 18px 24px; display: inline-block; text-align: center;">
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
          <strong>Vigencia:</strong> Este código es válido durante los próximos <strong>15 minutos</strong>. Si expira, podrás solicitar uno nuevo directamente desde la pantalla de registro.
        </td>
      </tr>
    </table>
  </div>

  <!-- Safety Advice -->
  <p style="font-size: 12px; color: #7B8F85; line-height: 1.5; margin: 0; border-top: 1px solid #EEF3F0; padding-top: 16px; text-align: center;">
    Si no solicitaste crear una cuenta en <strong>A Tu Lado</strong>, puedes ignorar este correo con total tranquilidad; ninguna cuenta será activada sin este código.
  </p>
@endsection
