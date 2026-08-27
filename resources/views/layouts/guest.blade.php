<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'A tu lado — Apoyo y Bienestar Emocional')</title>
  <meta name="description" content="Plataforma de acompañamiento psicológico, regulación emocional basada en evidencia DBT, plan de seguridad personal y directorio de crisis 24/7.">
  
  <!-- Favicon / Brand CSS with Cache Buster -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : time() }}">
  <!-- Google Fonts: Fraunces, Instrument Serif, Manrope, IBM Plex Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..900;1,9..144,300..900&family=IBM+Plex+Mono:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Instrument+Serif:ital@0;1&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Pro/Free Kit -->
  <script src="https://kit.fontawesome.com/6244811c40.js" crossorigin="anonymous"></script>
  
  <!-- RPG Awesome & Game Icons Library -->
  <link rel="stylesheet" href="{{ asset('vendor/rpg-awesome/css/rpg-awesome.min.css') }}">
  <script src="{{ asset('vendor/iconify/iconify-icon.min.js') }}"></script>
  <script src="{{ asset('js/game-icons-pack.js') }}?v={{ file_exists(public_path('js/game-icons-pack.js')) ? filemtime(public_path('js/game-icons-pack.js')) : time() }}"></script>
  @stack('styles')
</head>
<body style="background-color: #F8FAF9; color: #1A2620;">

  <!-- PUBLIC NAVBAR -->
  <nav class="site-navbar" id="siteNavbar">
    <div class="nav-container">
      
      <!-- Brand Logo -->
      <a href="{{ route('home') }}" class="nav-brand" aria-label="Ir a inicio">
        <svg class="ptree" viewBox="0 0 16 16" width="28" height="28" xmlns="http://www.w3.org/2000/svg">
          <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
          <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
          <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
          <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
          <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
          <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
          <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
          <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
          <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
        </svg>
        <span>a tu <em class="editorial-italic" style="color: #A8E6C0;">lado</em></span>
      </a>

      <!-- Menu Links -->
      <ul class="nav-menu">
        <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a></li>
        <li><a href="{{ route('sientes') }}" class="nav-link {{ request()->routeIs('sientes') ? 'active' : '' }}">¿Cómo te sientes?</a></li>
        <li><a href="{{ route('tools.respiracion') }}" class="nav-link {{ request()->routeIs('tools.respiracion') ? 'active' : '' }}">Respira Conmigo</a></li>
        <li><a href="{{ route('recursos.index') }}" class="nav-link {{ request()->routeIs('recursos.*') ? 'active' : '' }}">Recursos</a></li>
        <li><a href="{{ route('revista.index') }}" class="nav-link {{ request()->routeIs('revista.*') ? 'active' : '' }}">Revista</a></li>
        <li><a href="{{ route('crisis') }}" class="nav-link {{ request()->routeIs('crisis') ? 'active' : '' }}">Líneas de Ayuda</a></li>
      </ul>

      <!-- Action Buttons -->
      <div class="nav-actions">
        <a href="tel:8002900024" class="nav-crisis-pill" title="Llamar a Línea de la Vida 24/7">
          <i class="fa-solid fa-phone" style="font-size: 0.75rem;"></i>
          <span class="crisis-label-desktop">CRISIS: 800 290 0024</span>
          <span class="crisis-label-mobile">SOS 24h</span>
        </a>

        @auth
          <a href="{{ route('dashboard') }}" class="nav-btn-access">
            <i class="fa-solid fa-user-shield"></i>
            <span>Mi Espacio</span>
          </a>
        @else
          <a href="{{ route('login') }}" class="nav-btn-access">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>Acceder</span>
          </a>
        @endauth

        <button class="nav-hamburger" id="navHamburgerBtn" aria-label="Abrir menú de navegación" aria-expanded="false">
          <i class="fa-solid fa-bars" style="font-size: 1.25rem;"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- MOBILE DRAWER -->
  <div class="nav-mobile-drawer" id="navMobileDrawer">
    <a href="{{ route('home') }}" class="nav-mobile-link"><i class="fa-solid fa-house" style="margin-right: 8px; color: #A8E6C0;"></i> Inicio</a>
    <a href="{{ route('sientes') }}" class="nav-mobile-link"><i class="fa-solid fa-heart-pulse" style="margin-right: 8px; color: #A8E6C0;"></i> ¿Cómo te sientes hoy?</a>
    <a href="{{ route('tools.respiracion') }}" class="nav-mobile-link"><i class="fa-solid fa-lungs" style="margin-right: 8px; color: #A8E6C0;"></i> Respira Conmigo</a>
    <a href="{{ route('recursos.index') }}" class="nav-mobile-link"><i class="fa-solid fa-book-bookmark" style="margin-right: 8px; color: #A8E6C0;"></i> Recursos y Ejercicios</a>
    <a href="{{ route('revista.index') }}" class="nav-mobile-link"><i class="fa-solid fa-newspaper" style="margin-right: 8px; color: #A8E6C0;"></i> Revista de Bienestar</a>
    <a href="{{ route('crisis') }}" class="nav-mobile-link" style="color: #FFA59C;"><i class="fa-solid fa-phone-volume" style="margin-right: 8px;"></i> Directorio de Crisis 24/7</a>
    @auth
      <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top: 1rem;"><i class="fa-solid fa-user-shield"></i> Ir a Mi Espacio</a>
    @else
      <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
        <a href="{{ route('login') }}" class="btn btn-secondary" style="flex: 1;"><i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión</a>
        <a href="{{ route('register') }}" class="btn btn-primary" style="flex: 1;"><i class="fa-solid fa-user-plus"></i> Crear Cuenta</a>
      </div>
    @endauth
  </div>

  <!-- GLOBAL FLOATING TOAST CONTAINER (FUERA DE LAS TARJETAS) -->
  <div id="zenToastContainer">
    @if(session('success'))
      <div class="zen-toast-pill success" onclick="this.remove()">
        <i class="fa-solid fa-circle-check" style="font-size: 1.15rem; color: #1E4A25;"></i>
        <span>{{ session('success') }}</span>
        <div class="toast-fill-bar"></div>
      </div>
    @endif
    @if(session('error'))
      <div class="zen-toast-pill error" onclick="this.remove()">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.15rem; color: #922B21;"></i>
        <span>{{ session('error') }}</span>
        <div class="toast-fill-bar"></div>
      </div>
    @endif
    @if(session('info'))
      <div class="zen-toast-pill info" onclick="this.remove()">
        <i class="fa-solid fa-circle-info" style="font-size: 1.15rem; color: #4A3575;"></i>
        <span>{{ session('info') }}</span>
        <div class="toast-fill-bar"></div>
      </div>
    @endif
  </div>

  <div id="spaProgressBar"></div>

  <!-- MAIN PAGE CONTENT (SPA TRANSITION WRAPPER) -->
  <main id="spaContent">
    @yield('content')
  </main>

  <!-- SITE FOOTER -->
  <footer class="site-footer">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <svg class="ptree" viewBox="0 0 16 16" width="26" height="26" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
            <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
            <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
            <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
            <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
            <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
            <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
            <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
            <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
          </svg>
          <span>a tu <em class="editorial-italic" style="color: #A8E6C0;">lado</em></span>
        </div>
        <p class="footer-desc">
          Un espacio seguro y confidencial de acompañamiento psicológico, herramientas DBT y prevención en crisis para México y Latinoamérica.
        </p>
        <a href="tel:8002900024" class="badge badge-crisis" style="padding: 0.5rem 1rem; gap: 8px; text-decoration: none;">
          <i class="fa-solid fa-phone"></i>
          <span>Línea 24h: 800 290 0024</span>
        </a>
      </div>

      <div>
        <h4 class="footer-col-title">Explorar</h4>
        <ul class="footer-links">
          <li><a href="{{ route('home') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Inicio</a></li>
          <li><a href="{{ route('sientes') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> ¿Cómo te sientes hoy?</a></li>
          <li><a href="{{ route('recursos.index') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Biblioteca de Recursos</a></li>
          <li><a href="{{ route('revista.index') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Revista de Salud Mental</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-col-title">Herramientas</h4>
        <ul class="footer-links">
          <li><a href="{{ route('tools.respiracion') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Respira Conmigo (4-7-8)</a></li>
          <li><a href="{{ route('tools.grounding') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Grounding 5-4-3-2-1</a></li>
          <li><a href="{{ route('tools.stop') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Técnica STOP (DBT)</a></li>
          <li><a href="{{ route('crisis') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Líneas de Crisis 24h</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-col-title">Tu Espacio</h4>
        <ul class="footer-links">
          @auth
            <li><a href="{{ route('dashboard') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Panel Principal</a></li>
            <li><a href="{{ route('mood.history') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Historial de Emociones</a></li>
            <li><a href="{{ route('safety-plan.show') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Plan de Seguridad</a></li>
            <li><a href="{{ route('favorites.index') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Mis Favoritos</a></li>
          @else
            <li><a href="{{ route('login') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Iniciar Sesión</a></li>
            <li><a href="{{ route('register') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Crear Cuenta Gratis</a></li>
            <li><a href="{{ route('login') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: #A8E6C0;"></i> Diario Personal</a></li>
          @endauth
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© {{ date('Y') }} A tu lado · Plataforma de Bienestar y Salud Mental</p>
      <p style="font-size: 0.76rem; opacity: 0.75; max-width: 600px;">
        *Este servicio es complementario y no sustituye la psicoterapia profesional individualizada. En emergencias severas, comunícate de inmediato a la Línea de la Vida.
      </p>
    </div>
  </footer>

  <script src="{{ asset('js/main.js') }}?v={{ file_exists(public_path('js/main.js')) ? filemtime(public_path('js/main.js')) : time() }}"></script>
  @stack('scripts')
</body>
</html>
