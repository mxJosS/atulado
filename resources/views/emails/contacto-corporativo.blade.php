@extends('emails.layout')

@section('title', 'Nueva solicitud de contacto corporativo — A tu lado')
@section('preheader', $datos['interes_texto'] . ' · ' . $datos['organizacion'] . ' · ' . $datos['nombre'])

@php
  // [etiqueta, valor, enlace]: sólo se muestran las filas con valor
  $filas = array_filter([
      ['Nombre', $datos['nombre'], null],
      ['Puesto', $datos['puesto'], null],
      ['Organización', $datos['organizacion'], null],
      ['Sector', $datos['sector'], null],
      ['Personas', $datos['personas'], null],
      ['Correo', $datos['email'], 'mailto:' . $datos['email']],
      ['Teléfono', $datos['telefono'], $datos['telefono'] ? 'tel:' . preg_replace('/[^0-9+]/', '', $datos['telefono']) : null],
  ], fn ($fila) => filled($fila[1]));
@endphp

@section('content')
  <div style="text-align: center; margin-bottom: 22px;">
    <div style="display: inline-block; width: 48px; height: 48px; background-color: #E8F5E9; border-radius: 50%; line-height: 48px; font-size: 24px; margin-bottom: 12px;">
      🤝
    </div>
    <h1 style="font-size: 22px; font-weight: 700; color: #1A2620; margin: 0 0 8px 0; font-family: 'Georgia', serif;">
      Nueva solicitud de contacto
    </h1>
    <p style="font-size: 15px; color: #4A5B53; margin: 0; line-height: 1.5;">
      Una organización escribió desde la página de Contacto. Responde a este correo para contestarle directo a <strong>{{ $datos['nombre'] }}</strong>.
    </p>
  </div>

  <div style="text-align: center; margin-bottom: 22px;">
    <span style="display: inline-block; background-color: #F3EFE6; border: 1px solid #8FC9A6; border-radius: 999px; padding: 8px 16px; font-size: 13px; font-weight: 700; color: #2E5D4B;">
      {{ $datos['interes_texto'] }}
    </span>
  </div>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 22px; font-size: 14px; color: #14201A;">
    @foreach($filas as [$etiqueta, $valor, $enlace])
      <tr>
        <td style="padding: 10px 12px 10px 0; border-bottom: 1px solid #EEF3F0; color: #6F8078; width: 34%; vertical-align: top;">{{ $etiqueta }}</td>
        <td style="padding: 10px 0; border-bottom: 1px solid #EEF3F0; font-weight: 600; vertical-align: top;">
          @if($enlace)
            <a href="{{ $enlace }}" style="color: #2E5D4B; text-decoration: underline;">{{ $valor }}</a>
          @else
            {{ $valor }}
          @endif
        </td>
      </tr>
    @endforeach
  </table>

  @if($datos['mensaje'])
    <p style="font-size: 12px; color: #6F8078; margin: 0 0 6px 0; letter-spacing: 1px; text-transform: uppercase; font-family: 'Courier New', Courier, monospace;">Mensaje</p>
    <div style="background-color: #F3EFE6; border-radius: 14px; padding: 14px 16px; margin-bottom: 22px; font-size: 14px; color: #33413B; line-height: 1.6;">
      {!! nl2br(e($datos['mensaje'])) !!}
    </div>
  @endif

  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center">
        <a href="mailto:{{ $datos['email'] }}?subject={{ rawurlencode('Re: ' . $datos['interes_texto'] . ' para ' . $datos['organizacion']) }}" target="_blank" style="display: inline-block; background-color: #2E5D4B; color: #FFFFFF; text-decoration: none; font-size: 15px; font-weight: 700; padding: 13px 26px; border-radius: 999px;">
          Responder a {{ $datos['nombre'] }}
        </a>
      </td>
    </tr>
  </table>
@endsection
