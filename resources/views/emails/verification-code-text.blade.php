A TU LADO - Espacio seguro de bienestar emocional

@if($acceso ?? false)
Tu código para entrar
=================================

Hola {{ $name }},

Alguien escribió tu contraseña para entrar a la consola de A Tu Lado. Si fuiste tú, escribe este código de 6 dígitos en la pantalla de acceso:

CÓDIGO DE ACCESO: {{ $code }}

VIGENCIA:
Este código es válido durante 10 minutos y sirve una sola vez.

Si no fuiste tú, alguien conoce tu contraseña. No compartas este código con nadie y cambia tu contraseña de inmediato desde tu perfil.
@else
Confirma tu correo electrónico
=================================

Hola {{ $name }},

Gracias por dar este paso hacia tu bienestar emocional con nosotros.
Para activar tu cuenta y acceder a tu espacio seguro, ingresa el siguiente código de 6 dígitos:

CÓDIGO DE VERIFICACIÓN: {{ $code }}

VIGENCIA:
Este código es válido durante los próximos 15 minutos. Si expira, podrás solicitar uno nuevo directamente desde la pantalla de verificación.

Si no solicitaste crear una cuenta en A Tu Lado, puedes ignorar este correo con total tranquilidad; ninguna cuenta será activada sin este código.
@endif

Atentamente,
El equipo de A Tu Lado
https://atulado.com.mx
soporte: hola@atulado.com.mx
