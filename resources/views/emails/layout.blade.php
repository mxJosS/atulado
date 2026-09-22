<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'A tu lado')</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #F3F7F5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      color: #1A2620;
    }
    table {
      border-collapse: collapse;
    }
    img {
      border: 0;
      line-height: 100%;
      outline: none;
      text-decoration: none;
    }
    @media only screen and (max-width: 600px) {
      .email-container {
        width: 100% !important;
        padding: 16px !important;
      }
      .card-content {
        padding: 24px 20px !important;
      }
      .code-digit-box {
        font-size: 26px !important;
        letter-spacing: 4px !important;
      }
    }
  </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F3F7F5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1A2620;">

  <!-- Preview Preheader Hidden -->
  <div style="display: none; font-size: 1px; color: #F3F7F5; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
    @yield('preheader', 'A tu lado — Espacio seguro para tu bienestar emocional.')
  </div>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3F7F5; padding: 40px 15px;">
    <tr>
      <td align="center">
        <!-- Wrapper Container -->
        <table class="email-container" border="0" cellpadding="0" cellspacing="0" width="560" style="max-width: 560px; width: 100%;">
          
          <!-- Brand Header -->
          <tr>
            <td align="center" style="padding-bottom: 24px;">
              <a href="{{ config('app.url') }}" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <table border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                      {{-- Logo oficial en PNG: los clientes de correo no muestran SVG --}}
                      <img src="{{ rtrim(config('app.url'), '/') }}/images/marca/logo-atulado-64.png" width="40" height="40" alt="A tu lado" style="display: block; border: 0; outline: none;">
                    </td>
                    <td style="vertical-align: middle;">
                      <span style="font-size: 24px; font-weight: 700; color: #2E5D4B; letter-spacing: -0.5px; font-family: 'Georgia', serif;">
                        a tu <span style="color: #4CAF50; font-style: italic;">lado</span>
                      </span>
                    </td>
                  </tr>
                </table>
              </a>
              <div style="font-size: 12px; color: #6F877C; margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                Espacio seguro de bienestar emocional
              </div>
            </td>
          </tr>

          <!-- Main Card -->
          <tr>
            <td>
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid #DCE6E1; overflow: hidden;">
                <tr>
                  <td class="card-content" style="padding: 36px 32px;">
                    @yield('content')
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding-top: 28px; text-align: center;">
              <p style="font-size: 12px; color: #7A8E85; line-height: 1.6; margin: 0 0 10px 0;">
                Este correo fue enviado de manera automática por la plataforma <strong>A Tu Lado</strong>.<br>
                Si tienes dudas o necesitas asistencia, contáctanos en <a href="mailto:hola@atulado.com.mx" style="color: #2E5D4B; text-decoration: underline;">hola@atulado.com.mx</a>.
              </p>
              <p style="font-size: 11px; color: #9EAEA6; margin: 0;">
                © {{ date('Y') }} A Tu Lado. Todos los derechos reservados. Tu privacidad y salud emocional son nuestra prioridad.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
