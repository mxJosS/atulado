@extends('layouts.guest')

@section('title', 'A tu lado — Apoyo Emocional, Herramientas DBT y Prevención')

@section('content')
<!-- ════ HERO SECTION OFICIAL (ITEM 2) ════ -->
<section class="hero-forest-green" style="background: #1E2A22 !important; color: #FFFFFF !important; padding: clamp(4rem, 7vw, 6.5rem) 1.5rem clamp(4.5rem, 8vw, 6.5rem); position: relative; overflow: hidden; text-align: center;">
  
  <!-- Subtle Ambient Halos -->
  <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px); background-size: 28px 28px; pointer-events: none;"></div>
  <div style="position: absolute; width: 650px; height: 650px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, rgba(46, 93, 75, 0.12) 50%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 860px; margin: 0 auto;">

    <!-- Tree Pixel Art Official Logo -->
    <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
      <svg class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 12px 24px rgba(0,0,0,0.4));">
        <!-- Soil & Ground -->
        <rect x="0" y="27" width="32" height="5" fill="#1E4A25"/>
        <rect x="0" y="26" width="32" height="1" fill="#2D6B3A"/>
        <!-- Trunk -->
        <rect x="14" y="19" width="4" height="8" fill="#6B3A1F"/>
        <rect x="13" y="21" width="1" height="5" fill="#4A2710"/>
        <rect x="17" y="22" width="1" height="4" fill="#4A2710"/>
        <rect x="10" y="20" width="4" height="2" fill="#6B3A1F"/>
        <rect x="18" y="21" width="4" height="2" fill="#6B3A1F"/>
        <!-- Foliage Outer Dark -->
        <rect x="7" y="6" width="18" height="14" fill="#2D6B3A"/>
        <rect x="5" y="8" width="22" height="12" fill="#2D6B3A"/>
        <!-- Foliage Medium & Light -->
        <rect x="8" y="7" width="16" height="12" fill="#3D8C4F"/>
        <rect x="10" y="5" width="12" height="14" fill="#5AB56E"/>
        <rect x="12" y="4" width="8" height="3" fill="#3D8C4F"/>
        <rect x="11" y="7" width="5" height="3" fill="#7FD68A"/>
        <rect x="10" y="10" width="3" height="2" fill="#A8E6C0"/>
        <rect x="18" y="9" width="3" height="2" fill="#A8E6C0"/>
        <!-- Red Apples -->
        <rect x="9" y="12" width="2" height="2" fill="#C0392B"/>
        <rect x="9" y="11" width="2" height="1" fill="#C0392B"/>
        <rect x="20" y="11" width="2" height="2" fill="#C0392B"/>
        <rect x="20" y="10" width="2" height="1" fill="#C0392B"/>
        <rect x="15" y="15" width="2" height="2" fill="#C0392B"/>
        <rect x="15" y="14" width="2" height="1" fill="#C0392B"/>
        <rect x="13" y="9" width="2" height="2" fill="#C0392B"/>
        <rect x="13" y="8" width="2" height="1" fill="#C0392B"/>
      </svg>
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
      <a href="tel:8002900024" class="panic-button-circle" title="Llamar inmediatamente a Línea de la Vida">
        <span class="plus-icon">+</span>
        <span class="phone-num">800 290 0024</span>
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

<!-- ════ 1. INFINITE TRUST MARQUEE (ITEM 1) ════ -->
<div class="trust-marquee-wrapper" title="Desliza para pausar">
  <div class="trust-marquee-track">
    <!-- First sequence -->
    <div class="marquee-group">
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-lock" style="color: #3D7A5F;"></i>
        <span>100% CONFIDENCIAL</span>
      </div>
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-hand-holding-heart" style="color: #3D7A5F;"></i>
        <span>GRATUITO Y SIN COSTO</span>
      </div>
      <div class="marquee-item" style="color: #5B4A8A;">
        <i class="fa-solid fa-spa" style="color: #5B4A8A;"></i>
        <span>BASADO EN DBT</span>
      </div>
      <div class="marquee-item" style="color: #A93226;">
        <i class="fa-solid fa-clock" style="color: #A93226;"></i>
        <span>DISPONIBLE 24/7</span>
      </div>
      <div class="marquee-item" style="color: #8A7332;">
        <i class="fa-solid fa-earth-americas" style="color: #8A7332;"></i>
        <span>MÉXICO Y LATAM</span>
      </div>
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-shield-halved" style="color: #3D7A5F;"></i>
        <span>ESPACIO SEGURO Y ANÓNIMO</span>
      </div>
      <div class="marquee-item" style="color: #5B4A8A;">
        <i class="fa-solid fa-leaf" style="color: #5B4A8A;"></i>
        <span>SIN JUICIOS NI ETIQUETAS</span>
      </div>
    </div>

    <!-- Duplicated sequence for seamless infinite loop -->
    <div class="marquee-group" aria-hidden="true">
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-lock" style="color: #3D7A5F;"></i>
        <span>100% CONFIDENCIAL</span>
      </div>
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-hand-holding-heart" style="color: #3D7A5F;"></i>
        <span>GRATUITO Y SIN COSTO</span>
      </div>
      <div class="marquee-item" style="color: #5B4A8A;">
        <i class="fa-solid fa-spa" style="color: #5B4A8A;"></i>
        <span>BASADO EN DBT</span>
      </div>
      <div class="marquee-item" style="color: #A93226;">
        <i class="fa-solid fa-clock" style="color: #A93226;"></i>
        <span>DISPONIBLE 24/7</span>
      </div>
      <div class="marquee-item" style="color: #8A7332;">
        <i class="fa-solid fa-earth-americas" style="color: #8A7332;"></i>
        <span>MÉXICO Y LATAM</span>
      </div>
      <div class="marquee-item" style="color: #2E5D4B;">
        <i class="fa-solid fa-shield-halved" style="color: #3D7A5F;"></i>
        <span>ESPACIO SEGURO Y ANÓNIMO</span>
      </div>
      <div class="marquee-item" style="color: #5B4A8A;">
        <i class="fa-solid fa-leaf" style="color: #5B4A8A;"></i>
        <span>SIN JUICIOS NI ETIQUETAS</span>
      </div>
    </div>
  </div>
</div>

<!-- ════ QUICK INTERACTIVE EMOTIONAL CHECK ════ -->
<section style="background: #F8FAF9; padding: 4.5rem 1.5rem; border-bottom: 1.5px solid #DCE8E0;">
  <div class="container">
    <div style="text-align: center; max-width: 720px; margin: 0 auto 2.5rem;">
      <span class="mono-tag" style="color: #2E5D4B;">Autoexploración Rápida</span>
      <h2 style="font-size: clamp(1.6rem, 3vw, 2.3rem); margin-top: 0.25rem; margin-bottom: 0.5rem; color: #1A2620;">
        ¿Cómo late tu corazón en este momento?
      </h2>
      <p style="color: #556860; font-size: 0.94rem;">
        Selecciona tu estado para desplegar una técnica de autorregulación inmediata.
      </p>
    </div>

    <!-- Quick Buttons with Icons -->
    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1.75rem;">
      <button class="btn btn-secondary home-emo-btn" data-tool="Respiración 4-7-8" data-tip="Inhala en 4s, sostén 7s y exhala en 8s para estimular el nervio vago y calmar la taquicardia." data-url="{{ route('tools.respiracion') }}" style="border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-water" style="color: #5B4A8A;"></i>
        <span>Ansiedad o Pánico</span>
      </button>
      <button class="btn btn-secondary home-emo-btn" data-tool="Grounding 5-4-3-2-1" data-tip="Enfoca tus 5 sentidos en objetos tangibles para cortar la sobrecarga mental de golpe." data-url="{{ route('tools.grounding') }}" style="border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-wind" style="color: #3D7A5F;"></i>
        <span>Abrumado / Estrés</span>
      </button>
      <button class="btn btn-secondary home-emo-btn" data-tool="Técnica STOP (DBT)" data-tip="Haz una pausa de 60 segundos antes de reaccionar. Congela tus acciones y respira." data-url="{{ route('tools.stop') }}" style="border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-circle-pause" style="color: #C0392B;"></i>
        <span>Enojo o Impulso</span>
      </button>
      <button class="btn btn-secondary home-emo-btn" data-tool="Registro y Validación" data-tip="No te juzgues por sentir dolor o cansancio. Permítete descansar y registrar lo que necesitas." data-url="{{ route('login') }}" style="border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-cloud-rain" style="color: #556860;"></i>
        <span>Tristeza o Cansancio</span>
      </button>
      <button class="btn btn-secondary home-emo-btn" data-tool="Diario de Gratitud" data-tip="Guarda este momento de paz en tu bitácora para recordar tu fortaleza en días nublados." data-url="{{ route('login') }}" style="border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-sun" style="color: #8A7332;"></i>
        <span>En Calma / Equilibrio</span>
      </button>
    </div>

    <!-- Live Preview Box -->
    <div id="homeRecBox" class="card" style="display: none; max-width: 760px; margin: 0 auto; background: #D4EDE2; border: 2px solid #5AB56E; animation: slideUpFade 0.4s ease;">
      <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
        <div>
          <span class="mono-tag" style="color: #2E5D4B;">Recomendación Inmediata</span>
          <h3 id="homeRecTitle" style="font-size: 1.35rem; margin-top: 0.2rem; color: #1A2620;"></h3>
          <p id="homeRecTip" style="font-size: 0.92rem; color: #1A2620; margin-top: 0.35rem; line-height: 1.6;"></p>
        </div>
        <div>
          <a id="homeRecUrl" href="#" class="btn btn-primary" style="gap: 6px;">
            <span>Abrir herramienta</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ FEATURED DBT TOOLS ════ -->
<section style="padding: 4.5rem 1.5rem; background: #FFFFFF; border-bottom: 1.5px solid #DCE8E0;">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem;">
      <span class="mono-tag" style="color: #2E5D4B;">Herramientas de Regulación</span>
      <h2 style="font-size: clamp(1.7rem, 3vw, 2.4rem); margin-top: 0.25rem; margin-bottom: 0.5rem; color: #1A2620;">
        Ciencia aplicada para calmar tu mente
      </h2>
      <p style="color: #556860; font-size: 0.95rem;">
        Técnicas terapéuticas validadas por la Terapia Dialéctico Conductual (DBT) y la neurobiología.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
      <div class="card" style="border-top: 5px solid #2E5D4B;">
        <div class="card-body">
          <span class="badge badge-sage">Fisiológico</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.65rem; margin-bottom: 0.35rem; color: #1A2620;">Respira Conmigo (4-7-8)</h3>
          <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem;">
            Estimula el nervio vago y disminuye la adrenalina en solo 2 minutos con ritmo visual guiado y biorregulación consciente.
          </p>
          <a href="{{ route('tools.respiracion') }}" class="btn btn-primary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-lungs"></i>
            <span>Iniciar Respiración Guiada</span>
          </a>
        </div>
      </div>

      <div class="card" style="border-top: 5px solid #5B4A8A;">
        <div class="card-body">
          <span class="badge badge-violet">Sensorial</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.65rem; margin-bottom: 0.35rem; color: #1A2620;">Grounding 5-4-3-2-1</h3>
          <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem;">
            Desactiva la respuesta de pánico anclando tu corteza prefrontal en tus 5 sentidos reales paso a paso.
          </p>
          <a href="{{ route('tools.grounding') }}" class="btn btn-secondary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-hand-holding-heart" style="color: #5B4A8A;"></i>
            <span>Abrir Asistente Sensorial</span>
          </a>
        </div>
      </div>

      <div class="card" style="border-top: 5px solid #C0392B;">
        <div class="card-body">
          <span class="badge badge-crisis">Tolerancia al Malestar</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.65rem; margin-bottom: 0.35rem; color: #1A2620;">Técnica STOP (DBT)</h3>
          <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem;">
            Para momentos de enojo o impulsividad: Stop, Take a breath, Observe y Proceed con mente sabia.
          </p>
          <a href="{{ route('tools.stop') }}" class="btn btn-secondary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-circle-pause" style="color: #C0392B;"></i>
            <span>Aprender Técnica STOP</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ 2. SECCIÓN REVISTA CIENTÍFICA & APORTES PROFESIONALES (ITEM 2) ════ -->
<section style="padding: 4.5rem 1.5rem; background: #F8FAF9; border-bottom: 1.5px solid #DCE8E0;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="mono-tag" style="color: #2E5D4B;">— EVIDENCIA & PSICOEDUCACIÓN</span>
        <h2 style="font-size: clamp(1.6rem, 3vw, 2.3rem); margin-top: 0.25rem; margin-bottom: 0.35rem; color: #1A2620;">
          Aportes de la Comunidad Profesional
        </h2>
        <p style="color: #556860; font-size: 0.94rem; max-width: 600px;">
          Artículos clínicos, investigaciones y técnicas basadas en evidencia redactadas por terapeutas y especialistas en salud mental.
        </p>
      </div>

      <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('revista.index') }}" class="btn btn-secondary btn-sm" style="gap: 6px;">
          <i class="fa-solid fa-newspaper"></i>
          <span>Explorar Revista Completa</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        @auth
          @if(auth()->user()->isProfessional())
            <a href="{{ route('revista.create') }}" class="btn btn-primary btn-sm" style="gap: 6px;">
              <i class="fa-solid fa-pen-nib"></i>
              <span>Publicar Artículo</span>
            </a>
          @endif
        @endauth
      </div>
    </div>

    <!-- Article Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
      @forelse($latestArticles as $art)
        <div class="article-forum-card">
          <div style="padding: 1.5rem; display: flex; flex-direction: column; flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <span class="badge-blue-category">{{ $art->category }}</span>
              <span class="mono-tag" style="color: #556860; display: flex; align-items: center; gap: 4px;">
                <i class="fa-regular fa-clock"></i> {{ $art->read_time }}
              </span>
            </div>

            <h3 style="font-size: 1.2rem; margin-bottom: 0.65rem; line-height: 1.35;">
              <a href="{{ route('revista.show', $art->slug) }}" style="color: #1A2620; text-decoration: none;">
                {{ $art->title }}
              </a>
            </h3>

            <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem; flex: 1;">
              {{ Str::limit($art->excerpt, 120) }}
            </p>

            <div style="padding-top: 0.85rem; border-top: 1px solid #DCE8E0; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
              <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #1877F2 0%, #0D6EFD 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; flex-shrink: 0;">
                  {{ strtoupper(substr($art->author_name, 0, 1)) }}
                </div>
                <div style="font-size: 0.78rem; white-space: nowrap; overflow: visible;">
                  <div style="font-weight: 700; color: #1A2620; display: flex; align-items: center; gap: 5px;">
                    <span>{{ $art->author_name }}</span>
                    <x-verified-badge size="16" />
                  </div>
                  <div style="color: #556860; font-size: 0.72rem;">{{ Str::limit($art->author_role, 24) }}</div>
                </div>
              </div>

              <a href="{{ route('revista.show', $art->slug) }}" class="btn btn-sm btn-secondary" style="font-size: 0.78rem; padding: 0.35rem 0.65rem; white-space: nowrap; gap: 4px; color: #1877F2; border-color: rgba(24, 119, 242, 0.3);">
                <span>Leer</span>
                <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1.5rem;">
          <i class="fa-solid fa-feather" style="font-size: 2.2rem; color: #8EADA4;"></i>
          <h3 style="margin-top: 0.75rem; font-size: 1.15rem;">Espacio abierto para profesionales</h3>
          <p style="color: #556860; font-size: 0.88rem; margin-top: 0.35rem;">Sé el primero en compartir psicoeducación basada en evidencia con la comunidad.</p>
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
