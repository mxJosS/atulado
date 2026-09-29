<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'A tu lado')</title>
  {{-- Correo con la marca (el lenguaje de Inicio): papel cálido, cabecera noche
       con el árbol y tarjeta blanca redondeada. Todo en línea: los clientes de
       correo ignoran casi cualquier hoja de estilos. --}}
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #F3EFE6;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      color: #14201A;
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
        padding: 26px 20px !important;
      }
      .code-digit-box {
        font-size: 26px !important;
        letter-spacing: 4px !important;
      }
    }
  </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F3EFE6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #14201A;">

  <!-- Preview Preheader Hidden -->
  <div style="display: none; font-size: 1px; color: #F3EFE6; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
    @yield('preheader', 'A tu lado — Espacio seguro para tu bienestar emocional.')
  </div>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3EFE6; padding: 36px 15px;">
    <tr>
      <td align="center">
        <!-- Wrapper Container -->
        <table class="email-container" border="0" cellpadding="0" cellspacing="0" width="560" style="max-width: 560px; width: 100%;">

          <!-- Brand Header: tarjeta noche con el árbol -->
          <tr>
            <td style="padding-bottom: 14px;">
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0A1410; background-image: radial-gradient(circle at 12% 0%, rgba(46, 93, 75, 0.85), rgba(10, 20, 16, 0) 60%); border-radius: 24px;">
                <tr>
                  <td align="center" style="padding: 26px 24px 22px;">
                    <a href="{{ config('app.url') }}" target="_blank" style="text-decoration: none;">
                      <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td style="vertical-align: middle; padding-right: 10px;">
                            {{-- Logo oficial en PNG: los clientes de correo no muestran SVG --}}
                            <img src="{{ rtrim(config('app.url'), '/') }}/images/marca/logo-atulado-64.png" width="44" height="44" alt="A tu lado" style="display: block; border: 0; outline: none;">
                          </td>
                          <td style="vertical-align: middle;">
                            <span style="font-size: 26px; font-weight: 700; color: #F4F8F5; letter-spacing: -0.5px; font-family: 'Georgia', serif;">
                              a tu <span style="color: #A8E6C0; font-style: italic; font-weight: 400;">lado</span>
                            </span>
                          </td>
                        </tr>
                      </table>
                    </a>
                    <div style="font-size: 11px; color: #A8E6C0; margin-top: 10px; letter-spacing: 2px; text-transform: uppercase; font-family: 'Courier New', Courier, monospace;">
                      Espacio seguro de bienestar emocional
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Main Card -->
          <tr>
            <td>
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border-radius: 24px; box-shadow: 0 12px 32px rgba(20, 32, 26, 0.08); border: 1px solid rgba(20, 32, 26, 0.08); overflow: hidden;">
                <tr>
                  <td class="card-content" style="padding: 38px 34px;">
                    @yield('content')
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Crisis: siempre a la vista -->
          <tr>
            <td style="padding-top: 14px;">
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFF3F1; border: 1px solid rgba(169, 50, 38, 0.18); border-radius: 18px;">
                <tr>
                  <td style="padding: 14px 18px; font-size: 13px; color: #8E2A20; line-height: 1.5; text-align: center;">
                    ¿Estás en crisis? Llama gratis las 24 h a la Línea de la Vida:
                    <a href="tel:8009112000" style="color: #A93226; font-weight: 700; text-decoration: none;">800 911 2000</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding-top: 24px; text-align: center;">
              <p style="font-size: 12px; color: #6F8078; line-height: 1.6; margin: 0 0 10px 0;">
                Este correo fue enviado de manera automática por la plataforma <strong>A Tu Lado</strong>.<br>
                Si tienes dudas o necesitas asistencia, contáctanos en <a href="mailto:hola@atulado.com.mx" style="color: #2E5D4B; text-decoration: underline;">hola@atulado.com.mx</a>.
              </p>
              <p style="font-size: 11px; color: #9A958A; margin: 0;">
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
