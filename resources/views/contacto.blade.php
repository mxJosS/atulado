@extends('layouts.guest')

@section('title', 'Contacto para empresas e instituciones — A tu lado')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/contacto.css') }}?v={{ filemtime(public_path('css/contacto.css')) }}">
@endpush

@php
  $px = fn (string $nombre) => asset("images/pixel/{$nombre}.png");
  $mailto = 'mailto:' . $correo . '?subject=' . rawurlencode('Quiero A Tu Lado en mi organización');
@endphp

@section('content')
<x-marca.hero eyebrow="Para empresas e instituciones" icono="fa-solid fa-handshake">
  <h1 class="m-titulo" data-aparecer="palabras">Lleva A Tu Lado a tu <em>organización</em></h1>
  <p class="m-bajada" data-aparecer>Acompañamiento emocional para colaboradores, estudiantes y docentes. Empieza con una prueba piloto o contrata un plan para tu empresa o institución educativa.</p>
  <div class="m-acciones" data-aparecer-grupo>
    <a href="#formulario" class="m-btn m-btn-menta">
      <i class="fa-solid fa-seedling"></i>
      <span>Solicitar prueba piloto</span>
    </a>
    <a href="{{ $mailto }}" class="m-btn m-btn-vidrio">
      <i class="fa-solid fa-envelope"></i>
      <span>Escríbenos por correo</span>
    </a>
  </div>
</x-marca.hero>

<section class="m-seccion contacto">
  <div class="container contacto-rejilla">

    {{-- Qué se puede pedir y el correo directo --}}
    <div class="contacto-info" data-aparecer-grupo>
      <div class="m-tarjeta m-tarjeta-noche">
        <div class="m-tarjeta-cuerpo">
          <span class="m-etiqueta m-etiqueta-clara">Contacto corporativo</span>
          <p>¿Prefieres escribirnos directo? Te respondemos desde este correo:</p>
          <a href="{{ $mailto }}" class="contacto-correo">{{ $correo }}</a>
        </div>
      </div>

      <div class="m-tarjeta">
        <div class="m-tarjeta-cuerpo">
          <img src="{{ $px('hojas-brote') }}" alt="" width="48" height="35" class="contacto-ico">
          <h2 class="m-h3">Prueba piloto de 90 días</h2>
          <p>Empieza con un grupo de tu organización y conoce la plataforma con tu equipo antes de decidir.</p>
        </div>
      </div>

      <div class="m-tarjeta">
        <div class="m-tarjeta-cuerpo">
          <img src="{{ $px('escudo-corazon') }}" alt="" width="44" height="44" class="contacto-ico">
          <h2 class="m-h3">Un plan para tu organización</h2>
          <p>Planes para empresas, colegios y universidades. Te ayudamos a elegir el que se ajusta al tamaño de tu equipo.</p>
        </div>
      </div>
    </div>

    {{-- Formulario: llega al buzón corporativo --}}
    <div class="m-tarjeta contacto-formulario" id="formulario">
      <div class="m-tarjeta-cuerpo">
        @if(session('contacto_enviado'))
          <div class="contacto-gracias" role="status">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <h2 class="m-h3">¡Gracias! Recibimos tu solicitud</h2>
            <p>Nuestro equipo te escribirá pronto al correo que nos dejaste. Si quieres agregar algo, escríbenos a <a href="{{ $mailto }}">{{ $correo }}</a>.</p>
            <a href="{{ route('home') }}" class="m-btn m-btn-verde m-btn-chico">
              <i class="fa-solid fa-house"></i>
              <span>Volver al inicio</span>
            </a>
          </div>
        @else
          <h2 class="m-h3">Cuéntanos sobre tu organización</h2>
          <p class="contacto-nota">Te respondemos por correo. Los campos con * son obligatorios.</p>

          @if(session('error'))
            <div class="contacto-alerta" role="alert">
              <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
              <span>{{ session('error') }}</span>
            </div>
          @endif

          <form method="POST" action="{{ route('contacto.enviar') }}" novalidate>
            @csrf

            <fieldset class="contacto-intereses">
              <legend class="form-label">¿Qué te interesa? *</legend>
              <div class="contacto-opciones">
                @foreach($intereses as $clave => $texto)
                  <label class="contacto-opcion">
                    <input type="radio" name="interes" value="{{ $clave }}" @checked($interesElegido === $clave)>
                    <span>{{ $texto }}</span>
                  </label>
                @endforeach
              </div>
              @error('interes')<div class="form-error">{{ $message }}</div>@enderror
            </fieldset>

            <div class="contacto-campos">
              <div class="form-group">
                <label for="nombre" class="form-label">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="form-control @error('nombre') is-invalid @enderror" autocomplete="name" maxlength="120" required>
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group">
                <label for="puesto" class="form-label">Puesto</label>
                <input type="text" id="puesto" name="puesto" value="{{ old('puesto') }}" class="form-control @error('puesto') is-invalid @enderror" autocomplete="organization-title" maxlength="120" placeholder="Ej. Recursos Humanos">
                @error('puesto')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group">
                <label for="organizacion" class="form-label">Empresa o institución *</label>
                <input type="text" id="organizacion" name="organizacion" value="{{ old('organizacion') }}" class="form-control @error('organizacion') is-invalid @enderror" autocomplete="organization" maxlength="160" required>
                @error('organizacion')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group">
                <label for="sector" class="form-label">Sector</label>
                <select id="sector" name="sector" class="form-control @error('sector') is-invalid @enderror">
                  <option value="">Elige una opción</option>
                  @foreach(array_merge($sectores, ['Otro']) as $sector)
                    <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ $sector }}</option>
                  @endforeach
                </select>
                @error('sector')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group">
                <label for="email" class="form-label">Correo de trabajo *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" autocapitalize="none" spellcheck="false" maxlength="160" placeholder="nombre@empresa.com" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group">
                <label for="telefono" class="form-label">Teléfono o WhatsApp</label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" class="form-control @error('telefono') is-invalid @enderror" autocomplete="tel" maxlength="30">
                @error('telefono')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group contacto-campo-ancho">
                <label for="personas" class="form-label">¿Cuántas personas serían?</label>
                <select id="personas" name="personas" class="form-control @error('personas') is-invalid @enderror">
                  <option value="">Elige una opción</option>
                  @foreach($personas as $rango)
                    <option value="{{ $rango }}" @selected(old('personas') === $rango)>{{ $rango }}</option>
                  @endforeach
                </select>
                @error('personas')<div class="form-error">{{ $message }}</div>@enderror
              </div>

              <div class="form-group contacto-campo-ancho">
                <label for="mensaje" class="form-label">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="4" maxlength="2000" class="form-control @error('mensaje') is-invalid @enderror" placeholder="Cuéntanos qué necesitas, fechas o dudas.">{{ old('mensaje') }}</textarea>
                @error('mensaje')<div class="form-error">{{ $message }}</div>@enderror
              </div>
            </div>

            {{-- Trampa para bots: fuera de la vista y de los lectores de pantalla --}}
            <div class="contacto-trampa" aria-hidden="true">
              <label for="sitio_web">Sitio web</label>
              <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>

            <label class="contacto-aviso">
              <input type="checkbox" name="acepto_aviso" value="1" @checked(old('acepto_aviso')) required>
              <span>Leí y acepto el <a href="{{ route('privacidad') }}" target="_blank" rel="noopener">aviso de privacidad</a>. Usaremos estos datos para responder a tu solicitud.</span>
            </label>
            @error('acepto_aviso')<div class="form-error">{{ $message }}</div>@enderror

            <button type="submit" class="m-btn m-btn-verde m-btn-ancho">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Enviar solicitud</span>
            </button>
          </form>
        @endif
      </div>
    </div>

  </div>
</section>

@if(session('contacto_enviado') || session('error') || $errors->any())
  {{-- Tras enviar, main.js cambia el contenido sin recargar y sin avisar a
       js/animaciones.js: lo marcado para aparecer se muestra ya visible, y la
       vista va al aviso de gracias o al primer campo con error. --}}
  <script>
    (function () {
      var zona = document.getElementById('spaContent');
      if (!zona) return;
      zona.querySelectorAll('[data-aparecer], [data-aparecer-grupo] > *').forEach(function (el) {
        el.setAttribute('data-visto', '');
      });
      var error = zona.querySelector('#formulario .form-error');
      if (error) {
        error.scrollIntoView({ block: 'center' });
      } else {
        document.getElementById('formulario').scrollIntoView({ block: 'start' });
      }
    })();
  </script>
@endif
@endsection
