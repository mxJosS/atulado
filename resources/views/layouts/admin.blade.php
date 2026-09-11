<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
  @endphp
  <title>@yield('title', 'Panel de Administración') — A tu lado</title>
  
  <!-- CSS & Official Fonts -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : time() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600..700;1,9..144,400&family=IBM+Plex+Mono:ital,wght@0,400;0,500;0,600;1,400&family=Instrument+Serif:ital@0;1&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Free Kit -->
  <script src="https://kit.fontawesome.com/6244811c40.js" crossorigin="anonymous" defer></script>
  @stack('styles')
  <style>
    .admin-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.72rem 1rem;
      border-radius: 10px;
      color: #A3B8B0;
      text-decoration: none;
      font-size: 0.88rem;
      font-weight: 500;
      transition: all 0.2s ease;
      margin-bottom: 0.35rem;
    }
    .admin-nav-item:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #FFFFFF;
      transform: translateX(3px);
    }
    .admin-nav-item.active {
      background: #2E5D4B;
      color: #FFFFFF;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .admin-nav-item .nav-icon {
      width: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
    }
    .admin-nav-item.active .nav-icon {
      color: #A8E6C0;
    }
  </style>
</head>
<body style="background: #F8FAF9; color: #1A2620;">

  <div class="dashboard-layout">

    <!-- ════ ADMIN COMPACT SIDEBAR (ONLY 4 MODULES) ════ -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      
      <!-- Brand Header -->
      <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
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

      <!-- Admin Tag -->
      <div style="padding: 0 1.25rem 0.85rem;">
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 0.22rem 0.65rem; border-radius: 6px; background: rgba(168,230,192,0.12); color: #A8E6C0; font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em;">
          <i class="fa-solid fa-shield-halved" style="font-size: 0.72rem;"></i> CONSOLA ADMINISTRADOR
        </span>
      </div>

      <!-- Navigation Menu: ONLY 4 SPECIFIED MODULES -->
      <nav class="sidebar-nav-group" style="padding: 0 0.85rem; flex: 1;">
        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; text-transform: uppercase; color: #6E887E; font-weight: 600; letter-spacing: 0.08em; margin-bottom: 0.65rem; padding-left: 0.45rem;">
          Módulos Principales
        </div>
        
        <!-- 1. Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <div class="nav-icon"><i class="fa-solid fa-table-cells-large"></i></div>
          <span>Dashboard</span>
        </a>

        <!-- 2. Acreditación -->
        @php
          $pendingCount = \App\Models\ProfessionalVerification::where('status', 'pendiente')->count();
        @endphp
        <a href="{{ route('admin.verifications.index') }}" class="admin-nav-item {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}" style="position: relative;">
          <div class="nav-icon"><i class="fa-solid fa-id-card-clip"></i></div>
          <span>Acreditación</span>
          @if($pendingCount > 0)
            <span style="margin-left: auto; background: #D97706; color: white; border-radius: 999px; padding: 2px 7px; font-size: 0.68rem; font-weight: 700;">
              {{ $pendingCount }}
            </span>
          @endif
        </a>

        <!-- 3. Usuarios -->
        <a href="{{ route('admin.users.index') }}" class="admin-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <div class="nav-icon"><i class="fa-solid fa-users-gear"></i></div>
          <span>Usuarios</span>
        </a>

        <!-- 4. Foros -->
        <a href="{{ route('admin.forums.index') }}" class="admin-nav-item {{ request()->routeIs('admin.forums.*') ? 'active' : '' }}">
          <div class="nav-icon"><i class="fa-solid fa-comments"></i></div>
          <span>Foros</span>
        </a>
      </nav>

      <!-- Pinned Profile Footer & Logout (Cleaned & Aligned to theme) -->
      <div class="sidebar-footer">
        <div class="sidebar-footer-content">
          <a href="{{ route('profile.show') }}" class="user-profile-badge" title="Ver y editar mi perfil">
            <div class="user-avatar-circle" style="background: #2E5D4B; overflow: hidden; padding: 0;">
              @if(auth()->user()?->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
              @else
                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
              @endif
            </div>
            <div class="user-info-text">
              <div class="user-name-text" style="display: flex; align-items: center; gap: 4px;">
                <span>{{ auth()->user()?->name ?? 'Admin' }}</span>
                <i class="fa-solid fa-shield-halved" style="color: #A8E6C0; font-size: 0.72rem;"></i>
              </div>
              <div class="user-role-text" style="color: #A8E6C0;">
                Administrador <i class="fa-solid fa-chevron-right" style="font-size: 0.62rem; margin-left: 2px;"></i>
              </div>
            </div>
          </a>

          <!-- Logout Button -->
          <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="btn-sidebar-logout" title="Cerrar Sesión" aria-label="Cerrar Sesión">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </button>
          </form>
        </div>
      </div>

    </aside>

    <!-- ════ MAIN ADMIN CONTENT CANVAS ════ -->
    <div class="dashboard-main">
      <main class="dashboard-content" style="padding: clamp(1.2rem, 3vw, 2.5rem);">
        
        <!-- Top Flash Notification (Zen Toast / Banner Style) -->
        @if (session('success'))
          <div style="margin-bottom: 1.5rem; background: #EBF7EE; border: 1px solid #A8E6C0; border-radius: 12px; padding: 0.85rem 1.25rem; display: flex; align-items: center; gap: 10px; color: #1E4A25; font-size: 0.9rem; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
            <i class="fa-solid fa-circle-check" style="color: #2E5D4B; font-size: 1.1rem;"></i>
            <span>{{ session('success') }}</span>
          </div>
        @endif

        @if (session('error'))
          <div style="margin-bottom: 1.5rem; background: #FDE8E8; border: 1px solid #F8B4B4; border-radius: 12px; padding: 0.85rem 1.25rem; display: flex; align-items: center; gap: 10px; color: #9B1C1C; font-size: 0.9rem; font-weight: 500;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i>
            <span>{{ session('error') }}</span>
          </div>
        @endif

        @if ($errors->any())
          <div style="margin-bottom: 1.5rem; background: #FDE8E8; border: 1px solid #F8B4B4; border-radius: 12px; padding: 1rem 1.25rem; color: #9B1C1C;">
            <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-circle-xmark"></i> Hubo errores en tu solicitud:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @yield('content')
      </main>
    </div>

  </div>

  @stack('scripts')
</body>
</html>