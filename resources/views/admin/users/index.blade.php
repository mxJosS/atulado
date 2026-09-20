@extends('layouts.admin')

@section('title', 'Gestión y Alta de Usuarios — Consola Administrador')

@push('styles')
<style>
  .btn-user-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.35rem 0.65rem;
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    border: 1px solid transparent;
    text-decoration: none;
    line-height: 1.2;
  }
  .btn-user-edit {
    background: #F0FDF4;
    color: #166534;
    border-color: #BBF7D0;
  }
  .btn-user-edit:hover {
    background: #DCFCE7;
    color: #14532D;
    border-color: #86EFAC;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(22, 101, 52, 0.12);
  }
  .btn-user-delete {
    background: #FEF2F2;
    color: #991B1B;
    border-color: #FECACA;
  }
  .btn-user-delete:hover {
    background: #FEE2E2;
    color: #7F1D1D;
    border-color: #FCA5A5;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(153, 27, 27, 0.12);
  }
</style>
@endpush

@section('content')
<div style="max-width: 1140px; margin: 0 auto;">

  <!-- HEADER -->
  <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— CONTROL DE ACCESOS Y EQUIPO</span>
      <h1 style="font-size: 2rem; margin-top: 0.2rem; color: #1A2620; display: flex; align-items: center; gap: 12px; font-family: 'Fraunces', serif;">
        <i class="fa-solid fa-users-gear" style="color: #2E5D4B;"></i>
        <span>Directorio de Usuarios</span>
      </h1>
      <p style="color: #556860; font-size: 0.95rem; margin-top: 0.25rem;">
        Administración de cuentas, roles del sistema y registro directo de profesionales de la salud.
      </p>
    </div>

    <!-- Botón abrir modal -->
    <button type="button" onclick="openUserModal()" class="btn btn-primary" style="gap: 8px; font-size: 0.9rem; padding: 0.65rem 1.25rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(46,93,75,0.25);">
      <i class="fa-solid fa-user-plus"></i>
      <span>Dar de Alta Usuario</span>
    </button>
  </div>

  <!-- FILTROS & BÚSQUEDA -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <!-- Tabs de Rol -->
    <div style="display: flex; gap: 6px; background: #E8EFEA; padding: 4px; border-radius: 10px;">
      <a href="{{ route('admin.users.index', array_merge(request()->except('rol', 'page'), [])) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ !request('rol') ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Todos ({{ $counts['todos'] }})
      </a>
      <a href="{{ route('admin.users.index', array_merge(request()->except('rol', 'page'), ['rol' => 'admin'])) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ request('rol') === 'admin' ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Admins ({{ $counts['admins'] }})
      </a>
      <a href="{{ route('admin.users.index', array_merge(request()->except('rol', 'page'), ['rol' => 'profesional'])) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ request('rol') === 'profesional' ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Profesionales ({{ $counts['profesionales'] }})
      </a>
      <a href="{{ route('admin.users.index', array_merge(request()->except('rol', 'page'), ['rol' => 'usuario'])) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ request('rol') === 'usuario' ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Usuarios ({{ $counts['usuarios'] }})
      </a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 8px; margin: 0;">
      @if(request('rol'))
        <input type="hidden" name="rol" value="{{ request('rol') }}">
      @endif
      <div style="position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8CA399; font-size: 0.85rem;"></i>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo..." 
               class="form-control" style="padding-left: 34px; width: 280px; font-size: 0.85rem; border-radius: 9px; height: 38px;">
      </div>
      <button type="submit" class="btn btn-secondary btn-sm" style="height: 38px; border-radius: 9px; padding: 0 1rem;">
        Buscar
      </button>
      @if(request('buscar'))
        <a href="{{ route('admin.users.index', request()->except('buscar')) }}" class="btn btn-secondary btn-sm" style="height: 38px; border-radius: 9px; padding: 0 0.8rem; display: flex; align-items: center;" title="Limpiar filtro">
          <i class="fa-solid fa-xmark"></i>
        </a>
      @endif
    </form>
  </div>

  <!-- LISTADO DE USUARIOS (TABLA) -->
  <div class="card" style="padding: 0; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
        <thead>
          <tr style="background: #F4F7F5; border-bottom: 1px solid rgba(0,0,0,0.06); color: #556860; font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <th style="padding: 1rem 1.25rem;">Usuario / Cuenta</th>
            <th style="padding: 1rem 1.25rem;">Rol</th>
            <th style="padding: 1rem 1.25rem;">Cédula / Especialidad</th>
            <th style="padding: 1rem 1.25rem;">Fecha Registro</th>
            <th style="padding: 1rem 1.25rem; text-align: center;">Estado</th>
            <th style="padding: 1rem 1.25rem; text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody style="divide-y: 1px solid rgba(0,0,0,0.04);">
          @forelse($users as $user)
            <tr style="border-bottom: 1px solid rgba(0,0,0,0.04); transition: background 0.15s ease;" onmouseover="this.style.background='#FBFDFB'" onmouseout="this.style.background='transparent'">
              <!-- Nombre y Avatar -->
              <td style="padding: 1rem 1.25rem;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <div class="avatar {{ $user->avatar_color ?? 'sage' }}" style="width: 38px; height: 38px; border-radius: 50%; font-size: 0.88rem; font-weight: 700; display: flex; align-items: center; justify-content: center; background: #E8EFEA; color: #2E5D4B; flex-shrink: 0;">
                    @if($user->avatar_url)
                      <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    @else
                      {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                  </div>
                  <div>
                    <div style="font-weight: 700; color: #1A2620; display: flex; align-items: center; gap: 6px;">
                      <span>{{ $user->name }}</span>
                      @if($user->is_admin)
                        <i class="fa-solid fa-shield-halved" style="color: #2E5D4B; font-size: 0.82rem;" title="Administrador"></i>
                      @elseif($user->role === 'profesional')
                        <i class="fa-solid fa-circle-check" style="color: #0E7490; font-size: 0.82rem;" title="Profesional Acreditado"></i>
                      @endif
                    </div>
                    <div style="font-size: 0.78rem; color: #6E887E;">
                      {{ $user->email }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Rol Badge -->
              <td style="padding: 1rem 1.25rem;">
                @if($user->is_admin || $user->role === 'admin')
                  <span style="display: inline-flex; align-items: center; gap: 5px; padding: 0.25rem 0.65rem; border-radius: 6px; background: #1A2620; color: #A8E6C0; font-size: 0.72rem; font-weight: 700; font-family: 'IBM Plex Mono', monospace;">
                    <i class="fa-solid fa-shield"></i> Administrador
                  </span>
                @elseif($user->role === 'profesional')
                  <span style="display: inline-flex; align-items: center; gap: 5px; padding: 0.25rem 0.65rem; border-radius: 6px; background: #E0F2FE; color: #0369A1; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-user-doctor"></i> Profesional
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 5px; padding: 0.25rem 0.65rem; border-radius: 6px; background: #F1F5F9; color: #475569; font-size: 0.72rem; font-weight: 600;">
                    <i class="fa-solid fa-user"></i> Usuario
                  </span>
                @endif
              </td>

              <!-- Cédula / Especialidad -->
              <td style="padding: 1rem 1.25rem;">
                @if($user->license_number)
                  <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.82rem; font-weight: 600; color: #1A2620;">
                    {{ $user->license_number }}
                  </div>
                  <div style="font-size: 0.74rem; color: #6E887E;">
                    {{ $user->professional_title ?? 'Especialista' }}
                  </div>
                @else
                  <span style="color: #9CA3AF; font-size: 0.8rem;">—</span>
                @endif
              </td>

              <!-- Fecha Registro -->
              <td style="padding: 1rem 1.25rem; font-size: 0.82rem; color: #556860;">
                {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
              </td>

              <!-- Estado -->
              <td style="padding: 1rem 1.25rem; text-align: center;">
                @if($user->email_verified_at)
                  <span style="color: #059669; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i> Activo
                  </span>
                @else
                  <span style="color: #D97706; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i> Pendiente
                  </span>
                @endif
              </td>

              <!-- Acciones (Editar, Eliminar) -->
              <td style="padding: 1rem 1.25rem; text-align: right;">
                <div style="display: inline-flex; align-items: center; gap: 6px; justify-content: flex-end;">
                  @php
                    $isTargetSuperAdmin = $user->isSuperAdmin();
                    $isSelf = $user->id === auth()->id();
                    $currentUserIsSuperAdmin = auth()->user()?->isSuperAdmin();
                    
                    // Si es la cuenta protegida, solo ella misma puede editarse
                    $canEdit = !$isTargetSuperAdmin || $currentUserIsSuperAdmin;

                    // La cuenta protegida NO se puede eliminar bajo ninguna circunstancia
                    // Para eliminar a otro admin, se requiere ser la cuenta principal
                    // Para eliminar usuarios/profesionales, cualquier admin puede hacerlo
                    $canDelete = false;
                    if (!$isTargetSuperAdmin && !$isSelf) {
                        if ($user->is_admin || $user->role === 'admin') {
                            $canDelete = $currentUserIsSuperAdmin;
                        } else {
                            $canDelete = true;
                        }
                    }
                  @endphp

                  @if($canEdit)
                    <button type="button" 
                            onclick="openEditUserModal({{ json_encode([
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->role,
                                'is_admin' => $user->is_admin,
                                'status' => $user->email_verified_at ? 'activo' : 'pendiente',
                                'license_number' => $user->license_number ?? '',
                                'institution' => $user->institution ?? '',
                                'is_self' => $isSelf,
                                'is_super_admin' => $isTargetSuperAdmin,
                            ]) }})"
                            class="btn-user-action btn-user-edit"
                            title="Editar usuario"
                            aria-label="Editar usuario">
                      <i class="fa-solid fa-pen-to-square"></i>
                      <span>Editar</span>
                    </button>
                  @endif

                  @if($canDelete)
                    <button type="button" 
                            onclick="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', false)"
                            class="btn-user-action btn-user-delete"
                            title="Eliminar usuario"
                            aria-label="Eliminar usuario">
                      <i class="fa-solid fa-trash-can"></i>
                      <span>Eliminar</span>
                    </button>
                  @elseif($isTargetSuperAdmin)
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.3rem 0.6rem; border-radius: 7px; font-size: 0.72rem; font-family: 'IBM Plex Mono', monospace; color: #556860; background: #EEF3F0; border: 1px solid #DCE5E0;" title="Cuenta protegida del sistema">
                      <i class="fa-solid fa-lock" style="font-size: 0.68rem; color: #2E5D4B;"></i>
                      <span>Protegido</span>
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align: center; padding: 3rem; color: #6E887E;">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 2rem; color: #CBD5E1; margin-bottom: 0.75rem; display: block;"></i>
                No se encontraron usuarios con los criterios de búsqueda especificados.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    @if($users->hasPages())
      <div style="padding: 1rem 1.25rem; background: #F8FAF9; border-top: 1px solid rgba(0,0,0,0.06);">
        {{ $users->links() }}
      </div>
    @endif
  </div>

</div>

<!-- ════ MODAL: DAR DE ALTA USUARIO ════ -->
<div id="userModal" style="display: none; position: fixed; inset: 0; background: rgba(10,20,15,0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.5rem;">
  <div style="background: white; width: 100%; max-width: 620px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden; border: 1px solid rgba(0,0,0,0.08); max-height: 90vh; display: flex; flex-direction: column;">
    
    <!-- Modal Header -->
    <div style="padding: 1.35rem 1.75rem; background: #F8FAF9; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1A2620; font-family: 'Fraunces', serif;">
          Dar de Alta Usuario
        </h3>
        <p style="margin: 3px 0 0; font-size: 0.82rem; color: #6E887E;">
          Crea una cuenta oficial para miembros del equipo o especialistas de salud.
        </p>
      </div>
      <button type="button" onclick="closeUserModal()" style="background: transparent; border: none; font-size: 1.2rem; color: #8CA399; cursor: pointer; padding: 4px;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Modal Body / Form -->
    <div style="padding: 1.75rem; overflow-y: auto;">
      <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <!-- Nombres y Apellidos en 2 columnas -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
              Nombre(s) <span style="color: #DC2626;">*</span>
            </label>
            <input type="text" name="first_name" required value="{{ old('first_name') }}" class="form-control" placeholder="Ej. Ana Laura" style="border-radius: 9px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
              Apellidos <span style="color: #DC2626;">*</span>
            </label>
            <input type="text" name="last_name" required value="{{ old('last_name') }}" class="form-control" placeholder="Ej. Mendoza Cruz" style="border-radius: 9px;">
          </div>
        </div>

        <!-- Correo Electrónico -->
        <div style="margin-bottom: 1.15rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
            Correo Electrónico <span style="color: #DC2626;">*</span>
          </label>
          <input type="email" name="email" required value="{{ old('email') }}" class="form-control" placeholder="usuario@atulado.com.mx" style="border-radius: 9px;">
        </div>

        <!-- Contraseña y Confirmación -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.35rem;">
          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
              Contraseña <span style="color: #DC2626;">*</span>
            </label>
            <input type="password" name="password" required class="form-control" placeholder="Mínimo 6 caracteres" style="border-radius: 9px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
              Confirmar Contraseña <span style="color: #DC2626;">*</span>
            </label>
            <input type="password" name="password_confirmation" required class="form-control" placeholder="Repite la contraseña" style="border-radius: 9px;">
          </div>
        </div>

        <!-- ROL SELECTOR (Administrador | Profesional | Usuario) -->
        <div style="margin-bottom: 1.5rem; background: #F8FAF9; padding: 1.15rem; border-radius: 12px; border: 1px solid rgba(0,0,0,0.06);">
          <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #1A2620; margin-bottom: 0.5rem; display: block;">
            Selecciona el Rol de la Cuenta <span style="color: #DC2626;">*</span>
          </label>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
            <!-- Opción Administrador -->
            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 0.85rem; border-radius: 10px; border: 2px solid #E2E8F0; background: white; cursor: pointer; transition: all 0.2s ease;" id="labelRoleAdmin">
              <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }} onchange="toggleRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #1A2620;">Administrador</div>
                <div style="font-size: 0.75rem; color: #6E887E; margin-top: 2px;">Acceso a la consola gerencial y auditoría.</div>
              </div>
            </label>

            <!-- Opción Profesional -->
            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 0.85rem; border-radius: 10px; border: 2px solid #E2E8F0; background: white; cursor: pointer; transition: all 0.2s ease;" id="labelRolePro">
              <input type="radio" name="role" value="profesional" {{ old('role') === 'profesional' ? 'checked' : '' }} onchange="toggleRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #1A2620;">Profesional</div>
                <div style="font-size: 0.75rem; color: #6E887E; margin-top: 2px;">Especialista de salud acreditado.</div>
              </div>
            </label>

            <!-- Opción Usuario Normal / Paciente -->
            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 0.85rem; border-radius: 10px; border: 2px solid #2E5D4B; background: #F8FAF9; cursor: pointer; transition: all 0.2s ease;" id="labelRoleUser">
              <input type="radio" name="role" value="usuario" {{ (!old('role') || old('role') === 'usuario') ? 'checked' : '' }} onchange="toggleRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #1A2620;">Usuario / Paciente</div>
                <div style="font-size: 0.75rem; color: #6E887E; margin-top: 2px;">Cuenta regular con acceso a Mi Espacio.</div>
              </div>
            </label>
          </div>
        </div>

        <!-- CAMPOS DINÁMICOS PARA PROFESIONAL (VALIDACIÓN AUTOMÁTICA) -->
        <div id="proFieldsContainer" style="display: none; background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 14px; padding: 1.35rem; margin-bottom: 1.5rem;">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem;">
            <i class="fa-solid fa-certificate" style="color: #16A34A; font-size: 1.1rem;"></i>
            <div style="font-weight: 700; font-size: 0.92rem; color: #15803D;">
              Acreditación Inmediata de Especialista
            </div>
          </div>
          <p style="font-size: 0.78rem; color: #166534; margin: 0 0 1rem;">
            Al dar de alta este profesional, sus credenciales se validarán automáticamente en el sistema sin necesidad de trámites ni alertas posteriores.
          </p>

          <!-- Especialidad o Enfoque Clínico (Opcional) -->
          <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #166534; margin-bottom: 0.4rem; display: block;">
              Especialidad o Área de Salud (Opcional)
            </label>
            <input type="text" name="specialty" value="{{ old('specialty') }}" class="form-control" placeholder="Ej. Nutrición Clínica, Psicología, Psiquiatría, etc." style="border-radius: 9px; background: white;">
          </div>

          <!-- Grado Escolar -->
          <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #166534; margin-bottom: 0.4rem; display: block;">
              Grado Escolar <span style="color: #DC2626;">*</span>
            </label>
            <select name="education_level" id="educationLevelSelect" class="form-control" style="border-radius: 9px; background: white;">
              <option value="" disabled selected>-- Selecciona el grado académico --</option>
              <option value="licenciatura" {{ old('education_level') === 'licenciatura' ? 'selected' : '' }}>Licenciatura</option>
              <option value="especialidad" {{ old('education_level') === 'especialidad' ? 'selected' : '' }}>Especialidad</option>
              <option value="maestria" {{ old('education_level') === 'maestria' ? 'selected' : '' }}>Maestría</option>
              <option value="doctorado" {{ old('education_level') === 'doctorado' ? 'selected' : '' }}>Doctorado</option>
            </select>
          </div>

          <!-- Número de Cédula Profesional -->
          <div style="margin-bottom: 1rem;">
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #166534; margin-bottom: 0.4rem; display: block;">
              Número de Cédula Profesional <span style="color: #DC2626;">*</span>
            </label>
            <input type="text" name="license_number" id="licenseNumberInput" value="{{ old('license_number') }}" class="form-control" placeholder="Ej. 12345678" style="border-radius: 9px; background: white;">
          </div>

          <!-- Institución (Opcional) -->
          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #166534; margin-bottom: 0.4rem; display: block;">
              Institución Universitaria / Hospital (Opcional)
            </label>
            <input type="text" name="institution" value="{{ old('institution') }}" class="form-control" placeholder="Ej. UNAM / Instituto Nacional de Psiquiatría" style="border-radius: 9px; background: white;">
          </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid rgba(0,0,0,0.06);">
          <button type="button" onclick="closeUserModal()" class="btn btn-secondary" style="border-radius: 9px; padding: 0.65rem 1.25rem;">
            Cancelar
          </button>
          <button type="submit" class="btn btn-primary" style="border-radius: 9px; padding: 0.65rem 1.45rem; font-weight: 700;">
            <i class="fa-solid fa-check"></i>
            <span>Guardar y Dar de Alta</span>
          </button>
        </div>

      </form>
    </div>

  </div>
</div>

<!-- ════ MODAL: EDITAR USUARIO ════ -->
<div id="editUserModal" style="display: none; position: fixed; inset: 0; background: rgba(10,20,15,0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.5rem;">
  <div style="background: white; width: 100%; max-width: 580px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden; border: 1px solid rgba(0,0,0,0.08); max-height: 90vh; display: flex; flex-direction: column;">
    
    <!-- Modal Header -->
    <div style="padding: 1.35rem 1.75rem; background: #F8FAF9; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1A2620; font-family: 'Fraunces', serif;">
          Editar Usuario
        </h3>
        <p style="margin: 3px 0 0; font-size: 0.82rem; color: #6E887E;">
          Modifica los accesos, rol y estado de la cuenta en el sistema.
        </p>
      </div>
      <button type="button" onclick="closeEditUserModal()" style="background: transparent; border: none; font-size: 1.2rem; color: #8CA399; cursor: pointer; padding: 4px;">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div style="padding: 1.75rem; overflow-y: auto;">
      <form id="editUserForm" method="POST" action="">
        @csrf
        @method('PUT')

        <!-- Nombre Completo -->
        <div style="margin-bottom: 1.15rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
            Nombre Completo <span style="color: #DC2626;">*</span>
          </label>
          <input type="text" name="name" id="editUserName" required class="form-control" style="border-radius: 9px;">
        </div>

        <!-- Correo Electrónico -->
        <div style="margin-bottom: 1.15rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
            Correo Electrónico <span style="color: #DC2626;">*</span>
          </label>
          <input type="email" name="email" id="editUserEmail" required class="form-control" style="border-radius: 9px;">
        </div>

        <!-- Selector de Rol (Usuario | Profesional | Administrador) -->
        <div style="margin-bottom: 1.25rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.45rem; display: block;">
            Rol del Sistema <span style="color: #DC2626;">*</span>
          </label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.65rem;">
            <!-- Usuario -->
            <label style="display: flex; align-items: flex-start; gap: 8px; padding: 0.75rem; border-radius: 10px; border: 2px solid #E2E8F0; background: white; cursor: pointer; transition: all 0.2s ease;" id="editLabelRoleUser">
              <input type="radio" name="role" value="usuario" id="editRoleUser" onchange="toggleEditRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #1A2620; display: flex; align-items: center; gap: 4px;">
                  <i class="fa-solid fa-user" style="color: #475569;"></i> Usuario
                </div>
                <div style="font-size: 0.72rem; color: #6E887E; margin-top: 2px;">Cuenta estándar.</div>
              </div>
            </label>

            <!-- Profesional -->
            <label style="display: flex; align-items: flex-start; gap: 8px; padding: 0.75rem; border-radius: 10px; border: 2px solid #E2E8F0; background: white; cursor: pointer; transition: all 0.2s ease;" id="editLabelRolePro">
              <input type="radio" name="role" value="profesional" id="editRolePro" onchange="toggleEditRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #1A2620; display: flex; align-items: center; gap: 4px;">
                  <i class="fa-solid fa-user-doctor" style="color: #0E7490;"></i> Profesional
                </div>
                <div style="font-size: 0.72rem; color: #6E887E; margin-top: 2px;">Acreditado.</div>
              </div>
            </label>

            <!-- Administrador -->
            <label style="display: flex; align-items: flex-start; gap: 8px; padding: 0.75rem; border-radius: 10px; border: 2px solid #E2E8F0; background: white; cursor: pointer; transition: all 0.2s ease;" id="editLabelRoleAdmin">
              <input type="radio" name="role" value="admin" id="editRoleAdmin" onchange="toggleEditRoleFields()" style="margin-top: 3px;">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #1A2620; display: flex; align-items: center; gap: 4px;">
                  <i class="fa-solid fa-shield-halved" style="color: #2E5D4B;"></i> Admin
                </div>
                <div style="font-size: 0.72rem; color: #6E887E; margin-top: 2px;">Control total.</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Estado de la Cuenta (Activo vs Pendiente) -->
        <div style="margin-bottom: 1.25rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.45rem; display: block;">
            Estado de Verificación de la Cuenta <span style="color: #DC2626;">*</span>
          </label>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <label style="display: flex; align-items: center; gap: 8px; padding: 0.65rem 0.85rem; border-radius: 9px; border: 1.5px solid #A7F3D0; background: #ECFDF5; cursor: pointer; font-size: 0.82rem; font-weight: 700; color: #065F46;">
              <input type="radio" name="status" value="activo" id="editStatusActivo">
              <i class="fa-solid fa-circle-check" style="color: #059669;"></i>
              <span>Activo (Verificado)</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; padding: 0.65rem 0.85rem; border-radius: 9px; border: 1.5px solid #FDE68A; background: #FFFBEB; cursor: pointer; font-size: 0.82rem; font-weight: 700; color: #92400E;">
              <input type="radio" name="status" value="pendiente" id="editStatusPendiente">
              <i class="fa-solid fa-hourglass-half" style="color: #D97706;"></i>
              <span>Pendiente</span>
            </label>
          </div>
        </div>

        <!-- Campos de Profesional (Cédula / Institución) -->
        <div id="editProFieldsContainer" style="display: none; margin-bottom: 1.25rem; background: #F0FDF4; padding: 1rem; border-radius: 10px; border: 1px solid #BBF7D0;">
          <div style="margin-bottom: 0.85rem;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem; display: block;">
              Número de Cédula Profesional
            </label>
            <input type="text" name="license_number" id="editUserLicense" class="form-control" placeholder="Ej. 12345678" style="border-radius: 9px; background: white;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem; display: block;">
              Institución Universitaria / Hospital (Opcional)
            </label>
            <input type="text" name="institution" id="editUserInstitution" class="form-control" placeholder="Ej. UNAM" style="border-radius: 9px; background: white;">
          </div>
        </div>

        <!-- Nueva Contraseña (Opcional) -->
        <div style="margin-bottom: 1.5rem;">
          <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #2D3748; margin-bottom: 0.4rem; display: block;">
            Restablecer Contraseña (Opcional)
          </label>
          <input type="password" name="password" placeholder="Dejar en blanco para conservar la actual" class="form-control" style="border-radius: 9px;">
          <span style="font-size: 0.74rem; color: #6E887E; margin-top: 3px; display: block;">Si escribes una nueva contraseña, debe tener al menos 6 caracteres.</span>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 1.25rem; border-top: 1px solid rgba(0,0,0,0.06);">
          <button type="button" onclick="closeEditUserModal()" class="btn btn-secondary" style="border-radius: 9px; padding: 0.65rem 1.25rem;">
            Cancelar
          </button>
          <button type="submit" class="btn btn-primary" style="border-radius: 9px; padding: 0.65rem 1.45rem; font-weight: 700;">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Guardar Cambios</span>
          </button>
        </div>

      </form>
    </div>

  </div>
</div>

<!-- Formulario Oculto para Eliminar Usuarios -->
<form id="globalDeleteUserForm" method="POST" action="" style="display: none;">
  @csrf
  @method('DELETE')
</form>

@push('scripts')
<script>
  // ── Modal Crear Usuario ──────────────────────────────
  function openUserModal() {
    document.getElementById('userModal').style.display = 'flex';
    toggleRoleFields();
  }

  function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
  }

  function toggleRoleFields() {
    const isPro = document.querySelector('input[name="role"][value="profesional"]').checked;
    const isAdmin = document.querySelector('input[name="role"][value="admin"]').checked;
    const proContainer = document.getElementById('proFieldsContainer');
    const eduSelect = document.getElementById('educationLevelSelect');
    const licInput = document.getElementById('licenseNumberInput');
    const labelAdmin = document.getElementById('labelRoleAdmin');
    const labelPro = document.getElementById('labelRolePro');
    const labelUser = document.getElementById('labelRoleUser');

    if (isPro) {
      proContainer.style.display = 'block';
      eduSelect.setAttribute('required', 'required');
      licInput.setAttribute('required', 'required');
      labelPro.style.borderColor = '#2E5D4B';
      labelPro.style.background = '#F0FDF4';
      if (labelAdmin) { labelAdmin.style.borderColor = '#E2E8F0'; labelAdmin.style.background = 'white'; }
      if (labelUser) { labelUser.style.borderColor = '#E2E8F0'; labelUser.style.background = 'white'; }
    } else {
      proContainer.style.display = 'none';
      eduSelect.removeAttribute('required');
      licInput.removeAttribute('required');
      labelPro.style.borderColor = '#E2E8F0';
      labelPro.style.background = 'white';

      if (isAdmin) {
        if (labelAdmin) { labelAdmin.style.borderColor = '#2E5D4B'; labelAdmin.style.background = '#F8FAF9'; }
        if (labelUser) { labelUser.style.borderColor = '#E2E8F0'; labelUser.style.background = 'white'; }
      } else {
        if (labelUser) { labelUser.style.borderColor = '#2E5D4B'; labelUser.style.background = '#F8FAF9'; }
        if (labelAdmin) { labelAdmin.style.borderColor = '#E2E8F0'; labelAdmin.style.background = 'white'; }
      }
    }
  }

  // ── Modal Editar Usuario ──────────────────────────────
  function openEditUserModal(user) {
    const form = document.getElementById('editUserForm');
    form.action = '/admin/usuarios/' + user.id;

    const emailInput = document.getElementById('editUserEmail');
    const roleAdmin = document.getElementById('editRoleAdmin');
    const rolePro = document.getElementById('editRolePro');
    const roleUser = document.getElementById('editRoleUser');
    const statusPendiente = document.getElementById('editStatusPendiente');

    document.getElementById('editUserName').value = user.name || '';
    emailInput.value = user.email || '';

    // Si es la cuenta principal protegida
    if (user.is_super_admin) {
      emailInput.readOnly = true;
      emailInput.style.background = '#F3F4F6';
      roleAdmin.checked = true;
      rolePro.disabled = true;
      roleUser.disabled = true;
      statusPendiente.disabled = true;
    } else {
      emailInput.readOnly = false;
      emailInput.style.background = 'white';
      rolePro.disabled = false;
      roleUser.disabled = false;
      statusPendiente.disabled = false;

      // Rol (Administrador, Profesional o Usuario)
      if (user.is_admin || user.role === 'admin') {
        roleAdmin.checked = true;
      } else if (user.role === 'profesional') {
        rolePro.checked = true;
      } else {
        roleUser.checked = true;
      }
    }

    // Estado (Activo vs Pendiente)
    if (user.status === 'activo' || user.is_super_admin) {
      document.getElementById('editStatusActivo').checked = true;
    } else {
      document.getElementById('editStatusPendiente').checked = true;
    }

    // Profesional
    document.getElementById('editUserLicense').value = user.license_number || '';
    document.getElementById('editUserInstitution').value = user.institution || '';

    toggleEditRoleFields();
    document.getElementById('editUserModal').style.display = 'flex';
  }

  function closeEditUserModal() {
    document.getElementById('editUserModal').style.display = 'none';
  }

  function toggleEditRoleFields() {
    const isPro = document.getElementById('editRolePro').checked;
    const isAdmin = document.getElementById('editRoleAdmin').checked;
    const isUser = document.getElementById('editRoleUser').checked;
    const proContainer = document.getElementById('editProFieldsContainer');
    const labelAdmin = document.getElementById('editLabelRoleAdmin');
    const labelPro = document.getElementById('editLabelRolePro');
    const labelUser = document.getElementById('editLabelRoleUser');

    proContainer.style.display = isPro ? 'block' : 'none';

    if (isPro) {
      labelPro.style.borderColor = '#0E7490';
      labelPro.style.background = '#F0FDFA';
      labelAdmin.style.borderColor = '#E2E8F0';
      labelAdmin.style.background = 'white';
      labelUser.style.borderColor = '#E2E8F0';
      labelUser.style.background = 'white';
    } else if (isAdmin) {
      labelAdmin.style.borderColor = '#2E5D4B';
      labelAdmin.style.background = '#F8FAF9';
      labelPro.style.borderColor = '#E2E8F0';
      labelPro.style.background = 'white';
      labelUser.style.borderColor = '#E2E8F0';
      labelUser.style.background = 'white';
    } else {
      labelUser.style.borderColor = '#475569';
      labelUser.style.background = '#F8FAFC';
      labelAdmin.style.borderColor = '#E2E8F0';
      labelAdmin.style.background = 'white';
      labelPro.style.borderColor = '#E2E8F0';
      labelPro.style.background = 'white';
    }
  }

  // ── Eliminar Usuario con Modal de Confirmación ────────
  function confirmDeleteUser(userId, userName, userEmail, isSelf) {
    if (userEmail && userEmail.toLowerCase() === 'admin@atulado.com.mx') {
      showAdminNoticeModal({
        title: 'Acción No Permitida',
        message: 'La cuenta principal de administración está protegida y no puede ser eliminada.',
        type: 'danger',
        confirmText: 'Entendido'
      });
      return;
    }

    if (isSelf) {
      showAdminNoticeModal({
        title: 'Acción No Permitida',
        message: 'No puedes eliminar tu propia cuenta de administrador en sesión.',
        type: 'danger',
        confirmText: 'Entendido'
      });
      return;
    }

    showAdminNoticeModal({
      title: 'Confirmar Eliminación',
      message: '¿Estás seguro de que deseas eliminar permanentemente al usuario <strong>' + userName + '</strong> (' + userEmail + ')?<br><br><span style="color: #DC2626; font-size: 0.82rem; font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> Se eliminarán sus accesos y registros asociados. Esta acción es irreversible.</span>',
      type: 'danger',
      showCancel: true,
      cancelText: 'Cancelar',
      confirmText: 'Sí, Eliminar Usuario',
      onConfirm: function() {
        const form = document.getElementById('globalDeleteUserForm');
        form.action = '/admin/usuarios/' + userId;
        form.submit();
      }
    });
  }

  // Si hay error en validación previa abrir modal automáticamente
  @if($errors->any() && (old('first_name') || old('email')))
    document.addEventListener('DOMContentLoaded', function() {
      openUserModal();
    });
  @endif

  // Cerrar al dar click fuera
  document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeUserModal();
  });
  document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditUserModal();
  });
</script>
@endpush
@endsection