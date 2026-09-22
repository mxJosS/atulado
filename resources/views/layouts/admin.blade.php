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
  @include('partials.iconos')
  
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
    .admin-switch-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 0.55rem 0.85rem;
      border-radius: 9px;
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
      text-decoration: none;
      font-size: 0.82rem;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.14);
      transition: all 0.2s ease;
      margin-bottom: 0.75rem;
    }
    .admin-switch-btn:hover {
      background: rgba(255, 255, 255, 0.16);
      color: #A8E6C0;
      border-color: rgba(168, 230, 192, 0.4);
    }
    .admin-mobile-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem 1.25rem;
      background: #FFFFFF;
      border-bottom: 1px solid #E2ECE6;
      position: sticky;
      top: 0;
      z-index: 40;
    }
    .mobile-sidebar-toggle-admin {
      display: none;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      padding: 0;
      border-radius: 8px;
      border: 1px solid #DCE8E0;
      background: #FFFFFF;
      color: #1A2620;
      cursor: pointer;
      font-size: 1.1rem;
    }
    @media (max-width: 960px) {
      .mobile-sidebar-toggle-admin {
        display: inline-flex !important;
      }
    }
    @media (max-width: 520px) {
      .admin-topbar-text {
        display: none;
      }
    }
  </style>
</head>
<body style="background: #F8FAF9; color: #1A2620;">

  <div class="dashboard-layout">

    <!-- ════ ADMIN COMPACT SIDEBAR (ONLY 4 MODULES) ════ -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      
      <!-- Brand Header -->
      <div class="sidebar-header">
        <a href="{{ auth()->user()?->is_admin ? route('admin.dashboard') : route('admin.instituciones.index') }}" class="sidebar-brand">
          <x-logo :size="22" />
          <span>a tu <em class="editorial-italic" style="color: #A8E6C0;">lado</em></span>
        </a>
      </div>

      <!-- Admin Tag -->
      <div style="padding: 0 1.25rem 0.65rem;">
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 0.22rem 0.65rem; border-radius: 6px; background: rgba(168,230,192,0.12); color: #A8E6C0; font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em;">
          <i class="fa-solid fa-shield-halved" style="font-size: 0.72rem;"></i> CONSOLA ADMINISTRADOR
        </span>
      </div>

      <!-- Quick Switch to User App -->
      <div style="padding: 0 0.85rem 0.65rem;">
        <a href="{{ route('dashboard') }}" class="admin-switch-btn" title="Ir a la aplicación como usuario">
          <i class="fa-solid fa-house-user" style="color: #A8E6C0;"></i>
          <span>Ir a la App (Mi Espacio)</span>
        </a>
      </div>

      <!-- Navigation Menu: Categorized Modules -->
      <nav class="sidebar-nav-group" style="padding: 0 0.85rem; flex: 1; overflow-y: auto;">
        @if(auth()->user()?->is_admin)
        <!-- GESTIÓN GENERAL -->
        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; text-transform: uppercase; color: #6E887E; font-weight: 600; letter-spacing: 0.08em; margin-bottom: 0.65rem; padding-left: 0.45rem;">
          Gestión General
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

        <!-- 4. Foros & Revista -->
        <a href="{{ route('admin.forums.index') }}" class="admin-nav-item {{ request()->routeIs('admin.forums.*') ? 'active' : '' }}">
          <div class="nav-icon"><i class="fa-solid fa-comments"></i></div>
          <span>Foros & Revista</span>
        </a>

        @endif

        <!-- OPERACIÓN INSTITUCIONAL B2B -->
        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; text-transform: uppercase; color: #6E887E; font-weight: 600; letter-spacing: 0.08em; margin: 1.25rem 0 0.65rem; padding-left: 0.45rem;">
          Operación Institucional (B2B)
        </div>

        <!-- 5. Instituciones -->
        <a href="{{ route('admin.instituciones.index') }}" class="admin-nav-item {{ request()->routeIs('admin.instituciones.*') ? 'active' : '' }}">
          <div class="nav-icon"><i class="fa-solid fa-building-shield"></i></div>
          <span>Instituciones</span>
        </a>

        <!-- 6. Cola de atención (casos de crisis, atención manual) -->
        @php
          $casosAbiertos = \App\Models\EventoCrisis::abiertos()->count();
          $sinAtender = \App\Models\EventoCrisis::abiertos()->whereNull('contactado_en')->count();
        @endphp
        <a href="{{ route('admin.cola.index') }}" class="admin-nav-item {{ request()->routeIs('admin.cola.*') ? 'active' : '' }}" style="position: relative;">
          <div class="nav-icon"><i class="fa-solid fa-life-ring"></i></div>
          <span>Cola de atención</span>
          @if($casosAbiertos > 0)
            <span style="margin-left: auto; background: {{ $sinAtender > 0 ? '#B02418' : '#D97706' }}; color: white; border-radius: 999px; padding: 2px 7px; font-size: 0.68rem; font-weight: 700;" title="{{ $sinAtender }} sin contacto de {{ $casosAbiertos }} abiertos">
              {{ $casosAbiertos }}
            </span>
          @endif
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

    <!-- Overlay for Mobile Sidebar in Admin -->
    <div id="adminSidebarOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 85;"></div>

    <!-- ════ MAIN ADMIN CONTENT CANVAS ════ -->
    <div class="dashboard-main">

      <!-- Admin Topbar with Mobile Hamburger, Brand & Quick Actions -->
      <header class="admin-mobile-topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
          <button id="adminMobileSidebarToggle" type="button" class="mobile-sidebar-toggle-admin" aria-label="Abrir menú de navegación">
            <i class="fa-solid fa-bars"></i>
          </button>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-family: 'Fraunces', serif; font-weight: 700; font-size: 1.05rem; color: #1A2620;">
              Consola Admin
            </span>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
          <a href="{{ route('dashboard') }}" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px; background: #EBF7EE; color: #1E4A25; border: 1px solid #B8E2C8; border-radius: 9999px; font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.8rem; text-decoration: none;" title="Abrir mi espacio de usuario">
            <i class="fa-solid fa-house-user"></i>
            <span class="admin-topbar-text">Mi Espacio</span>
          </a>

          <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px; background: #FFF1F2; color: #9F1239; border: 1px solid #FECDD3; border-radius: 9999px; font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.8rem; cursor: pointer;" title="Cerrar Sesión">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              <span class="admin-topbar-text">Salir</span>
            </button>
          </form>
        </div>
      </header>

      <!-- GLOBAL FLOATING TOAST CONTAINER (Consistente con todo el sistema A tu lado) -->
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

      <main class="dashboard-content" style="padding: clamp(1.2rem, 3vw, 2.5rem);">
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

  <!-- ════ MODAL GLOBAL DE CONFIRMACIÓN Y AVISO ADMINISTRATIVO ════ -->
  <div id="adminNoticeModal" style="display: none; position: fixed; inset: 0; background: rgba(10,20,15,0.6); z-index: 999999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: white; width: 100%; max-width: 440px; border-radius: 20px; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden; border: 1px solid rgba(0,0,0,0.08); animation: zenToastDrop 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
      <div style="padding: 1.85rem 1.65rem; text-align: center;">
        <div id="adminNoticeIconContainer" style="width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; background: #EBF7EE; color: #1E4A25;">
          <i id="adminNoticeIcon" class="fa-solid fa-circle-info"></i>
        </div>
        <h3 id="adminNoticeTitle" style="margin: 0 0 0.5rem; font-size: 1.22rem; font-weight: 700; color: #1A2620; font-family: 'Fraunces', serif;">
          Aviso Administrativo
        </h3>
        <p id="adminNoticeMessage" style="margin: 0 0 1.5rem; font-size: 0.88rem; color: #556860; line-height: 1.55;">
          Contenido del aviso
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
          <button type="button" id="adminNoticeCancelBtn" onclick="closeAdminNoticeModal()" class="btn btn-secondary" style="border-radius: 9px; padding: 0.55rem 1.25rem; font-size: 0.85rem; font-weight: 600;">
            Cancelar
          </button>
          <button type="button" id="adminNoticeConfirmBtn" class="btn btn-primary" style="border-radius: 9px; padding: 0.55rem 1.4rem; font-size: 0.85rem; font-weight: 700;">
            Entendido
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // 1. Auto-dismiss de alertas Toast tras 5 segundos con animación suave de desvanecimiento
    function initToastAutoDismiss() {
      document.querySelectorAll('.zen-toast-pill').forEach(function(toast) {
        if (toast.dataset.dismissInitialized) return;
        toast.dataset.dismissInitialized = 'true';

        setTimeout(function() {
          if (toast.isConnected) {
            toast.classList.add('toast-leaving');
          }
        }, 4500);

        setTimeout(function() {
          if (toast.isConnected) {
            toast.remove();
          }
        }, 5000);
      });
    }

    document.addEventListener('DOMContentLoaded', initToastAutoDismiss);

    // 2. Helper Global para disparar Toast de forma programática
    window.showZenToast = function(message, type) {
      type = type || 'success';
      const container = document.getElementById('zenToastContainer');
      if (!container) return;

      const toast = document.createElement('div');
      toast.className = 'zen-toast-pill ' + type;
      toast.onclick = function() { toast.remove(); };

      let iconClass = 'fa-circle-check';
      let iconColor = '#1E4A25';
      if (type === 'error') {
        iconClass = 'fa-triangle-exclamation';
        iconColor = '#922B21';
      } else if (type === 'info') {
        iconClass = 'fa-circle-info';
        iconColor = '#4A3575';
      }

      toast.innerHTML = '<i class="fa-solid ' + iconClass + '" style="font-size: 1.15rem; color: ' + iconColor + ';"></i>' +
                        '<span>' + message + '</span>' +
                        '<div class="toast-fill-bar"></div>';

      container.appendChild(toast);
      initToastAutoDismiss();
    };

    // 3. Helper Global para Modales de Confirmación (Reemplaza a console.log y confirm)
    window.showAdminNoticeModal = function(options) {
      options = options || {};
      const modal = document.getElementById('adminNoticeModal');
      if (!modal) return;

      document.getElementById('adminNoticeTitle').textContent = options.title || 'Panel de Administración';
      document.getElementById('adminNoticeMessage').innerHTML = options.message || '';

      const iconEl = document.getElementById('adminNoticeIcon');
      const iconCont = document.getElementById('adminNoticeIconContainer');
      const confirmBtn = document.getElementById('adminNoticeConfirmBtn');
      const cancelBtn = document.getElementById('adminNoticeCancelBtn');

      if (options.type === 'danger') {
        iconEl.className = 'fa-solid fa-trash-can';
        iconCont.style.background = '#FEF2F2';
        iconCont.style.color = '#DC2626';
        confirmBtn.style.background = '#DC2626';
        confirmBtn.style.borderColor = '#DC2626';
      } else if (options.type === 'edit') {
        iconEl.className = 'fa-solid fa-pen-to-square';
        iconCont.style.background = '#EBF7EE';
        iconCont.style.color = '#1E4A25';
        confirmBtn.style.background = '#2E5D4B';
        confirmBtn.style.borderColor = '#2E5D4B';
      } else {
        iconEl.className = 'fa-solid fa-circle-info';
        iconCont.style.background = '#F0F9FF';
        iconCont.style.color = '#0284C7';
        confirmBtn.style.background = '#2E5D4B';
        confirmBtn.style.borderColor = '#2E5D4B';
      }

      if (options.showCancel) {
        cancelBtn.style.display = 'inline-flex';
        cancelBtn.textContent = options.cancelText || 'Cancelar';
      } else {
        cancelBtn.style.display = 'none';
      }

      confirmBtn.textContent = options.confirmText || 'Entendido';
      confirmBtn.onclick = function() {
        closeAdminNoticeModal();
        if (typeof options.onConfirm === 'function') {
          options.onConfirm();
        }
      };

      modal.style.display = 'flex';
    };

    window.closeAdminNoticeModal = function() {
      const modal = document.getElementById('adminNoticeModal');
      if (modal) modal.style.display = 'none';
    };

    document.getElementById('adminNoticeModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeAdminNoticeModal();
      }
    });

    // 4. Admin Sidebar Mobile Toggle
    (function() {
      const toggleBtn = document.getElementById('adminMobileSidebarToggle');
      const sidebar = document.getElementById('dashboardSidebar');
      const overlay = document.getElementById('adminSidebarOverlay');

      if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function(e) {
          e.preventDefault();
          sidebar.classList.toggle('mobile-open');
          overlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
        });

        overlay.addEventListener('click', function() {
          sidebar.classList.remove('mobile-open');
          overlay.style.display = 'none';
        });

        sidebar.querySelectorAll('.admin-nav-item, .admin-switch-btn').forEach(function(item) {
          item.addEventListener('click', function() {
            if (window.innerWidth <= 960) {
              sidebar.classList.remove('mobile-open');
              overlay.style.display = 'none';
            }
          });
        });
      }
    })();
  </script>

  @stack('scripts')
</body>
</html>