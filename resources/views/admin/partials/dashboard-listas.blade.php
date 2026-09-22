{{-- Listas que se conservan del dashboard anterior --}}
  <!-- 2 COLUMNS: RECENT AUDITS & RECENT USERS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(460px, 100%), 1fr)); gap: 1.5rem;">
    
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

