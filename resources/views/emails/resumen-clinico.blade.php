@extends('emails.layout')

@section('title', 'Resumen clínico confidencial')
@section('preheader', 'Tienes un resumen clínico confidencial en A Tu Lado. El enlace vence el ' . $vence)

@section('content')
  <div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; width: 48px; height: 48px; background-color: #FCEEEC; border-radius: 50%; line-height: 48px; font-size: 24px; margin-bottom: 12px;">
      🔒
    </div>
    <h1 style="font-size: 22px; font-weight: 700; color: #1A2620; margin: 0 0 8px 0; font-family: 'Georgia', serif;">
      Resumen clínico confidencial
    </h1>
    <p style="font-size: 15px; color: #4A5B53; margin: 0; line-height: 1.5;">
      {{ $destinatario }}
    </p>
  </div>

  <p style="font-size: 14px; color: #4A5B53; line-height: 1.6; margin: 0 0 24px 0; text-align: center;">
    El equipo clínico de A Tu Lado te entregó el resumen con folio <strong>{{ $folio }}</strong>. Por confidencialidad, el contenido sólo se ve desde el enlace.
  </p>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 26px;">
    <tr>
      <td align="center">
        <a href="{{ $url }}" target="_blank" style="background-color: #2E5D4B; color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-size: 15px; font-weight: 600; display: inline-block;">
          Ver el resumen
        </a>
      </td>
    </tr>
  </table>

  <div style="background-color: #FCEEEC; border: 1px solid #E9B7B0; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;">
    <p style="font-size: 13px; color: #8C1C10; margin: 0; line-height: 1.5; text-align: center;">
      ⏱️ El enlace vence el <strong>{{ $vence }}</strong>. Es de uso exclusivo del destinatario: no lo reenvíes. Prohibido usar esta información para evaluación de desempeño, sanciones, despido o cualquier acto discriminatorio.
    </p>
  </div>
@endsection
