<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
  @endphp
  <title>@yield('title', 'Mi Espacio') — A tu lado</title>
  
  <!-- CSS & Official Fonts with Cache Buster -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : time() }}">
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
<body style="background: #F8FAF9; color: #1A2620;">

  <div class="dashboard-layout">

    <!-- ════ COMPACT STICKY SIDEBAR (NO-SCROLL PINNED LOGOUT) ════ -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      
      <!-- Brand Header -->
      <div class="sidebar-header">
        <a href="{{ route('home') }}" class="sidebar-brand">
          <svg class="ptree" viewBox="0 0 16 16" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
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
      </div>

      <!-- Navigation Menu -->
      <nav class="sidebar-nav-group">
        <div class="sidebar-section-title">Mi Espacio</div>
        
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-table-cells-large"></i></div>
          <span>Dashboard</span>
        </a>

        <a href="{{ route('mood.history') }}" class="sidebar-item {{ request()->routeIs('mood.history') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-chart-line"></i></div>
          <span>Historial & Racha</span>
        </a>

        <a href="{{ route('safety-plan.show') }}" class="sidebar-item {{ request()->routeIs('safety-plan.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-shield-heart"></i></div>
          <span>Plan de Seguridad</span>
        </a>

        <a href="{{ route('favorites.index') }}" class="sidebar-item {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-star"></i></div>
          <span>Mis Favoritos</span>
        </a>

        <div class="sidebar-section-title">Terapia & Herramientas DBT</div>

        <a href="{{ route('tools.respiracion') }}" class="sidebar-item {{ request()->routeIs('tools.respiracion') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-lungs"></i></div>
          <span>Respira Conmigo</span>
        </a>

        <a href="{{ route('tools.grounding') }}" class="sidebar-item {{ request()->routeIs('tools.grounding') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <span>Grounding 5-4-3-2-1</span>
        </a>

        <a href="{{ route('tools.stop') }}" class="sidebar-item {{ request()->routeIs('tools.stop') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-circle-pause"></i></div>
          <span>Técnica STOP (DBT)</span>
        </a>

        <a href="{{ route('sientes') }}" class="sidebar-item {{ request()->routeIs('sientes') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-heart-pulse"></i></div>
          <span>¿Cómo te sientes?</span>
        </a>

        <div class="sidebar-section-title">Comunidad & Recursos</div>

        <a href="{{ route('recursos.index') }}" class="sidebar-item {{ request()->routeIs('recursos.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-book-bookmark"></i></div>
          <span>Biblioteca de Recursos</span>
        </a>

        <a href="{{ route('revista.index') }}" class="sidebar-item {{ request()->routeIs('revista.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon"><i class="fa-solid fa-newspaper"></i></div>
          <span>Revista Científica</span>
        </a>

        <a href="{{ route('crisis') }}" class="sidebar-item {{ request()->routeIs('crisis') ? 'active' : '' }}" style="color: #FFA59C;">
          <div class="sidebar-item-icon"><i class="fa-solid fa-phone-volume"></i></div>
          <span>Líneas de Crisis 24/7</span>
        </a>
      </nav>

      <!-- PINNED USER PROFILE & LOGOUT AT BOTTOM -->
      <div class="sidebar-footer">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
          <a href="{{ route('profile.show') }}" class="user-profile-badge">
            <div class="user-avatar-circle" style="background: #2E5D4B; overflow: hidden; padding: 0;">
              @if(auth()->user()?->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
              @else
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
              @endif
            </div>
            <div class="user-info-text">
              <div class="user-name-text" style="display: flex; align-items: center; gap: 4px;">
                <span>{{ auth()->user()?->name ?? 'Usuario' }}</span>
                @if(auth()->user()?->isProfessional())
                  <x-verified-badge size="14" />
                @endif
              </div>
              <div class="user-role-text">
                @if(auth()->user()?->isProfessional())
                  <span style="color: #A8E6C0;"><i class="fa-solid fa-certificate"></i> Profesional</span>
                @else
                  Mi Perfil <i class="fa-solid fa-chevron-right" style="font-size: 0.65rem;"></i>
                @endif
              </div>
            </div>
          </a>

          <!-- Instant 1-Click Logout -->
          <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" title="Cerrar Sesión" style="background: rgba(192, 57, 43, 0.18); border: 1px solid rgba(192, 57, 43, 0.35); color: #FFA59C; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
              <i class="fa-solid fa-arrow-right-from-bracket" style="font-size: 0.82rem;"></i>
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Overlay for Mobile Sidebar -->
    <div id="sidebarOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 85;"></div>

    <!-- ════ MAIN DASHBOARD AREA ════ -->
    <div class="dashboard-main">

      <!-- TOPBAR WITH INSTANT SHORTCUTS -->
      <header class="dashboard-topbar">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <button id="mobileSidebarToggle" class="btn btn-sm btn-secondary" style="display: none; padding: 0.4rem 0.65rem;" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
          </button>
          <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #556860; font-family: var(--font-mono);">
            <i class="fa-regular fa-calendar" style="color: #2E5D4B;"></i>
            <span>{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</span>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
          <!-- Interactive Streak / Tree Badge Button -->
          @php
            $userStreak = auth()->user()?->calculateStreak() ?? 0;
            $userTreeLevel = min(5, max(1, intdiv($userStreak, 3) + 1));
          @endphp
          <button type="button" onclick="(typeof window.openTreeGame === 'function' && document.getElementById('treeGameModalOverlay')) ? window.openTreeGame() : (window.location.href='{{ route('dashboard') }}?open_game=1')" class="btn btn-sm btn-secondary" style="gap: 6px; background: #FAFDFB; border-color: #A8E6C0; color: #1E4A25; font-weight: 700;" title="Tu racha activa y árbol de bienestar">
            <i class="fa-solid fa-fire" style="color: #E67E22;"></i>
            <span>{{ $userStreak }} {{ $userStreak === 1 ? 'día' : 'días' }}</span>
            <span style="color: #C2D6CA;">|</span>
            <i class="fa-solid fa-seedling" style="color: #3D8C4F;"></i>
            <span>Nvl {{ $userTreeLevel }}</span>
          </button>

          <!-- Quick Breath Shortcut -->
          <a href="{{ route('tools.respiracion') }}" class="btn btn-sm btn-secondary" style="gap: 6px;">
            <i class="fa-solid fa-lungs" style="color: #2E5D4B;"></i>
            <span>Respira 1 min</span>
          </a>

          <!-- Crisis Button SOS -->
          <a href="tel:8002900024" class="btn btn-sm btn-crisis" style="gap: 6px;" title="Llamar gratis a Línea de la Vida 24/7">
            <i class="fa-solid fa-phone"></i>
            <span>SOS 24h</span>
          </a>
        </div>
      </header>

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

      <!-- MAIN PAGE CONTENT -->
      <main class="dashboard-content" id="spaContent">
        @yield('content')
      </main>

    </div>
  </div>

  <script src="{{ asset('js/main.js') }}?v={{ file_exists(public_path('js/main.js')) ? filemtime(public_path('js/main.js')) : time() }}"></script>
  @stack('scripts')
</body>
</html>
