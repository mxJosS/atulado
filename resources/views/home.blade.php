@extends('layouts.guest')

@section('title', 'A tu lado — Apoyo Emocional, Herramientas DBT y Prevención')

@section('content')
<!-- ════ HERO SECTION ════ -->
<section class="hero-wrapper">
  <div class="hero-grid-bg"></div>
  <div class="hero-halo"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="pulse-dot"></span> Bienestar Emocional & Salud Mental · México y LATAM
    </div>

    <!-- Animated Floating Pixel Tree -->
    <div class="tree-floating-wrapper" style="margin-bottom: 2rem;">
      <svg class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="27" width="32" height="5" fill="#2e5c30"/>
        <rect x="0" y="26" width="32" height="1" fill="#3d7a35"/>
        <rect x="14" y="19" width="4" height="8" fill="#6b4020"/>
        <rect x="13" y="21" width="1" height="5" fill="#533010"/>
        <rect x="17" y="22" width="1" height="4" fill="#533010"/>
        <rect x="10" y="20" width="4" height="2" fill="#6b4020"/>
        <rect x="18" y="21" width="4" height="2" fill="#6b4020"/>
        <rect x="7" y="6" width="18" height="14" fill="#2e5c2e"/>
        <rect x="5" y="8" width="22" height="12" fill="#3a7040"/>
        <rect x="8" y="7" width="16" height="12" fill="#4a8a50"/>
        <rect x="10" y="5" width="12" height="14" fill="#4a8a50"/>
        <rect x="12" y="4" width="8" height="3" fill="#3a7040"/>
        <rect x="11" y="7" width="5" height="3" fill="#70c070"/>
        <rect x="10" y="10" width="3" height="2" fill="#5aaa5a"/>
        <rect x="18" y="9" width="3" height="2" fill="#5aaa5a"/>
        <rect x="9" y="12" width="2" height="2" fill="#e84040"/>
        <rect x="9" y="11" width="2" height="1" fill="#c02020"/>
        <rect x="20" y="11" width="2" height="2" fill="#e84040"/>
        <rect x="20" y="10" width="2" height="1" fill="#c02020"/>
        <rect x="15" y="15" width="2" height="2" fill="#e84040"/>
        <rect x="15" y="14" width="2" height="1" fill="#c02020"/>
        <rect x="13" y="9" width="2" height="2" fill="#e84040"/>
        <rect x="13" y="8" width="2" height="1" fill="#c02020"/>
        <rect x="2" y="8" width="1" height="1" fill="rgba(255,255,180,0.5)"/>
        <rect x="28" y="5" width="1" height="1" fill="rgba(255,255,180,0.5)"/>
      </svg>
      <div class="tree-ground-shadow"></div>
    </div>

    <h1 class="hero-title">
      a tu <em>lado</em>
    </h1>

    <p class="hero-subtitle">
      Un árbol siempre está ahí: con raíces profundas, ramas protectoras y frutos que reconfortan. Nosotros también.
    </p>

    <!-- CRISIS DIRECT CALL BUTTON -->
    <div>
      <a href="tel:8002900024" class="hero-crisis-action" title="Presiona para llamar a la Línea de la Vida">
        <svg width="26" height="26" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
          <rect x="14" y="2" width="8" height="32" rx="3" fill="#ffffff"/>
          <rect x="2" y="14" width="32" height="8" rx="3" fill="#ffffff"/>
        </svg>
        <span class="hero-crisis-number">800 290 0024</span>
        <span class="hero-crisis-label">Línea de la Vida<br>Gratuita · 24h</span>
      </a>
    </div>

    <div style="font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 2rem;">
      Si estás en crisis severa, pulsa el botón rojo. Estamos aquí.
    </div>

    <!-- CTA BUTTONS -->
    <div class="hero-cta-group">
      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
          <span>Ir a Mi Espacio Seguro</span>
          <span>→</span>
        </a>
      @else
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
          <span>Comenzar mi diario gratis</span>
          <span>🌱</span>
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-white btn-lg">
          <span>Acceder con mi cuenta</span>
        </a>
      @endauth
      <a href="{{ route('sientes') }}" class="btn btn-outline-white btn-lg">
        <span>¿Cómo te sientes?</span>
      </a>
    </div>
  </div>
</section>

<!-- ════ TRUST STRIP ════ -->
<div class="trust-strip">
  <div class="trust-items-grid">
    <div class="trust-pill">
      <div class="trust-icon-circle">🔒</div>
      <span>100% Confidencial</span>
    </div>
    <div class="trust-pill">
      <div class="trust-icon-circle">🌱</div>
      <span>Gratuito y sin costo</span>
    </div>
    <div class="trust-pill">
      <div class="trust-icon-circle">🪷</div>
      <span>Basado en DBT</span>
    </div>
    <div class="trust-pill">
      <div class="trust-icon-circle">⏱️</div>
      <span>Disponible 24/7</span>
    </div>
    <div class="trust-pill">
      <div class="trust-icon-circle">🌎</div>
      <span>México y LATAM</span>
    </div>
  </div>
</div>

<!-- ════ PROBLEM & IMPACT SECTION ════ -->
<section style="background: var(--bg-canvas); padding: 5rem 1.5rem; border-bottom: 1px solid var(--ink-100);">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3.5rem; align-items: center;">
      <div>
        <span class="mono-tag" style="color: var(--sage-600);">La Realidad Invisible</span>
        <h2 style="margin-top: 0.5rem; margin-bottom: 1.25rem; font-size: clamp(2rem, 4vw, 2.8rem);">
          No tienes que cargar con todo en <em>silencio</em>
        </h2>
        <p style="font-size: 1.05rem; color: var(--ink-600); line-height: 1.8; margin-bottom: 1.5rem;">
          En momentos de tormenta emocional, el mayor obstáculo suele ser el no saber a dónde acudir o sentir vergüenza de pedir ayuda. 
        </p>
        <p style="font-size: 0.95rem; color: var(--ink-700); line-height: 1.8;">
          <strong>A tu lado</strong> nació para cerrar esa brecha: brindando herramientas terapéuticas inmediatas de biorregulación, un diario de evolución personal y acceso instantáneo a redes de apoyo profesionales.
        </p>
      </div>

      <!-- Stat cards -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
        <div class="card" style="padding: 1.75rem; text-align: center; background: var(--sage-50); border-color: var(--sage-200);">
          <div style="font-family: var(--font-display); font-size: 2.8rem; font-weight: 900; color: var(--sage-800); line-height: 1;">1 de 4</div>
          <div style="font-size: 0.85rem; color: var(--ink-600); margin-top: 0.5rem; font-weight: 600;">Personas experimentará desregulación emocional aguda</div>
        </div>

        <div class="card" style="padding: 1.75rem; text-align: center; background: var(--lav-50); border-color: var(--lav-200);">
          <div style="font-family: var(--font-display); font-size: 2.8rem; font-weight: 900; color: var(--lav-700); line-height: 1;">60s</div>
          <div style="font-size: 0.85rem; color: var(--ink-600); margin-top: 0.5rem; font-weight: 600;">Para reducir la frecuencia cardíaca con respiración 4-7-8</div>
        </div>

        <div class="card" style="padding: 1.75rem; text-align: center; background: var(--sky-50); border-color: var(--sky-200);">
          <div style="font-family: var(--font-display); font-size: 2.8rem; font-weight: 900; color: var(--sky-700); line-height: 1;">100%</div>
          <div style="font-size: 0.85rem; color: var(--ink-600); margin-top: 0.5rem; font-weight: 600;">Privacidad y soberanía sobre tus notas personales</div>
        </div>

        <div class="card" style="padding: 1.75rem; text-align: center; background: var(--amber-50); border-color: var(--amber-200);">
          <div style="font-family: var(--font-display); font-size: 2.8rem; font-weight: 900; color: var(--amber-700); line-height: 1;">24/7</div>
          <div style="font-size: 0.85rem; color: var(--ink-600); margin-top: 0.5rem; font-weight: 600;">Líneas de crisis integradas con discado directo</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ INTERACTIVE TOOLS SHOWCASE ════ -->
<section style="padding: 5rem 1.5rem; background: #ffffff;">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3.5rem;">
      <span class="mono-tag" style="color: var(--sage-600);">Herramientas de Regulación</span>
      <h2 style="font-size: clamp(1.9rem, 3.5vw, 2.7rem); margin-top: 0.35rem; margin-bottom: 0.75rem;">
        Ciencia aplicada para calmar tu mente
      </h2>
      <p style="color: var(--ink-600); font-size: 1rem; line-height: 1.7;">
        Técnicas terapéuticas validadas por la Terapia Dialéctico Conductual (DBT) y la neurobiología del sistema nervioso.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.75rem;">
      <!-- Tool 1 -->
      <div class="card" style="border-top: 5px solid var(--sage-500);">
        <div class="card-body">
          <span class="badge badge-sage">Fisiológico</span>
          <h3 style="font-size: 1.35rem; margin-top: 0.75rem; margin-bottom: 0.5rem;">Respiración 4-7-8</h3>
          <p style="font-size: 0.9rem; color: var(--ink-600); line-height: 1.7; margin-bottom: 1.5rem;">
            Estimula el nervio vago y disminuye la adrenalina en solo 2 minutos mediante ritmo guiado y campanas armónicas.
          </p>
          <a href="{{ route('tools.respiracion') }}" class="btn btn-primary btn-sm" style="width: 100%;">
            Iniciar Respiración Guiada →
          </a>
        </div>
      </div>

      <!-- Tool 2 -->
      <div class="card" style="border-top: 5px solid var(--sky-500);">
        <div class="card-body">
          <span class="badge badge-sky">Sensorial</span>
          <h3 style="font-size: 1.35rem; margin-top: 0.75rem; margin-bottom: 0.5rem;">Grounding 5-4-3-2-1</h3>
          <p style="font-size: 0.9rem; color: var(--ink-600); line-height: 1.7; margin-bottom: 1.5rem;">
            Desactiva la respuesta de pánico obligando a tu corteza prefrontal a anclarse en tus 5 sentidos reales.
          </p>
          <a href="{{ route('tools.grounding') }}" class="btn btn-secondary btn-sm" style="width: 100%;">
            Abrir Asistente Sensorial →
          </a>
        </div>
      </div>

      <!-- Tool 3 -->
      <div class="card" style="border-top: 5px solid var(--lav-500);">
        <div class="card-body">
          <span class="badge badge-lav">Tolerancia al Malestar</span>
          <h3 style="font-size: 1.35rem; margin-top: 0.75rem; margin-bottom: 0.5rem;">Técnica STOP (DBT)</h3>
          <p style="font-size: 0.9rem; color: var(--ink-600); line-height: 1.7; margin-bottom: 1.5rem;">
            Para momentos de enojo o impulsividad: Stop, Take a breath, Observe, Proceed con mente sabia.
          </p>
          <a href="{{ route('tools.stop') }}" class="btn btn-secondary btn-sm" style="width: 100%;">
            Aprender Técnica STOP →
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ FEATURED RESOURCES LIBRARY ════ -->
<section style="background: var(--bg-subtle); padding: 5rem 1.5rem;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="mono-tag" style="color: var(--sage-600);">Biblioteca Dinámica</span>
        <h2 style="font-size: 2rem; margin-top: 0.2rem;">Recursos de Bienestar</h2>
      </div>
      <a href="{{ route('recursos.index') }}" class="btn btn-secondary btn-sm">
        Ver catálogo completo ({{ $featuredResources->count() }}+) →
      </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
      @foreach($featuredResources as $res)
        <div class="card">
          <div class="card-header-banner theme-{{ $res->color_theme }}">
            <span class="mono-tag" style="position: absolute; top: 12px; left: 14px; background: rgba(255,255,255,0.7); padding: 0.2rem 0.55rem; border-radius: var(--radius-sm);">
              {{ $res->category_label }}
            </span>
            <span style="font-size: 2.5rem;">
              @if($res->color_theme === 'sage') 🌸
              @elseif($res->color_theme === 'sky') 🌊
              @elseif($res->color_theme === 'lav') 🪷
              @elseif($res->color_theme === 'terra') 🍂
              @elseif($res->color_theme === 'amber') ✨
              @else 🛑
              @endif
            </span>
          </div>
          <div class="card-body">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">
              <a href="{{ route('recursos.show', $res->slug) }}" style="color: var(--ink-900);">
                {{ $res->title }}
              </a>
            </h3>
            <p style="font-size: 0.85rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
              {{ Str::limit($res->summary, 95) }}
            </p>
            <a href="{{ route('recursos.show', $res->slug) }}" class="btn btn-sm btn-secondary" style="width: 100%; justify-content: center;">
              Ver ejercicio →
            </a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ════ LATEST FROM MAGAZINE ════ -->
<section style="padding: 5rem 1.5rem; background: #ffffff;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="mono-tag" style="color: var(--lav-600);">Psicoeducación Editorial</span>
        <h2 style="font-size: 2rem; margin-top: 0.2rem;">Últimas lecturas de la Revista</h2>
      </div>
      <a href="{{ route('revista.index') }}" class="btn btn-secondary btn-sm">
        Ver todos los artículos →
      </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.75rem;">
      @foreach($latestArticles as $art)
        <div class="card">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <span class="badge badge-{{ $art->color_tag }}">{{ $art->category }}</span>
              <span class="mono-tag" style="color: var(--ink-400);">⏱ {{ $art->read_time }}</span>
            </div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; line-height: 1.3;">
              <a href="{{ route('revista.show', $art->slug) }}" style="color: var(--ink-900);">
                {{ $art->title }}
              </a>
            </h3>
            <p style="font-size: 0.88rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
              {{ $art->excerpt }}
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--ink-100); padding-top: 0.85rem;">
              <span style="font-size: 0.8rem; color: var(--ink-400);">Por {{ $art->author_name }}</span>
              <a href="{{ route('revista.show', $art->slug) }}" style="color: var(--sage-600); font-weight: 700; font-size: 0.85rem; text-decoration: underline;">
                Leer más →
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ════ FINAL INSPIRING CTA ════ -->
<section style="background: var(--dark-950); color: #ffffff; padding: 6rem 1.5rem; text-align: center; position: relative; overflow: hidden;">
  <div class="hero-halo" style="top: 50%; left: 50%;"></div>
  <div style="position: relative; z-index: 2; max-width: 650px; margin: 0 auto;">
    <div class="mono-tag" style="color: var(--sage-300); margin-bottom: 0.75rem;">Tu refugio personal</div>
    <h2 style="color: #ffffff; font-size: clamp(2rem, 4.5vw, 3.2rem); margin-bottom: 1rem; line-height: 1.15;">
      Comienza a cultivar tu <em>paz interior</em> hoy
    </h2>
    <p style="color: rgba(255, 255, 255, 0.65); font-size: 1.05rem; line-height: 1.75; margin-bottom: 2rem;">
      Regístrate para llevar tu diario emocional, registrar tus momentos de gratitud y tener listo tu Plan de Seguridad personal.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
      @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
          Entrar a Mi Espacio Seguro →
        </a>
      @else
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
          Crear cuenta gratis y comenzar 🌱
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-white btn-lg">
          Iniciar Sesión
        </a>
      @endauth
    </div>
  </div>
</section>
@endsection
