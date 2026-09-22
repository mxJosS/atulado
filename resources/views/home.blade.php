@extends('layouts.guest')

@section('title', 'A tu lado — Apoyo Emocional, Herramientas DBT y Prevención')

@push('styles')
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/portada.css') }}?v={{ filemtime(public_path('css/portada.css')) }}">
@endpush

@section('content')
<!-- ════ HERO SECTION OFICIAL (ITEM 2) ════ -->
<section class="hero-forest-green" style="background: #1E2A22 !important; color: #FFFFFF !important; padding: clamp(4rem, 7vw, 6.5rem) 1.5rem clamp(4.5rem, 8vw, 6.5rem); position: relative; overflow: hidden; text-align: center;">
  
  <!-- Subtle Ambient Halos -->
  <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px); background-size: 28px 28px; pointer-events: none;"></div>
  <div style="position: absolute; width: 650px; height: 650px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, rgba(46, 93, 75, 0.12) 50%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 860px; margin: 0 auto;">

    <!-- Tree Pixel Art Official Logo -->
    <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
      <x-logo :size="130" style="filter: drop-shadow(0 12px 24px rgba(0,0,0,0.35));" />
    </div>

    <!-- Title 'a tu lado' with editorial italic -->
    <h1 style="color: #FFFFFF !important; font-size: clamp(2.8rem, 6.5vw, 5rem); line-height: 1.05; margin-bottom: 1.25rem; font-weight: 700; letter-spacing: -0.02em;">
      a tu <em class="editorial-italic" style="color: #A8E6C0 !important; font-weight: 400;">lado</em>
    </h1>

    <!-- Quote Subtitle -->
    <p style="font-size: clamp(1.05rem, 2.2vw, 1.25rem); color: #C8DDD1 !important; max-width: 660px; margin: 0 auto 2.5rem; line-height: 1.7; font-weight: 300;">
      Un árbol siempre está ahí — con raíces profundas, brazos abiertos y frutos que dar. Nosotros también.
    </p>

    <!-- CRISIS ROUND PANIC BUTTON (OBLIGATORIO) -->
    <div style="margin-bottom: 1.75rem; display: flex; justify-content: center;">
      <a href="tel:8009112000" class="panic-button-circle" title="Llamar inmediatamente a Línea de la Vida">
        <span class="plus-icon">+</span>
        <span class="phone-num">800 911 2000</span>
        <span class="sub-tag-1">LÍNEA DE LA VIDA</span>
        <span class="sub-tag-2">GRATUITA · 24H</span>
      </a>
    </div>

    <!-- Sub-caption under panic button -->
    <div style="font-family: var(--font-mono); font-size: 0.76rem; letter-spacing: 0.12em; text-transform: uppercase; color: #C8DDD1; opacity: 0.85; margin-bottom: 2.75rem;">
      SI ESTÁS EN CRISIS, PRESIONA EL BOTÓN. ESTAMOS AQUÍ.
    </div>

    <!-- CTA ACTION BUTTONS -->
    <div style="display: flex; gap: 0.85rem; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('sientes') }}" class="btn btn-outline-white btn-lg" style="gap: 8px;">
        <i class="fa-solid fa-heart-pulse" style="color: #A8E6C0;"></i>
        <span>¿Cómo te sientes?</span>
      </a>
      
      <a href="{{ route('tools.respiracion') }}" class="btn btn-outline-white btn-lg" style="gap: 8px;">
        <i class="fa-solid fa-lungs" style="color: #A8E6C0;"></i>
        <span>Respira Conmigo</span>
      </a>

      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" style="gap: 8px;">
          <i class="fa-solid fa-user-shield"></i>
          <span>Entrar a Mi Espacio</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg" style="gap: 8px;">
          <i class="fa-solid fa-arrow-right-to-bracket"></i>
          <span>Acceder / Iniciar Sesión</span>
        </a>
      @endauth
    </div>

  </div>
</section>

@php
  $px = fn (string $nombre) => asset("images/pixel/{$nombre}.png");
  $confianza = [
      ['corazon', '100% CONFIDENCIAL', '#2E5D4B'],
      ['robot', 'GRATUITO Y SIN COSTO', '#2E5D4B'],
      ['libro-violeta', 'BASADO EN DBT', '#5B4A8A'],
      ['sol-rojo', 'DISPONIBLE 24/7', '#A93226'],
      ['puerta', 'MÉXICO Y LATAM', '#8A7332'],
      ['escudo-corazon', 'ESPACIO SEGURO Y ANÓNIMO', '#2E5D4B'],
      ['pluma', 'SIN JUICIOS NI ETIQUETAS', '#5B4A8A'],
  ];
@endphp

<!-- ════ 1. INFINITE TRUST MARQUEE (ITEM 1) ════ -->
<div class="trust-marquee-wrapper portada-franja" title="Desliza para pausar">
  <div class="trust-marquee-track">
    @foreach([false, true] as $copia)
      <div class="marquee-group" @if($copia) aria-hidden="true" @endif>
        @foreach($confianza as [$icono, $texto, $color])
          <div class="marquee-item" style="color: {{ $color }};">
            <img src="{{ $px($icono) }}" alt="" class="px-ico" width="20" height="20">
            <span>{{ $texto }}</span>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</div>

<!-- ════ QUICK INTERACTIVE EMOTIONAL CHECK ════ -->
<section class="portada-calma">
  <img src="{{ $px('destello-2') }}" alt="" class="portada-deco" style="left: 3.5%; top: 34%; width: 20px;">
  <img src="{{ $px('hojas-cayendo') }}" alt="" class="portada-deco" style="left: 3%; top: 50%; width: 30px;">
  <img src="{{ $px('destello-4') }}" alt="" class="portada-deco" style="left: 4.5%; top: 72%; width: 15px;">
  <img src="{{ $px('destello-1') }}" alt="" class="portada-deco" style="right: 5.5%; top: 9%; width: 22px;">
  <img src="{{ $px('destello-3') }}" alt="" class="portada-deco" style="right: 3%; top: 17%; width: 17px;">
  <img src="{{ $px('hojas-cayendo') }}" alt="" class="portada-deco" style="right: 2.5%; top: 64%; width: 28px; transform: scaleX(-1);">

  <div class="container portada-calma-grid">
    <div class="portada-calma-texto">
      <span class="mono-tag portada-etiqueta">
        Autoexploración Rápida
        <img src="{{ $px('hojas-brote') }}" alt="" width="26" height="19">
      </span>
      <h2>¿Cómo late tu corazón en este momento?</h2>
      <p class="portada-sub">Selecciona tu estado para desplegar una técnica de autorregulación inmediata.</p>

      <!-- Quick Buttons with Icons -->
      <div class="portada-estados">
        <button type="button" class="portada-chip home-emo-btn" data-tool="Respiración 4-7-8" data-tip="Inhala en 4s, sostén 7s y exhala en 8s para estimular el nervio vago y calmar la taquicardia." data-url="{{ route('tools.respiracion') }}">
          <img src="{{ $px('persona-violeta') }}" alt="" width="20" height="20">
          <span>Ansiedad o Pánico</span>
        </button>
        <button type="button" class="portada-chip home-emo-btn" data-tool="Grounding 5-4-3-2-1" data-tip="Enfoca tus 5 sentidos en objetos tangibles para cortar la sobrecarga mental de golpe." data-url="{{ route('tools.grounding') }}">
          <img src="{{ $px('hojas') }}" alt="" width="20" height="20">
          <span>Abrumado / Estrés</span>
        </button>
        <button type="button" class="portada-chip home-emo-btn" data-tool="Técnica STOP (DBT)" data-tip="Haz una pausa de 60 segundos antes de reaccionar. Congela tus acciones y respira." data-url="{{ route('tools.stop') }}">
          <img src="{{ $px('bola-roja') }}" alt="" width="20" height="20">
          <span>Enojo o Impulso</span>
        </button>
        <button type="button" class="portada-chip home-emo-btn" data-tool="Registro y Validación" data-tip="No te juzgues por sentir dolor o cansancio. Permítete descansar y registrar lo que necesitas." data-url="{{ route('login') }}">
          <img src="{{ $px('nube') }}" alt="" width="20" height="20" class="px-gris">
          <span>Tristeza o Cansancio</span>
        </button>
        <button type="button" class="portada-chip home-emo-btn" data-tool="Diario de Gratitud" data-tip="Guarda este momento de paz en tu bitácora para recordar tu fortaleza en días nublados." data-url="{{ route('login') }}">
          <img src="{{ $px('sol-rojo') }}" alt="" width="20" height="20" class="px-dorado">
          <span>En Calma / Equilibrio</span>
        </button>
      </div>

      <!-- Live Preview Box -->
      <div id="homeRecBox" class="card" style="display: none; margin-bottom: 1.25rem; background: #D4EDE2; border: 2px solid #5AB56E; animation: slideUpFade 0.4s ease;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
          <div>
            <span class="mono-tag" style="color: #2E5D4B;">Recomendación Inmediata</span>
            <h3 id="homeRecTitle" style="font-size: 1.25rem; margin-top: 0.2rem; color: #1A2620;"></h3>
            <p id="homeRecTip" style="font-size: 0.9rem; color: #1A2620; margin-top: 0.35rem; line-height: 1.6;"></p>
          </div>
          <div>
            <a id="homeRecUrl" href="#" class="btn btn-primary" style="gap: 6px;">
              <span>Abrir herramienta</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="portada-nota">
        <img src="{{ $px('corazon') }}" alt="" width="28" height="22">
        <p>Este espacio te ofrece compañía y herramientas de autocuidado.<br>No reemplaza la atención profesional en salud mental.</p>
      </div>
    </div>

    <div class="portada-calma-ilustracion">
      <div class="portada-nubes" aria-hidden="true"></div>
      <picture>
        <source srcset="{{ asset('images/pixel/banca.webp') }}" type="image/webp">
        <img src="{{ $px('banca') }}" alt="Dos personas conversan con calma en una banca del parque" width="1100" height="608" class="portada-banca">
      </picture>
      <p class="portada-manuscrita">Estás<br>aquí.<br>Eso ya<br>es mucho.</p>
      <img src="{{ $px('corazon') }}" alt="" class="portada-manuscrita-corazon" width="22" height="17">
    </div>
  </div>
</section>

<!-- ════ FEATURED DBT TOOLS ════ -->
<section class="portada-herramientas">
  <img src="{{ $px('margaritas') }}" alt="" class="portada-deco portada-deco-amplia" style="left: 0; bottom: -4px; width: 150px;">
  <img src="{{ $px('lavandas') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 0; bottom: -4px; width: 150px;">

  <div class="container">
    <div class="portada-encabezado-centro">
      <span class="mono-tag portada-etiqueta">
        <img src="{{ $px('hojas') }}" alt="" width="22" height="21" class="portada-espejo">
        Herramientas de Regulación
        <img src="{{ $px('hojas') }}" alt="" width="22" height="21">
      </span>
      <h2>Ciencia aplicada para calmar tu mente</h2>
      <p>Técnicas terapéuticas validadas por la Terapia Dialéctico Conductual (DBT) y la neurobiología.</p>
    </div>

    <div class="portada-tarjetas">
      <article class="portada-tarjeta verde">
        <img src="{{ $px('hojas') }}" alt="" class="portada-tarjeta-ilustracion">
        <span class="portada-insignia">Fisiológico</span>
        <h3>Respira Conmigo (4-7-8)</h3>
        <p>Estimula el nervio vago y disminuye la adrenalina en solo 2 minutos con ritmo visual guiado y biorregulación consciente.</p>
        <a href="{{ route('tools.respiracion') }}" class="portada-boton solido">
          <img src="{{ $px('nube') }}" alt="" width="18" height="18">
          <span>Iniciar Respiración Guiada</span>
        </a>
      </article>

      <article class="portada-tarjeta violeta">
        <img src="{{ $px('rocas') }}" alt="" class="portada-tarjeta-ilustracion">
        <span class="portada-insignia">Sensorial</span>
        <h3>Grounding 5-4-3-2-1</h3>
        <p>Desactiva la respuesta de pánico anclando tu corteza prefrontal en tus 5 sentidos reales paso a paso.</p>
        <a href="{{ route('tools.grounding') }}" class="portada-boton contorno">
          <img src="{{ $px('persona-violeta') }}" alt="" width="18" height="18">
          <span>Abrir Asistente Sensorial</span>
        </a>
      </article>

      <article class="portada-tarjeta roja">
        <img src="{{ $px('stop') }}" alt="" class="portada-tarjeta-ilustracion">
        <span class="portada-insignia">Tolerancia al Malestar</span>
        <h3>Técnica STOP (DBT)</h3>
        <p>Para momentos de enojo o impulsividad: Stop, Take a breath, Observe y Proceed con mente sabia.</p>
        <a href="{{ route('tools.stop') }}" class="portada-boton contorno">
          <img src="{{ $px('bola-roja') }}" alt="" width="18" height="18">
          <span>Aprender Técnica STOP</span>
        </a>
      </article>
    </div>
  </div>
</section>

<!-- ════ 2. SECCIÓN REVISTA CIENTÍFICA & APORTES PROFESIONALES (ITEM 2) ════ -->
<section class="portada-aportes">
  <img src="{{ $px('destello-1') }}" alt="" class="portada-deco portada-deco-amplia" style="left: 3.5%; top: 16%; width: 20px;">
  <img src="{{ $px('destello-3') }}" alt="" class="portada-deco portada-deco-amplia" style="left: 3.8%; top: 66%; width: 16px;">
  <img src="{{ $px('destello-2') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 10%; top: 9%; width: 17px;">
  <img src="{{ $px('hojas-brote') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 3%; top: 13%; width: 42px;">
  <img src="{{ $px('destello-4') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 4%; top: 62%; width: 15px;">
  <img src="{{ $px('margarita') }}" alt="" class="portada-deco portada-deco-amplia" style="left: 2.5%; bottom: 0; width: 62px;">
  <img src="{{ $px('lavanda') }}" alt="" class="portada-deco portada-deco-amplia" style="right: 2.5%; bottom: 0; width: 56px;">

  <div class="container">
    <div class="portada-aportes-cabecera">
      <div>
        <span class="mono-tag portada-etiqueta">— Evidencia &amp; Psicoeducación</span>
        <h2>Aportes de la Comunidad Profesional</h2>
        <p>Artículos clínicos, investigaciones y técnicas basadas en evidencia redactadas por terapeutas y especialistas en salud mental.</p>
      </div>

      <div class="portada-aportes-acciones">
        <a href="{{ route('revista.index') }}" class="portada-boton contorno">
          <img src="{{ $px('libro-verde') }}" alt="" width="18" height="18">
          <span>Explorar Revista Completa</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        @auth
          @if(auth()->user()->isProfessional())
            <a href="{{ route('revista.create') }}" class="portada-boton solido">
              <img src="{{ $px('lapiz') }}" alt="" width="18" height="18">
              <span>Publicar Artículo</span>
            </a>
          @endif
        @endauth
      </div>
    </div>

    <!-- Article Cards Grid -->
    <div class="portada-articulos">
      @forelse($latestArticles as $art)
        @php $avatar = 'avatar-' . (crc32((string) $art->author_name) % 3 + 1); @endphp
        <article class="portada-articulo">
          <div class="portada-articulo-meta">
            <span class="badge-blue-category">{{ $art->category }}</span>
            <span class="portada-lectura">
              <img src="{{ $px('ojo') }}" alt="" width="16" height="11">
              {{ $art->read_time }}
            </span>
          </div>

          <h3>
            <a href="{{ route('revista.show', $art->slug) }}">{{ $art->title }}</a>
          </h3>

          <p>{{ Str::limit($art->excerpt, 120) }}</p>

          <div class="portada-articulo-pie">
            <div class="portada-autor">
              <img src="{{ $px($avatar) }}" alt="" class="portada-avatar" width="38" height="44">
              <div style="min-width: 0;">
                <div class="portada-autor-nombre">
                  <span>{{ $art->author_name }}</span>
                  <x-verified-badge size="16" :pixel="true" />
                </div>
                <div class="portada-autor-rol">{{ Str::limit($art->author_role, 24) }}</div>
              </div>
            </div>

            <a href="{{ route('revista.show', $art->slug) }}" class="portada-leer">
              <span>Leer</span>
              <img src="{{ $px('flecha') }}" alt="" width="16" height="15">
            </a>
          </div>
        </article>
      @empty
        <div class="portada-articulo" style="grid-column: 1 / -1; text-align: center; align-items: center; padding: 3rem 1.5rem;">
          <img src="{{ $px('pluma') }}" alt="" width="48" height="39">
          <h3 style="margin-top: 0.75rem;">Espacio abierto para profesionales</h3>
          <p style="margin-bottom: 0;">Sé el primero en compartir psicoeducación basada en evidencia con la comunidad.</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

<!-- ════ FINAL CALL TO ACTION ════ -->
<section style="background: #080C0A !important; color: #FFFFFF !important; padding: 5rem 1.5rem; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.18) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>
  <div style="position: relative; z-index: 2; max-width: 640px; margin: 0 auto;">
    <span class="mono-tag" style="color: #A8E6C0; margin-bottom: 0.5rem; display: block;">Tu espacio de calma</span>
    <h2 style="color: #FFFFFF !important; font-size: clamp(1.8rem, 4vw, 2.8rem); margin-bottom: 0.85rem; line-height: 1.2;">
      Comienza a cultivar tu <em class="editorial-italic" style="color: #A8E6C0 !important;">paz interior</em> hoy
    </h2>
    <p style="color: #C8DDD1 !important; font-size: 1rem; line-height: 1.7; margin-bottom: 2rem;">
      Regístrate para llevar tu diario de bienestar, registrar momentos de gratitud y tener listo tu Plan de Seguridad personal.
    </p>
    <div style="display: flex; gap: 0.85rem; justify-content: center; flex-wrap: wrap;">
      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg" style="gap: 8px;">
          <i class="fa-solid fa-user-shield"></i>
          <span>Entrar a Mi Espacio Seguro</span>
        </a>
      @else
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="gap: 8px;">
          <i class="fa-solid fa-user-plus"></i>
          <span>Crear cuenta gratis</span>
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-white btn-lg" style="gap: 8px;">
          <i class="fa-solid fa-arrow-right-to-bracket"></i>
          <span>Iniciar Sesión</span>
        </a>
      @endauth
    </div>
  </div>
</section>
@endsection
