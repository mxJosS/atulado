A TU LADO - Nueva solicitud de contacto corporativo

{{ $datos['interes_texto'] }}
=================================

Nombre: {{ $datos['nombre'] }}
@if($datos['puesto'])
Puesto: {{ $datos['puesto'] }}
@endif
Organización: {{ $datos['organizacion'] }}
@if($datos['sector'])
Sector: {{ $datos['sector'] }}
@endif
@if($datos['personas'])
Personas: {{ $datos['personas'] }}
@endif
Correo: {{ $datos['email'] }}
@if($datos['telefono'])
Teléfono: {{ $datos['telefono'] }}
@endif
@if($datos['mensaje'])

MENSAJE:
{{ $datos['mensaje'] }}
@endif

Responde a este correo para contestarle directo a {{ $datos['nombre'] }}.

Enviado desde la página de Contacto de A Tu Lado
{{ route('contacto') }}
