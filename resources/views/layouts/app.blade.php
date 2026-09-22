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
  @include('partials.iconos')
  
  <!-- CSS & Official Fonts with Cache Buster -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : time() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600..700;1,9..144,400&family=IBM+Plex+Mono:ital,wght@0,400;0,500;0,600;1,400&family=Instrument+Serif:ital@0;1&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Pro/Free Kit (Non-blocking deferred) -->
  <script src="https://kit.fontawesome.com/6244811c40.js" crossorigin="anonymous" defer></script>
  
  <!-- RPG Awesome & Game Icons Library (Deferred) -->
  <link rel="stylesheet" href="{{ asset('vendor/rpg-awesome/css/rpg-awesome.min.css') }}">
  <script src="{{ asset('vendor/iconify/iconify-icon.min.js') }}" defer></script>
  <script src="{{ asset('js/game-icons-pack.js') }}?v={{ file_exists(public_path('js/game-icons-pack.js')) ? filemtime(public_path('js/game-icons-pack.js')) : time() }}" defer></script>
  @stack('styles')
</head>
<body style="background: #F8FAF9; color: #1A2620;">

  <div class="dashboard-layout">

    <!-- ════ COMPACT STICKY SIDEBAR (NO-SCROLL PINNED LOGOUT) ════ -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      
      <!-- Brand Header -->
      <div class="sidebar-header">
        <a href="{{ route('home') }}" class="sidebar-brand">
          <x-logo :size="22" />
          <span>a tu <em class="editorial-italic" style="color: #A8E6C0;">lado</em></span>
        </a>
      </div>

      <!-- Navigation Menu -->
      <nav class="sidebar-nav-group">
        <div class="sidebar-section-title">Mi Espacio</div>
        
        @if(auth()->user()?->is_admin)
          <a href="{{ route('admin.dashboard') }}" class="sidebar-item" style="background: rgba(168,230,192,0.12); color: #A8E6C0; border: 1px solid rgba(168,230,192,0.25); margin-bottom: 0.5rem;" title="Ir al Panel de Administración">
            <div class="sidebar-item-icon"><i class="fa-solid fa-shield-halved" style="color: #A8E6C0;"></i></div>
            <span style="font-weight: 700;">Torre de Control</span>
          </a>
        @elseif(auth()->user()?->isClinicoAcreditado())
          <a href="{{ route('admin.cola.index') }}" class="sidebar-item" style="background: rgba(168,230,192,0.12); color: #A8E6C0; border: 1px solid rgba(168,230,192,0.25); margin-bottom: 0.5rem;" title="Ir al panel clínico">
            <div class="sidebar-item-icon"><i class="fa-solid fa-user-doctor" style="color: #A8E6C0;"></i></div>
            <span style="font-weight: 700;">Panel clínico</span>
          </a>
        @endif

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
        <div class="sidebar-footer-content">
          <a href="{{ route('profile.show') }}" class="user-profile-badge" title="Ir a mi perfil">
            <div class="user-avatar-circle" style="background: #2E5D4B; overflow: hidden; padding: 0;">
              @if(auth()->user()?->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
              @else
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
              @endif
            </div>
            <div class="user-info-text">
              <div class="user-name-text" style="display: flex; align-items: center; gap: 4px;">
                <span>{{ auth()->user()?->name ?? 'Usuario' }}</span>
                @if(auth()->user()?->isProfessional())
                  <x-verified-badge size="14" :popover="false" />
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
          <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="btn-sidebar-logout" title="Cerrar Sesión" aria-label="Cerrar Sesión">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
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
        <div class="topbar-left-group">
          <button id="mobileSidebarToggle" class="btn btn-sm btn-secondary mobile-sidebar-toggle" aria-label="Abrir menú de navegación">
            <i class="fa-solid fa-bars"></i>
          </button>
          <div class="topbar-date-display">
            <i class="fa-regular fa-calendar" style="color: #2E5D4B;"></i>
            <span>{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</span>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
          @if(auth()->user()?->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm" style="background: #111A14; color: #A8E6C0; border: 1px solid rgba(168,230,192,0.3); font-weight: 700; font-size: 0.78rem; padding: 0.45rem 0.85rem; border-radius: 9999px; gap: 6px; text-decoration: none; display: inline-flex; align-items: center;" title="Volver a la consola de administración">
              <i class="fa-solid fa-shield-halved" style="color: #A8E6C0;"></i>
              <span>Torre de Control</span>
            </a>
          @elseif(auth()->user()?->isClinicoAcreditado())
            <a href="{{ route('admin.cola.index') }}" class="btn btn-sm" style="background: #111A14; color: #A8E6C0; border: 1px solid rgba(168,230,192,0.3); font-weight: 700; font-size: 0.78rem; padding: 0.45rem 0.85rem; border-radius: 9999px; gap: 6px; text-decoration: none; display: inline-flex; align-items: center;" title="Ir al panel clínico">
              <i class="fa-solid fa-user-doctor" style="color: #A8E6C0;"></i>
              <span>Panel clínico</span>
            </a>
          @endif

          <!-- Interactive Streak / Tree Badge Button -->
          @php
            $userStreak = auth()->user()?->calculateStreak() ?? 0;
            $userTreeLevel = min(5, max(1, intdiv($userStreak, 3) + 1));
          @endphp
          <button type="button" onclick="(typeof window.openTreeGame === 'function' && document.getElementById('treeGameModalOverlay')) ? window.openTreeGame() : (window.location.href='{{ route('dashboard') }}?open_game=1')" class="topbar-streak-pill" title="Tu racha activa y árbol de bienestar (Click para abrir)">
            <div class="streak-flame-segment">
              <i class="fa-solid fa-fire {{ $userStreak > 0 ? 'flame-active' : '' }}"></i>
              <span class="streak-number">{{ $userStreak }}</span>
              <span class="streak-label">{{ $userStreak === 1 ? 'día' : 'días' }}</span>
            </div>
            <div class="streak-pill-divider"></div>
            <div class="streak-tree-segment">
              <i class="fa-solid fa-seedling"></i>
              <span class="tree-level-label">Nvl {{ $userTreeLevel }}</span>
            </div>
          </button>
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
  <script>
    window.ATULADO_USER_ID = {{ auth()->id() ? auth()->id() : "'guest'" }};
  </script>
  <script src="{{ asset('js/game-engine.js') }}?v={{ file_exists(public_path('js/game-engine.js')) ? filemtime(public_path('js/game-engine.js')) : time() }}"></script>
  @stack('scripts')
</body>
</html>
