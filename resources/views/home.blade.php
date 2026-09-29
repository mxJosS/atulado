@extends('layouts.guest')

@section('title', 'A tu lado — Apoyo Emocional, Herramientas DBT y Prevención')

@push('styles')
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/portada.css') }}?v={{ filemtime(public_path('css/portada.css')) }}">
@endpush

@php
  $px = fn (string $nombre) => asset("images/pixel/{$nombre}.png");

  $confianza = [
      ['corazon', '100% CONFIDENCIAL'],
      ['robot', 'GRATUITO Y SIN COSTO'],
      ['libro-violeta', 'BASADO EN DBT'],
      ['sol-rojo', 'DISPONIBLE 24/7'],
      ['puerta', 'MÉXICO Y LATAM'],
      ['escudo-corazon', 'ESPACIO SEGURO Y ANÓNIMO'],
      ['pluma', 'SIN JUICIOS NI ETIQUETAS'],
  ];

  // [icono, clase del icono, texto, herramienta, consejo, destino]
  $estados = [
      ['persona-violeta', '', 'Ansiedad o pánico', 'Respiración 4-7-8', 'Inhala en 4s, sostén 7s y exhala en 8s para estimular el nervio vago y calmar la taquicardia.', route('tools.respiracion')],
      ['hojas', '', 'Abrumado / estrés', 'Grounding 5-4-3-2-1', 'Enfoca tus 5 sentidos en objetos tangibles para cortar la sobrecarga mental de golpe.', route('tools.grounding')],
      ['bola-roja', '', 'Enojo o impulso', 'Técnica STOP (DBT)', 'Haz una pausa de 60 segundos antes de reaccionar. Congela tus acciones y respira.', route('tools.stop')],
      ['nube', 'px-gris', 'Tristeza o cansancio', 'Registro y Validación', 'No te juzgues por sentir dolor o cansancio. Permítete descansar y registrar lo que necesitas.', route('login')],
      ['sol-rojo', 'px-dorado', 'En calma / equilibrio', 'Diario de Gratitud', 'Guarda este momento de paz en tu bitácora para recordar tu fortaleza en días nublados.', route('login')],
  ];

  // [tono, número, ilustración, insignia, título, texto, acción, destino]
  $herramientas = [
      ['verde', '01', 'hojas', 'Fisiológico', 'Respira Conmigo (4-7-8)', 'Estimula el nervio vago y disminuye la adrenalina en solo 2 minutos con ritmo visual guiado y biorregulación consciente.', 'Iniciar respiración guiada', route('tools.respiracion')],
      ['violeta', '02', 'rocas', 'Sensorial', 'Grounding 5-4-3-2-1', 'Desactiva la respuesta de pánico anclando tu corteza prefrontal en tus 5 sentidos reales paso a paso.', 'Abrir asistente sensorial', route('tools.grounding')],
      ['rojo', '03', 'stop', 'Tolerancia al malestar', 'Técnica STOP (DBT)', 'Para momentos de enojo o impulsividad: Stop, Take a breath, Observe y Proceed con mente sabia.', 'Aprender técnica STOP', route('tools.stop')],
  ];
@endphp

@section('content')
{{-- ════ PORTADA (ITEM 2): mensaje, accesos y botón de crisis ════ --}}
<section class="inicio-hero">
  <div class="inicio-hero-fondo" aria-hidden="true">
    <span class="inicio-aurora inicio-aurora-1"></span>
    <span class="inicio-aurora inicio-aurora-2"></span>
    <span class="inicio-aurora inicio-aurora-3"></span>
  </div>

  <div class="container inicio-hero-grid">
    <div class="inicio-hero-texto">
      <span class="inicio-eyebrow" data-aparecer>
        <span class="inicio-punto-vivo" aria-hidden="true"></span>
        Acompañamiento emocional · gratuito y confidencial
      </span>

      <h1 class="inicio-titulo" data-aparecer="palabras">
        Un árbol siempre está ahí. <em>Nosotros también.</em>
      </h1>

      <p class="inicio-bajada" data-aparecer>
        Herramientas de regulación emocional basadas en DBT, tu plan de seguridad personal y líneas de ayuda las 24 horas. Un espacio seguro y sin juicios para México y Latinoamérica.
      </p>

      <div class="inicio-acciones" data-aparecer-grupo>
        <a href="{{ route('sientes') }}" class="inicio-btn inicio-btn-vidrio inicio-btn-vidrio-menta">
          <i class="fa-solid fa-heart-pulse"></i>
          <span>¿Cómo te sientes?</span>
        </a>
        <a href="{{ route('tools.respiracion') }}" class="inicio-btn inicio-btn-vidrio">
          <i class="fa-solid fa-lungs"></i>
          <span>Respira Conmigo</span>
        </a>
        @auth
          <a href="{{ route('dashboard') }}" class="inicio-btn inicio-btn-verde">
            <i class="fa-solid fa-user-shield"></i>
            <span>Entrar a Mi Espacio</span>
          </a>
        @else
          <a href="{{ route('login') }}" class="inicio-btn inicio-btn-verde">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>Acceder / Iniciar Sesión</span>
          </a>
        @endauth
      </div>
    </div>

    <div class="inicio-hero-visual">
      <div class="inicio-escena" data-paralaje="suave">
        <div class="inicio-escena-cielo" aria-hidden="true">
          <img src="{{ $px('destello-1') }}" alt="" data-destello style="left: 12%; top: 14%; width: 20px;">
          <img src="{{ $px('destello-3') }}" alt="" data-destello style="right: 14%; top: 10%; width: 16px;">
          <img src="{{ $px('destello-2') }}" alt="" data-destello style="right: 22%; top: 40%; width: 14px;">
          <img src="{{ $px('destello-4') }}" alt="" data-destello style="left: 20%; top: 46%; width: 13px;">
          <img src="{{ $px('hojas-cayendo') }}" alt="" data-caer style="left: 30%; width: 22px;">
          <img src="{{ $px('hojas-cayendo') }}" alt="" data-caer style="left: 62%; width: 18px;">
          <img src="{{ $px('hojas-cayendo') }}" alt="" data-caer style="left: 46%; width: 16px;">
        </div>

        <div class="inicio-arbol">
          <x-logo :size="270" data-flotar alt="Árbol de A tu lado" />
          <span class="inicio-suelo" aria-hidden="true"></span>
        </div>

        {{-- Botón de crisis (OBLIGATORIO): sin animación de entrada, visible desde el primer instante --}}
        <div class="inicio-crisis">
          <a href="tel:8009112000" class="panic-button-circle" title="Llamar inmediatamente a Línea de la Vida">
            <span class="plus-icon">+</span>
            <span class="phone-num">800 911 2000</span>
            <span class="sub-tag-1">LÍNEA DE LA VIDA</span>
            <span class="sub-tag-2">GRATUITA · 24H</span>
          </a>
          <div class="inicio-crisis-texto">
            <strong>¿Estás en crisis?</strong>
            <span>Presiona el botón. Estamos aquí.</span>
            <a href="{{ route('crisis') }}">Ver todas las líneas de ayuda <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ════ FRANJA DE CONFIANZA (ITEM 1) ════ --}}
  <div class="trust-marquee-wrapper portada-franja" title="Desliza para pausar">
    <div class="trust-marquee-track">
      @foreach([false, true] as $copia)
        <div class="marquee-group" @if($copia) aria-hidden="true" @endif>
          @foreach($confianza as [$icono, $texto])
            <div class="marquee-item">
              <img src="{{ $px($icono) }}" alt="" class="px-ico" width="20" height="20">
              <span>{{ $texto }}</span>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════ AUTOEXPLORACIÓN RÁPIDA ════ --}}
<section class="inicio-seccion inicio-calma">
  <img src="{{ $px('destello-2') }}" alt="" class="portada-deco" data-destello style="left: 3.5%; top: 18%; width: 20px;">
  <img src="{{ $px('hojas-cayendo') }}" alt="" class="portada-deco" data-mecer style="left: 2.5%; top: 58%; width: 30px;">
  <img src="{{ $px('destello-1') }}" alt="" class="portada-deco" data-destello style="right: 4%; top: 12%; width: 22px;">
  <img src="{{ $px('hojas-cayendo') }}" alt="" class="portada-deco" data-mecer style="right: 2.5%; top: 70%; width: 28px; transform: scaleX(-1);">

  <div class="container inicio-calma-grid">
    <div class="inicio-calma-texto">
      <span class="inicio-etiqueta" data-aparecer>
        Autoexploración rápida
        <img src="{{ $px('hojas-brote') }}" alt="" width="26" height="19">
      </span>
      <h2 class="inicio-h2" data-aparecer>¿Cómo late tu corazón <em>en este momento?</em></h2>
      <p class="inicio-sub" data-aparecer>Selecciona tu estado para desplegar una técnica de autorregulación inmediata.</p>

      <div class="inicio-estados" data-aparecer-grupo>
        @foreach($estados as [$icono, $claseIcono, $texto, $herramienta, $consejo, $destino])
          <button type="button" class="inicio-estado home-emo-btn" data-tool="{{ $herramienta }}" data-tip="{{ $consejo }}" data-url="{{ $destino }}">
            <span class="inicio-estado-ico"><img src="{{ $px($icono) }}" alt="" width="22" height="22" @class([$claseIcono])></span>
            <span>{{ $texto }}</span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </button>
        @endforeach
      </div>

      {{-- Lo muestra main.js al elegir un estado --}}
      <div id="homeRecBox" style="display: none;">
        <div class="inicio-recomendacion">
          <div>
            <span class="inicio-etiqueta inicio-etiqueta-clara">Para este momento</span>
            <h3 id="homeRecTitle"></h3>
            <p id="homeRecTip"></p>
          </div>
          <a id="homeRecUrl" href="#" class="inicio-btn inicio-btn-menta inicio-btn-chico">
            <span>Abrir herramienta</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="inicio-nota" data-aparecer>
        <img src="{{ $px('corazon') }}" alt="" width="28" height="22">
        <p>Este espacio te ofrece compañía y herramientas de autocuidado.<br>No reemplaza la atención profesional en salud mental.</p>
      </div>
    </div>

    <figure class="inicio-calma-ilustracion" data-aparecer="suave">
      <div class="portada-nubes" aria-hidden="true"></div>
      <picture>
        <source srcset="{{ asset('images/pixel/banca.webp') }}" type="image/webp">
        <img src="{{ $px('banca') }}" alt="Dos personas conversan con calma en una banca del parque" width="1100" height="608" class="portada-banca">
      </picture>
      <p class="portada-manuscrita" data-aparecer="escribir" data-retraso="0.5">Estás<br>aquí.<br>Eso ya<br>es mucho.</p>
      <img src="{{ $px('corazon') }}" alt="" class="portada-manuscrita-corazon" data-aparecer data-retraso="1.9" width="22" height="17">
    </figure>
  </div>
</section>

{{-- ════ HERRAMIENTAS DE REGULACIÓN ════ --}}
<section class="inicio-seccion inicio-herramientas">
  <img src="{{ $px('margaritas') }}" alt="" class="portada-deco portada-deco-amplia" style="left: 0; bottom: -4px; width: 150px;">
  <img src="{{ $px('lavandas') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 0; bottom: -4px; width: 150px;">

  <div class="container">
    <header class="inicio-encabezado" data-aparecer>
      <span class="inicio-etiqueta">
        <img src="{{ $px('hojas') }}" alt="" width="22" height="21" class="portada-espejo">
        Herramientas de regulación
        <img src="{{ $px('hojas') }}" alt="" width="22" height="21">
      </span>
      <h2 class="inicio-h2">Ciencia aplicada para <em>calmar tu mente</em></h2>
      <p class="inicio-sub">Técnicas terapéuticas validadas por la Terapia Dialéctico Conductual (DBT) y la neurobiología.</p>
    </header>

    <div class="inicio-tarjetas" data-aparecer-grupo>
      @foreach($herramientas as [$tono, $numero, $ilustracion, $insignia, $titulo, $texto, $accion, $destino])
        <a href="{{ $destino }}" class="inicio-tarjeta tono-{{ $tono }}" data-foco>
          <div class="inicio-tarjeta-arte">
            <span class="inicio-tarjeta-num">{{ $numero }}</span>
            <img src="{{ $px($ilustracion) }}" alt="">
          </div>
          <div class="inicio-tarjeta-cuerpo">
            <span class="inicio-insignia">{{ $insignia }}</span>
            <h3>{{ $titulo }}</h3>
            <p>{{ $texto }}</p>
            <span class="inicio-tarjeta-accion">{{ $accion }} <i class="fa-solid fa-arrow-right"></i></span>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>

{{-- ════ REVISTA CIENTÍFICA Y APORTES PROFESIONALES ════ --}}
<section class="inicio-seccion inicio-aportes">
  <img src="{{ $px('destello-3') }}" alt="" class="portada-deco portada-deco-amplia" data-destello style="left: 3.8%; top: 20%; width: 16px;">
  <img src="{{ $px('hojas-brote') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 3%; top: 12%; width: 42px;">
  <img src="{{ $px('destello-4') }}" alt="" class="portada-deco portada-deco-amplia" data-destello style="right: 4%; top: 64%; width: 15px;">

  <div class="container">
    <header class="inicio-encabezado inicio-encabezado-fila" data-aparecer>
      <div>
        <span class="inicio-etiqueta">Evidencia y psicoeducación</span>
        <h2 class="inicio-h2">Aportes de la <em>comunidad profesional</em></h2>
        <p class="inicio-sub">Artículos clínicos, investigaciones y técnicas basadas en evidencia redactadas por terapeutas y especialistas en salud mental.</p>
      </div>

      <div class="inicio-aportes-acciones">
        <a href="{{ route('revista.index') }}" class="inicio-btn inicio-btn-contorno inicio-btn-chico">
          <img src="{{ $px('libro-verde') }}" alt="" width="18" height="18">
          <span>Explorar la revista</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        @auth
          @if(auth()->user()->isProfessional())
            <a href="{{ route('revista.create') }}" class="inicio-btn inicio-btn-verde inicio-btn-chico">
              <img src="{{ $px('lapiz') }}" alt="" width="18" height="18">
              <span>Publicar artículo</span>
            </a>
          @endif
        @endauth
      </div>
    </header>

    <div class="inicio-articulos" data-aparecer-grupo>
      @forelse($latestArticles as $art)
        @php $avatar = 'avatar-' . (crc32((string) $art->author_name) % 3 + 1); @endphp
        <article class="inicio-articulo" data-foco>
          <div class="inicio-articulo-meta">
            <span class="inicio-categoria">{{ $art->category }}</span>
            <span class="inicio-lectura">
              <img src="{{ $px('ojo') }}" alt="" width="16" height="11">
              {{ $art->read_time }}
            </span>
          </div>

          <h3><a href="{{ route('revista.show', $art->slug) }}">{{ $art->title }}</a></h3>
          <p>{{ Str::limit($art->excerpt, 120) }}</p>

          <div class="inicio-articulo-pie">
            <div class="inicio-autor">
              <img src="{{ $px($avatar) }}" alt="" class="inicio-avatar" width="38" height="44">
              <div style="min-width: 0;">
                <div class="inicio-autor-nombre">
                  <span>{{ $art->author_name }}</span>
                  <x-verified-badge size="16" :pixel="true" />
                </div>
                <div class="inicio-autor-rol">{{ Str::limit($art->author_role, 24) }}</div>
              </div>
            </div>
            <a href="{{ route('revista.show', $art->slug) }}" class="inicio-leer" aria-label="Leer «{{ $art->title }}»">
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </article>
      @empty
        <div class="inicio-articulo inicio-articulo-vacio">
          <img src="{{ $px('pluma') }}" alt="" width="48" height="39">
          <h3>Espacio abierto para profesionales</h3>
          <p>Sé el primero en compartir psicoeducación basada en evidencia con la comunidad.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

{{-- ════ LLAMADO FINAL ════ --}}
<section class="inicio-seccion inicio-cierre">
  <div class="container">
    <div class="inicio-cierre-tarjeta" data-aparecer>
      <div class="inicio-hero-fondo" aria-hidden="true">
        <span class="inicio-aurora inicio-aurora-1"></span>
        <span class="inicio-aurora inicio-aurora-2"></span>
      </div>
      <img src="{{ $px('margaritas') }}" alt="" class="inicio-cierre-flor" style="left: -6px;">
      <img src="{{ $px('lavandas') }}" alt="" class="inicio-cierre-flor" style="right: -6px;">

      <div class="inicio-cierre-contenido">
        <span class="inicio-etiqueta inicio-etiqueta-clara">Tu espacio de calma</span>
        <h2>Comienza a cultivar tu <em>paz interior</em> hoy</h2>
        <p>Regístrate para llevar tu diario de bienestar, registrar momentos de gratitud y tener listo tu Plan de Seguridad personal.</p>
        <div class="inicio-acciones inicio-acciones-centro">
          @auth
            <a href="{{ route('dashboard') }}" class="inicio-btn inicio-btn-menta">
              <i class="fa-solid fa-user-shield"></i>
              <span>Entrar a Mi Espacio Seguro</span>
            </a>
          @else
            <a href="{{ route('register') }}" class="inicio-btn inicio-btn-menta">
              <i class="fa-solid fa-user-plus"></i>
              <span>Crear cuenta gratis</span>
            </a>
            <a href="{{ route('login') }}" class="inicio-btn inicio-btn-vidrio">
              <i class="fa-solid fa-arrow-right-to-bracket"></i>
              <span>Iniciar sesión</span>
            </a>
          @endauth
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
