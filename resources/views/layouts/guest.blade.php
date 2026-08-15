<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'A tu lado — Apoyo y Bienestar Emocional')</title>
  <meta name="description" content="Plataforma de acompañamiento psicológico, regulación emocional basada en evidencia DBT, plan de seguridad personal y directorio de crisis 24/7.">
  
  <!-- Favicon / Brand -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('styles')
</head>
<body>

  <!-- PUBLIC NAVBAR -->
  <nav class="site-navbar" id="siteNavbar">
    <div class="nav-container">
      <a href="{{ route('home') }}" class="nav-brand" aria-label="Ir a inicio">
        <svg class="ptree nav-brand-tree" viewBox="0 0 16 16" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
          <rect x="5" y="0" width="6" height="2" fill="#5a9060"/>
          <rect x="3" y="2" width="10" height="2" fill="#6B8F71"/>
          <rect x="2" y="4" width="12" height="2" fill="#7ab870"/>
          <rect x="3" y="6" width="10" height="2" fill="#6B8F71"/>
          <rect x="5" y="8" width="6" height="2" fill="#5a9060"/>
          <rect x="7" y="10" width="2" height="4" fill="#8B5e30"/>
          <rect x="4" y="1" width="1" height="1" fill="#e84040"/>
          <rect x="11" y="3" width="1" height="1" fill="#e84040"/>
          <rect x="9" y="7" width="1" height="1" fill="#e84040"/>
        </svg>
        <span>a tu lado</span>
      </a>

      <ul class="nav-menu">
        <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a></li>
        <li><a href="{{ route('recursos.index') }}" class="nav-link {{ request()->routeIs('recursos.*') ? 'active' : '' }}">Recursos</a></li>
        <li><a href="{{ route('revista.index') }}" class="nav-link {{ request()->routeIs('revista.*') ? 'active' : '' }}">Revista</a></li>
        <li><a href="{{ route('sientes') }}" class="nav-link {{ request()->routeIs('sientes') ? 'active' : '' }}">¿Cómo te sientes?</a></li>
        <li><a href="{{ route('crisis') }}" class="nav-link {{ request()->routeIs('crisis') ? 'active' : '' }}">Líneas de Ayuda</a></li>
      </ul>

      <div class="nav-actions">
        <a href="tel:8002900024" class="nav-crisis-pill" title="Llamar a Línea de la Vida">
          <span class="nav-crisis-dot"></span>
          <span>Crisis: 800 290 0024</span>
        </a>

        @auth
          <a href="{{ route('dashboard') }}" class="nav-btn-access">
            <span>Mi Espacio</span>
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </a>
        @else
          <a href="{{ route('login') }}" class="nav-btn-access">
            <span>Acceder</span>
          </a>
        @endauth

        <button class="nav-hamburger" id="navHamburgerBtn" aria-label="Abrir menú de navegación">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
      </div>
    </div>
  </nav>

  <!-- MOBILE DRAWER -->
  <div class="nav-mobile-drawer" id="navMobileDrawer">
    <a href="{{ route('home') }}" class="nav-mobile-link">Inicio</a>
    <a href="{{ route('recursos.index') }}" class="nav-mobile-link">Recursos y Ejercicios</a>
    <a href="{{ route('revista.index') }}" class="nav-mobile-link">Revista de Bienestar</a>
    <a href="{{ route('sientes') }}" class="nav-mobile-link">¿Cómo te sientes hoy?</a>
    <a href="{{ route('tools.respiracion') }}" class="nav-mobile-link">Respiración Guiada</a>
    <a href="{{ route('crisis') }}" class="nav-mobile-link" style="color: #ff9999;">Directorio de Crisis 24/7</a>
    @auth
      <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top: 1rem;">Ir a Mi Espacio</a>
    @else
      <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
        <a href="{{ route('login') }}" class="btn btn-secondary" style="flex: 1;">Iniciar Sesión</a>
        <a href="{{ route('register') }}" class="btn btn-primary" style="flex: 1;">Crear Cuenta</a>
      </div>
    @endauth
  </div>

  <!-- FLASH ALERTS -->
  @if(session('success') || session('error') || session('info'))
    <div class="container" style="margin-top: 1.5rem;">
      @if(session('success'))
        <div class="alert-banner alert-success">
          <span>🌿</span>
          <div>{{ session('success') }}</div>
        </div>
      @endif
      @if(session('error'))
        <div class="alert-banner alert-error">
          <span>⚠️</span>
          <div>{{ session('error') }}</div>
        </div>
      @endif
      @if(session('info'))
        <div class="alert-banner alert-info">
          <span>💡</span>
          <div>{{ session('info') }}</div>
        </div>
      @endif
    </div>
  @endif

  <!-- MAIN PAGE CONTENT -->
  <main>
    @yield('content')
  </main>

  <!-- SITE FOOTER -->
  <footer class="site-footer">
    <div class="footer-grid">
      <div>
        <div class="nav-brand" style="margin-bottom: 0.85rem;">
          <svg class="ptree" viewBox="0 0 16 16" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#5a9060"/>
            <rect x="3" y="2" width="10" height="2" fill="#6B8F71"/>
            <rect x="2" y="4" width="12" height="2" fill="#7ab870"/>
            <rect x="3" y="6" width="10" height="2" fill="#6B8F71"/>
            <rect x="5" y="8" width="6" height="2" fill="#5a9060"/>
            <rect x="7" y="10" width="2" height="4" fill="#8B5e30"/>
            <rect x="4" y="1" width="1" height="1" fill="#e84040"/>
            <rect x="11" y="3" width="1" height="1" fill="#e84040"/>
            <rect x="9" y="7" width="1" height="1" fill="#e84040"/>
          </svg>
          <span>a tu lado</span>
        </div>
        <p style="color: rgba(255, 255, 255, 0.55); font-size: 0.9rem; max-width: 320px; line-height: 1.7; margin-bottom: 1.25rem;">
          Un espacio seguro y confidencial de acompañamiento psicológico, herramientas DBT y prevención en crisis para México y Latinoamérica.
        </p>
        <div class="badge badge-crisis" style="padding: 0.4rem 0.9rem;">
          Línea de la Vida: 800 290 0024 · Gratuita 24h
        </div>
      </div>

      <div>
        <h4 class="footer-col-title">Explorar</h4>
        <ul class="footer-links">
          <li><a href="{{ route('home') }}">Inicio</a></li>
          <li><a href="{{ route('recursos.index') }}">Biblioteca de Recursos</a></li>
          <li><a href="{{ route('revista.index') }}">Revista de Salud Mental</a></li>
          <li><a href="{{ route('sientes') }}">Test Emocional</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-col-title">Herramientas</h4>
        <ul class="footer-links">
          <li><a href="{{ route('tools.respiracion') }}">Respiración Diafragmática (4-7-8)</a></li>
          <li><a href="{{ route('tools.grounding') }}">Grounding Sensorial 5-4-3-2-1</a></li>
          <li><a href="{{ route('tools.stop') }}">Técnica STOP (DBT)</a></li>
          <li><a href="{{ route('crisis') }}">Líneas Internacionales</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-col-title">Tu Espacio</h4>
        <ul class="footer-links">
          @auth
            <li><a href="{{ route('dashboard') }}">Panel Principal</a></li>
            <li><a href="{{ route('mood.history') }}">Historial de Emociones</a></li>
            <li><a href="{{ route('safety-plan.show') }}">Mi Plan de Seguridad</a></li>
            <li><a href="{{ route('favorites.index') }}">Mis Favoritos</a></li>
          @else
            <li><a href="{{ route('login') }}">Iniciar Sesión</a></li>
            <li><a href="{{ route('register') }}">Crear Cuenta Gratis</a></li>
            <li><a href="{{ route('login') }}">Acceso a Diario Personal</a></li>
          @endauth
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© {{ date('Y') }} A tu lado · Plataforma de Bienestar y Salud Mental</p>
      <p style="font-size: 0.78rem; opacity: 0.7;">
        *Este servicio es complementario y no sustituye la psicoterapia profesional individualizada. En emergencias severas, comunícate de inmediato a la Línea de la Vida.
      </p>
    </div>
  </footer>

  <script src="{{ asset('js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
