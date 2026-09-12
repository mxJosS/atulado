@extends('layouts.admin')

@section('title', 'Dashboard Gerencial & Métricas — Panel Administrador')

@section('content')
<div style="max-width: 1120px; margin: 0 auto;">

  <!-- TOP HEADER -->
  <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— CONSOLA EJECUTIVA Y DE AUDITORÍA</span>
      <h1 style="font-size: 2.1rem; margin-top: 0.2rem; color: #1A2620; display: flex; align-items: center; gap: 12px; font-family: 'Fraunces', serif;">
        <i class="fa-solid fa-gauge-high" style="color: #2E5D4B;"></i>
        <span>Torre de Control A tu lado</span>
      </h1>
      <p style="color: #556860; font-size: 0.95rem; margin-top: 0.35rem;">
        Visión global de usuarios registrados, especialistas clínicos acreditados y actividad científica del ecosistema.
      </p>
    </div>

    <!-- Quick Action Buttons -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm" style="gap: 8px; font-size: 0.85rem; padding: 0.6rem 1.1rem; border-radius: 9px;">
        <i class="fa-solid fa-user-plus"></i>
        <span>Dar de Alta Usuario</span>
      </a>
      <a href="{{ route('admin.verifications.index') }}" class="btn btn-secondary btn-sm" style="gap: 8px; font-size: 0.85rem; padding: 0.6rem 1.1rem; border-radius: 9px;">
        <i class="fa-solid fa-id-card-clip"></i>
        <span>Auditar Cédulas</span>
      </a>
    </div>
  </div>

  <!-- 4 PRIMARY KPI METRIC CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
    
    <!-- 1. Total Usuarios -->
    <div class="card" style="padding: 1.35rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; text-transform: uppercase; color: #6E887E; font-weight: 600;">Total Usuarios</span>
        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(46,93,75,0.1); color: #2E5D4B; display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>
      <div style="font-size: 2.3rem; font-weight: 800; color: #1A2620; font-family: 'IBM Plex Mono', monospace; line-height: 1;">
        {{ $totalUsers }}
      </div>
      <div style="margin-top: 0.85rem; font-size: 0.82rem; color: #556860; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <span><strong style="color: #2E5D4B;">{{ $totalAdmins }}</strong> Admins</span>
        <span>•</span>
        <span><strong style="color: #0E7490;">{{ $totalProfessionals }}</strong> Profesionales</span>
        <span>•</span>
        <span><strong style="color: #475569;">{{ $totalRegularUsers }}</strong> Usuarios</span>
      </div>
    </div>

    <!-- 2. Solicitudes por Auditar -->
    <div class="card" style="padding: 1.35rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; text-transform: uppercase; color: #D97706; font-weight: 600;">Acreditación Pendiente</span>
        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(217,119,6,0.1); color: #D97706; display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-hourglass-half"></i>
        </div>
      </div>
      <div style="font-size: 2.3rem; font-weight: 800; color: #D97706; font-family: 'IBM Plex Mono', monospace; line-height: 1;">
        {{ $pendingVerifications }}
      </div>
      <div style="margin-top: 0.85rem; font-size: 0.82rem; color: #556860;">
        <span style="color: #059669; font-weight: 600;">{{ $approvedVerifications }}</span> cédulas aprobadas en histórico
      </div>
    </div>

    <!-- 3. Artículos en Revista / Foro -->
    <div class="card" style="padding: 1.35rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; text-transform: uppercase; color: #6E887E; font-weight: 600;">Revista & Foros</span>
        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(14,116,144,0.1); color: #0E7490; display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-newspaper"></i>
        </div>
      </div>
      <div style="font-size: 2.3rem; font-weight: 800; color: #1A2620; font-family: 'IBM Plex Mono', monospace; line-height: 1;">
        {{ $totalArticles }}
      </div>
      <div style="margin-top: 0.85rem; font-size: 0.82rem; color: #556860;">
        Publicaciones con respaldo clínico
      </div>
    </div>

    <!-- 4. Registros Emocionales / Check-ins -->
    <div class="card" style="padding: 1.35rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; text-transform: uppercase; color: #6E887E; font-weight: 600;">Check-ins Emocionales</span>
        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(90,181,110,0.15); color: #2D6B3A; display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-heart-pulse"></i>
        </div>
      </div>
      <div style="font-size: 2.3rem; font-weight: 800; color: #1A2620; font-family: 'IBM Plex Mono', monospace; line-height: 1;">
        {{ $totalMoodLogs }}
      </div>
      <div style="margin-top: 0.85rem; font-size: 0.82rem; color: #556860;">
        Monitoreos de bienestar completados
      </div>
    </div>

  </div>

  <!-- 2 COLUMNS: RECENT AUDITS & RECENT USERS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.5rem;">
    
    <!-- LEFT: SOLICITUDES DE ACREDITACIÓN RECIENTES -->
    <div class="card" style="padding: 1.6rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-id-badge" style="color: #2E5D4B;"></i>
          <span>Acreditaciones Recientes</span>
        </h2>
        <a href="{{ route('admin.verifications.index') }}" style="font-size: 0.82rem; color: #2E5D4B; font-weight: 600; text-decoration: none;">
          Ver todas &rarr;
        </a>
      </div>

      @if($recentVerifications->isEmpty())
        <div style="text-align: center; padding: 2rem; color: #6E887E; font-size: 0.9rem;">
          <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #A8E6C0; margin-bottom: 0.5rem; display: block;"></i>
          No hay solicitudes de verificación registradas en este momento.
        </div>
      @else
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
          @foreach($recentVerifications as $verif)
            <div style="padding: 0.95rem; border-radius: 12px; background: #F8FAF9; border: 1px solid rgba(0,0,0,0.04); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
              <div>
                <div style="font-weight: 700; font-size: 0.92rem; color: #1A2620;">
                  {{ $verif->full_name }}
                </div>
                <div style="font-size: 0.78rem; color: #556860; margin-top: 2px;">
                  Cédula: <code style="background: rgba(0,0,0,0.05); padding: 1px 5px; border-radius: 4px;">{{ $verif->license_number }}</code> • {{ $verif->education_level_label }}
                </div>
              </div>

              <div>
                @if($verif->status === 'pendiente')
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.65rem; border-radius: 999px; background: #FEF3C7; color: #92400E; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-clock"></i> Pendiente
                  </span>
                @elseif($verif->status === 'aprobada')
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.65rem; border-radius: 999px; background: #D1FAE5; color: #065F46; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Acreditada
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.65rem; border-radius: 999px; background: #FEE2E2; color: #991B1B; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-xmark"></i> Rechazada
                  </span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <!-- RIGHT: ÚLTIMOS USUARIOS DADOS DE ALTA (GRID UNIFORME RESPONSIVO) -->
    <div class="card" style="padding: 1.6rem; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.15rem; font-weight: 700; color: #1A2620; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-user-group" style="color: #2E5D4B;"></i>
          <span>Últimos Usuarios Registrados</span>
        </h2>
        <a href="{{ route('admin.users.index') }}" style="font-size: 0.82rem; color: #2E5D4B; font-weight: 600; text-decoration: none;">
          Ver todos &rarr;
        </a>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem;">
        @foreach($recentUsers as $usr)
          <div style="padding: 0.95rem; border-radius: 12px; background: #F8FAF9; border: 1px solid rgba(0,0,0,0.04); display: flex; justify-content: space-between; align-items: center; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
              <div style="width: 36px; height: 36px; font-size: 0.82rem; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #E8EFEA; color: #2E5D4B; flex-shrink: 0;">
                @if($usr->avatar_url)
                  <img src="{{ $usr->avatar_url }}" alt="{{ $usr->name }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                @else
                  {{ strtoupper(substr($usr->name, 0, 1)) }}
                @endif
              </div>
              <div style="min-width: 0;">
                <div style="font-weight: 700; font-size: 0.88rem; color: #1A2620; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ $usr->name }}
                </div>
                <div style="font-size: 0.74rem; color: #6E887E; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ $usr->email }}
                </div>
              </div>
            </div>

            <div style="flex-shrink: 0;">
              @if($usr->is_admin || $usr->role === 'admin')
                <span style="padding: 0.2rem 0.55rem; border-radius: 6px; background: #1A2620; color: #A8E6C0; font-size: 0.7rem; font-weight: 700;">
                  Admin
                </span>
              @elseif($usr->role === 'profesional')
                <span style="padding: 0.2rem 0.55rem; border-radius: 6px; background: #E0F2FE; color: #0369A1; font-size: 0.7rem; font-weight: 700;">
                  Profesional
                </span>
              @else
                <span style="padding: 0.2rem 0.55rem; border-radius: 6px; background: #F1F5F9; color: #475569; font-size: 0.7rem; font-weight: 700;">
                  Usuario
                </span>
              @endif
            </div>
          </div>
        @endforeach
      </div>

    </div>

  </div>

</div>
@endsection