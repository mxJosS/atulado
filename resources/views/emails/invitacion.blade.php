@extends('emails.layout')

@section('title', 'Tu invitación a A tu lado')
@section('preheader', $institucion . ' te invita a A Tu Lado, un espacio confidencial para tu bienestar emocional')

@section('content')
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 48px; height: 48px; background-color: #E8F5E9; border-radius: 50%; line-height: 48px; font-size: 24px; margin-bottom: 12px;">
      🌱
    </div>
    <h1 style="font-size: 22px; font-weight: 700; color: #1A2620; margin: 0 0 8px 0; font-family: 'Georgia', serif;">
      Te damos la bienvenida
    </h1>
    <p style="font-size: 15px; color: #4A5B53; margin: 0; line-height: 1.5;">
      Hola, <strong>{{ $nombre }}</strong>.
    </p>
  </div>

  <p style="font-size: 14px; color: #4A5B53; line-height: 1.6; margin: 0 0 16px 0; text-align: center;">
    <strong>{{ $institucion }}</strong> te da acceso a <strong>A Tu Lado</strong>, un espacio para registrar cómo te sientes, encontrar herramientas de bienestar y pedir apoyo cuando lo necesites.
  </p>

  <p style="font-size: 14px; color: #4A5B53; line-height: 1.6; margin: 0 0 24px 0; text-align: center;">
    Lo que escribas es <strong>confidencial</strong>: tu organización nunca ve tus respuestas ni tu nombre, sólo datos agregados y anónimos.
  </p>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 26px;">
    <tr>
      <td align="center">
        <a href="{{ $url }}" target="_blank" style="background-color: #2E5D4B; color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-size: 15px; font-weight: 600; display: inline-block; box-shadow: 0 3px 12px rgba(46, 93, 75, 0.25);">
          Activar mi cuenta
        </a>
      </td>
    </tr>
  </table>

  <div style="background-color: #F8FAF9; border: 1px solid #E2ECE7; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;">
    <p style="font-size: 13px; color: #5B6F65; margin: 0; line-height: 1.5; text-align: center;">
      ⏱️ Este enlace es personal y vence en <strong>{{ $dias }} días</strong>. Si vence, pide a tu área de Recursos Humanos que te lo reenvíen.
    </p>
  </div>

  <div style="border-top: 1px solid #EEF3F0; padding-top: 16px; font-size: 11px; color: #8C9E95; word-break: break-all; line-height: 1.5;">
    ¿Tienes problemas con el botón? Copia y pega el siguiente enlace en tu navegador:<br>
    <a href="{{ $url }}" target="_blank" style="color: #2E5D4B; text-decoration: underline;">{{ $url }}</a>
  </div>
@endsection
