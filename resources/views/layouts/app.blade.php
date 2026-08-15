<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Mi Espacio') — A tu lado</title>
  
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('styles')
</head>
<body style="background: var(--bg-canvas);">

  <!-- DASHBOARD WRAPPER -->
  <div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="sidebar-header">
        <a href="{{ route('home') }}" class="sidebar-brand">
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
        </a>
      </div>

      <nav class="sidebar-nav-group">
        <div class="sidebar-section-title">Mi Espacio Seguro</div>
        
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
          </div>
          <span>Dashboard</span>
        </a>

        <a href="{{ route('mood.history') }}" class="sidebar-item {{ request()->routeIs('mood.history') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 17l6-6 4 4 8-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 7h7v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span>Historial & Analíticas</span>
        </a>

        <a href="{{ route('safety-plan.show') }}" class="sidebar-item {{ request()->routeIs('safety-plan.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span>Plan de Seguridad</span>
        </a>

        <a href="{{ route('favorites.index') }}" class="sidebar-item {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span>Mis Favoritos</span>
        </a>

        <div class="sidebar-section-title" style="margin-top: 0.75rem;">Herramientas en Vivo</div>

        <a href="{{ route('tools.respiracion') }}" class="sidebar-item {{ request()->routeIs('tools.respiracion') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <span>Respiración Guiada</span>
        </a>

        <a href="{{ route('tools.grounding') }}" class="sidebar-item {{ request()->routeIs('tools.grounding') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12h20M12 2v20M5 5l14 14M5 19L19 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <span>Grounding 5-4-3-2-1</span>
        </a>

        <a href="{{ route('tools.stop') }}" class="sidebar-item {{ request()->routeIs('tools.stop') ? 'active' : '' }}">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="2"/><path d="M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <span>Técnica STOP (DBT)</span>
        </a>

        <div class="sidebar-section-title" style="margin-top: 0.75rem;">Explorar</div>

        <a href="{{ route('recursos.index') }}" class="sidebar-item">
          <div class="sidebar-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2"/></svg>
          </div>
          <span>Biblioteca de Recursos</span>
        </a>

        <a href="{{ route('crisis') }}" class="sidebar-item" style="color: #ff9999;">
          <div class="sidebar-item-icon">
            <span class="nav-crisis-dot"></span>
          </div>
          <span>Líneas de Crisis 24/7</span>
        </a>
      </nav>

      <!-- CRISIS CALL SIDEBAR PILL -->
      <div style="padding: 0.75rem 1.25rem;">
        <a href="tel:8002900024" class="btn btn-crisis btn-sm" style="width: 100%; border-radius: var(--radius-full); justify-content: center; font-size: 0.76rem;">
          <span class="nav-crisis-dot"></span>
          <span>Crisis: 800 290 0024</span>
        </a>
      </div>

      <!-- FOOTER USER PROFILE -->
      <div class="sidebar-footer">
        <a href="{{ route('profile.show') }}" class="user-profile-badge">
          @php
            $avatarColors = [
              'sage' => 'linear-gradient(135deg, #4d7c5f, #2e4f3a)',
              'terra' => 'linear-gradient(135deg, #b86b4a, #743922)',
              'lav' => 'linear-gradient(135deg, #7a6faa, #4a3e72)',
              'sky' => 'linear-gradient(135deg, #4a7fa5, #2a4c66)',
              'amber' => 'linear-gradient(135deg, #c4901a, #7c5908)',
              'dark' => 'linear-gradient(135deg, #242e29, #0e1310)',
            ];
            $userBg = $avatarColors[auth()->user()->avatar_color ?? 'sage'] ?? $avatarColors['sage'];
          @endphp
          <div class="user-avatar-circle" style="background: {{ $userBg }};">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
          <div class="user-info-text">
            <div class="user-name-text">{{ auth()->user()->name }}</div>
            <div class="user-role-text">Ver perfil y ajustes</div>
          </div>
        </a>

        <form action="{{ route('logout') }}" method="POST" style="margin-top: 0.85rem;">
          @csrf
          <button type="submit" style="background: none; border: none; color: rgba(255, 255, 255, 0.45); font-size: 0.78rem; display: flex; align-items: center; gap: 0.4rem; cursor: pointer; transition: color var(--transition-fast);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Cerrar sesión</span>
          </button>
        </form>
      </div>
    </aside>

    <!-- OVERLAY FOR MOBILE SIDEBAR -->
    <div id="sidebarOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 85;"></div>

    <!-- MAIN AREA -->
    <div class="dashboard-main">

      <!-- TOPBAR -->
      <header class="dashboard-topbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <button id="sidebarToggleBtn" class="nav-hamburger" style="display: none; color: var(--ink-800);" aria-label="Abrir barra lateral">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
          <div style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink-600); letter-spacing: 0.04em;">
            {{ Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
          <a href="{{ route('tools.respiracion') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <span>🌸</span>
            <span>Respirar</span>
          </a>
          <a href="tel:8002900024" class="nav-crisis-pill">
            <span class="nav-crisis-dot"></span>
            <span>Línea 24h</span>
          </a>
        </div>
      </header>

      <!-- FLASH MESSAGES -->
      @if(session('success') || session('error') || session('info'))
        <div style="padding: 1.5rem 2rem 0;">
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

      <!-- INNER VIEW CONTENT -->
      <main class="dashboard-content">
        @yield('content')
      </main>

    </div>
  </div>

  <script src="{{ asset('js/main.js') }}"></script>
  <style>
    @media (max-width: 960px) {
      #sidebarToggleBtn { display: block !important; }
      #sidebarOverlay.open { display: block !important; }
    }
  </style>
  @stack('scripts')
</body>
</html>
