@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Mi Perfil y Preferencias')

@php
  $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

@section('content')
<div style="max-width: 800px; margin: 0 auto;">

  <div style="margin-bottom: 1.75rem;">
    <span class="mono-tag" style="color: var(--sage-base);">— CONFIGURACIÓN DE CUENTA</span>
    <h1 style="font-size: 1.85rem; margin-top: 0.15rem; color: var(--text-near-black); display: flex; align-items: center; gap: 8px;">
      <span>Mi Perfil</span>
      @if($user->is_admin)
        <span style="background: rgba(46, 93, 75, 0.15); color: #2E5D4B; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 9999px; font-family: var(--font-mono); letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;">
          <i class="fa-solid fa-shield-halved"></i> ADMINISTRADOR
        </span>
      @elseif($user->isProfessional())
        <x-verified-badge size="22" />
      @endif
    </h1>
    <p style="color: var(--text-medium-gray); font-size: 0.9rem;">
      @if($user->is_admin)
        Administra tus credenciales de acceso, nombre de usuario y seguridad de cuenta.
      @else
        Personaliza tu experiencia, contactos de emergencia y credenciales de acceso.
      @endif
    </p>
  </div>

  @if($user->isProfessional())
    <div class="card" style="margin-bottom: 1.75rem; background: linear-gradient(135deg, #FFFFFF 0%, #F0F7FF 100%); border: 1.5px solid #0095F6; box-shadow: 0 4px 16px rgba(0, 149, 246, 0.08); border-radius: 16px; overflow: hidden;">
      <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 260px;">
          <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(0, 149, 246, 0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0, 149, 246, 0.15);">
            <x-verified-badge size="26" :popover="false" />
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-weight: 700; color: #1A2620; font-size: 1rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <span>Cuenta Profesional Verificada</span>
              <span style="background: rgba(0, 149, 246, 0.12); color: #0077CC; font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; font-family: var(--font-mono); letter-spacing: 0.05em;">OFICIAL</span>
            </div>
            <p style="font-size: 0.84rem; color: #556860; margin: 0.25rem 0 0 0; line-height: 1.5;">
              Cuentas con la insignia oficial de especialista. Tus artículos y aportes en la Revista Científica contarán con el sello de autenticidad y tu fotografía de autor.
            </p>
          </div>
        </div>
        <div style="flex-shrink: 0;">
          <a href="{{ route('revista.create') }}" class="btn btn-sm btn-primary" style="background: #0095F6; border-color: #0095F6; gap: 8px; font-size: 0.84rem; padding: 0.55rem 1.1rem; border-radius: 10px; font-weight: 600; white-space: nowrap; box-shadow: 0 2px 8px rgba(0, 149, 246, 0.25);">
            <i class="fa-solid fa-pen-nib"></i>
            <span>Publicar Artículo</span>
          </a>
        </div>
      </div>
  @endif

  <!-- PROFILE DETAILS FORM -->
  <div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-body" style="padding: 1.5rem;">
      <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-near-black);">
        <i class="fa-solid fa-user-gear" style="color: var(--sage-base);"></i>
        <span>Datos Personales</span>
      </h2>

      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Profile Photo Upload -->
        <div class="form-group" style="margin-bottom: 1.5rem;">
          <label class="form-label" style="display: flex; align-items: center; justify-content: space-between;">
            <span>Foto de Perfil</span>
            <span style="font-size: 0.76rem; color: #556860; font-weight: normal;">Visible en tu cuenta y artículos publicados</span>
          </label>

          <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; background: #F8FAF9; padding: 1.15rem; border-radius: 14px; border: 1.5px dashed #C2D6CA;">
            <!-- Current / Preview Avatar -->
            <div style="position: relative; width: 72px; height: 72px; flex-shrink: 0;">
              <div id="avatarPreviewBox" style="width: 72px; height: 72px; border-radius: 50%; overflow: hidden; background: #2E5D4B; display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 1.75rem; font-weight: 700; border: 2.5px solid #FFFFFF; box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
                @if($user->avatar_url)
                  <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                  <span id="avatarInitials" style="display: none;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @else
                  <span id="avatarInitials">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                  <img id="avatarPreviewImg" src="" alt="Vista previa" style="display: none; width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                @endif
              </div>
            </div>

            <!-- Upload Controls -->
            <div style="flex: 1; min-width: 220px;">
              <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;" onchange="handleAvatarPreview(this)">
              <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">

              <div style="display: flex; gap: 0.65rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                <label for="avatarInput" class="btn btn-secondary btn-sm" style="cursor: pointer; gap: 6px; font-size: 0.82rem; padding: 0.45rem 0.9rem;">
                  <i class="fa-solid fa-camera"></i>
                  <span>Subir nueva foto</span>
                </label>

                @if($user->avatar)
                  <button type="button" onclick="removeCurrentAvatar()" id="btnRemoveAvatar" class="btn btn-sm" style="background: rgba(192, 57, 43, 0.1); color: #C0392B; border: 1px solid rgba(192, 57, 43, 0.3); font-size: 0.8rem; padding: 0.45rem 0.85rem; gap: 5px;">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Quitar foto</span>
                  </button>
                @endif
              </div>

              <p style="font-size: 0.78rem; color: #556860; margin: 0; line-height: 1.4;">
                Formatos recomendados: JPG, PNG o WEBP. Tamaño máximo: 4MB. Esta imagen se mostrará como tu autoría oficial en la Revista Científica.
              </p>
              @error('avatar') <div class="form-error" style="margin-top: 0.4rem;">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="name" class="form-label">Nombre o apodo</label>
          <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control" required>
          @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" required>
          @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="bio" class="form-label">Frase o intención personal (bio)</label>
          <input type="text" name="bio" id="bio" value="{{ old('bio', $user->bio) }}" class="form-control" placeholder="Ej. Cuidando de mi salud mental día a día. Aprendiendo a escucharme.">
          @error('bio') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <!-- Emergency Crisis Contact Quick Fields (Only for Regular Users) -->
        @if(!$user->is_admin)
          <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1.5rem; border: 1px solid var(--border-light);">
            <h3 style="font-size: 1rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem; color: var(--clinical-red);">
              <i class="fa-solid fa-phone"></i>
              <span>Contacto Principal de Emergencia</span>
            </h3>
            <p style="font-size: 0.82rem; color: var(--text-medium-gray); margin-bottom: 0.85rem;">
              Esta persona podrá ser llamada rápidamente desde tu botón de auxilio personal.
            </p>

            <div class="responsive-two-col">
              <div class="form-group" style="margin-bottom: 0;">
                <label for="crisis_contact_name" class="form-label" style="font-size: 0.82rem;">Nombre y parentesco:</label>
                <input type="text" name="crisis_contact_name" id="crisis_contact_name" value="{{ old('crisis_contact_name', $user->crisis_contact_name) }}" class="form-control" placeholder="Ej. Sofía López (Hermana)">
              </div>

              <div class="form-group" style="margin-bottom: 0;">
                <label for="crisis_contact_phone" class="form-label" style="font-size: 0.82rem;">Teléfono:</label>
                <input type="text" name="crisis_contact_phone" id="crisis_contact_phone" value="{{ old('crisis_contact_phone', $user->crisis_contact_phone) }}" class="form-control" placeholder="Ej. 55 1234 5678">
              </div>
            </div>
          </div>
        @endif

        <div style="margin-top: 1.5rem; text-align: right;">
          <button type="submit" class="btn btn-primary" style="gap: 6px;">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Guardar Cambios de Perfil</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- PASSWORD UPDATE FORM -->
  <div class="card">
    <div class="card-body" style="padding: 1.5rem;">
      <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-near-black);">
        <i class="fa-solid fa-key" style="color: var(--sage-base);"></i>
        <span>Cambiar Contraseña</span>
      </h2>

      <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="current_password" class="form-label">Contraseña actual</label>
          <input type="password" name="current_password" id="current_password" class="form-control" required>
          @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="new_password" class="form-label">Nueva contraseña</label>
          <input type="password" name="password" id="new_password" class="form-control" required>
          @error('password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
          <button type="submit" class="btn btn-secondary" style="gap: 6px;">
            <i class="fa-solid fa-lock"></i>
            <span>Actualizar Contraseña</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- PROFESSIONAL HEALTHCARE ACCREDITATION (AT BOTTOM - ONLY FOR NON-ADMINS) -->
  @if(!$user->is_admin && !$user->isProfessional())
    @if(isset($latestVerification) && $latestVerification->status === 'pendiente')
      <!-- SOLICITUD PENDIENTE EN REVISIÓN -->
      <div class="card" style="margin-top: 1.75rem; background: linear-gradient(135deg, #FFFCF5 0%, #FFF8EB 100%); border: 1.5px solid #F39C12; border-radius: 16px; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 1.35rem 1.5rem;">
          <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(243, 156, 18, 0.15); color: #B7791F; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
              <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: #8A5300;">Solicitud de Verificación Profesional en Revisión</h3>
                <span class="mono-tag" style="background: #FDE68A; color: #92400E; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">EN PROCESO</span>
              </div>
              <p style="font-size: 0.84rem; color: #78350F; margin: 0.4rem 0 0.85rem 0; line-height: 1.5;">
                Hemos recibido tus datos y comprobantes. Nuestro equipo clínico está validando tu cédula en el Registro Nacional de Profesionistas para asignarte la insignia oficial.
              </p>
              
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; background: rgba(255, 255, 255, 0.8); padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid rgba(243, 156, 18, 0.2); font-size: 0.82rem;">
                <div>
                  <span style="color: #92400E; font-weight: 600; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase;">Nombre:</span>
                  <div style="font-weight: 600; color: #1A2620;">{{ $latestVerification->full_name }}</div>
                </div>
                <div>
                  <span style="color: #92400E; font-weight: 600; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase;">Número de Cédula:</span>
                  <div style="font-weight: 700; font-family: var(--font-mono); color: #1A2620;">{{ $latestVerification->license_number }}</div>
                </div>
                <div>
                  <span style="color: #92400E; font-weight: 600; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase;">Grado Escolar:</span>
                  <div style="font-weight: 600; color: #1A2620;">{{ $latestVerification->education_level_label }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @else
      <!-- SOLICITUD DE ACREDITACIÓN PROFESIONAL -->
      <div class="card verification-card-section" style="margin-top: 1.75rem; border: 1.5px solid #DCE8E0; border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="background: linear-gradient(135deg, #F8FAF9 0%, #EFF6F2 100%); padding: 1.25rem 1.5rem; border-bottom: 1px solid #DCE8E0; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 0.85rem;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(46, 93, 75, 0.12); color: #2E5D4B; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
              <h2 style="font-size: 1.05rem; font-weight: 700; color: #1A2620; margin: 0;">¿Eres Profesional de la Salud Mental?</h2>
              <p style="font-size: 0.82rem; color: #556860; margin: 0.15rem 0 0 0;">
                Acredita tu cédula oficial para obtener la insignia verificada y publicar investigaciones en la Revista Científica.
              </p>
            </div>
          </div>
        </div>

        <div class="card-body" style="padding: 1.5rem;">
          @if(isset($latestVerification) && $latestVerification->status === 'rechazada')
            <div style="background: #FDF2F2; border: 1px solid #F87171; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.84rem; color: #991B1B;">
              <div style="font-weight: 700; display: flex; align-items: center; gap: 6px; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Tu solicitud previa requiere corrección</span>
              </div>
              <p style="margin: 0; line-height: 1.4;">
                Motivo: {{ $latestVerification->admin_notes ?: 'Los datos de la cédula no coincidieron con el Registro Oficial.' }}
                Por favor, verifica tus datos e intenta nuevamente.
              </p>
            </div>
          @endif

          <form method="POST" action="{{ route('profile.verification.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="responsive-two-col" style="gap: 1.25rem; margin-bottom: 1.25rem;">
              <!-- 1. Nombre completo -->
              <div class="form-group" style="margin-bottom: 0;">
                <label for="full_name" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #1A2620;">
                  Nombre completo <span style="color: #E74C3C;">*</span>
                </label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $latestVerification->full_name ?? $user->name) }}" class="form-control" placeholder="Nombre completo como aparece en tu cédula" required>
                <span style="font-size: 0.74rem; color: #556860;">Tal como figura en tu título o cédula profesional.</span>
                @error('full_name') <div class="form-error">{{ $message }}</div> @enderror
              </div>

              <!-- 2. Número de cédula -->
              <div class="form-group" style="margin-bottom: 0;">
                <label for="license_number" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #1A2620;">
                  Número de Cédula Profesional <span style="color: #E74C3C;">*</span>
                </label>
                <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $latestVerification->license_number ?? '') }}" class="form-control" placeholder="Ej. 12345678" style="font-family: var(--font-mono); font-weight: 600;" required>
                <span style="font-size: 0.74rem; color: #556860;">Cédula federal o registro oficial de ejercicio clínico.</span>
                @error('license_number') <div class="form-error">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="responsive-two-col" style="gap: 1.25rem; margin-bottom: 1.25rem;">
              <!-- 3. Grado Escolar -->
              <div class="form-group" style="margin-bottom: 0;">
                <label for="education_level" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #1A2620;">
                  Grado Escolar <span style="color: #E74C3C;">*</span>
                </label>
                <select name="education_level" id="education_level" class="form-control custom-styled-select" required style="cursor: pointer; height: 46px; border-radius: 10px; border: 1.5px solid #DCE8E0; background-color: #FFFFFF; font-size: 0.88rem; color: #1A2620; padding: 0.45rem 0.85rem;">
                  <option value="" disabled {{ !old('education_level') ? 'selected' : '' }}>Selecciona tu grado escolar</option>
                  <option value="licenciatura" {{ old('education_level', $latestVerification->education_level ?? '') === 'licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                  <option value="especialidad" {{ old('education_level', $latestVerification->education_level ?? '') === 'especialidad' ? 'selected' : '' }}>Especialidad</option>
                  <option value="maestria" {{ old('education_level', $latestVerification->education_level ?? '') === 'maestria' ? 'selected' : '' }}>Maestria</option>
                  <option value="doctorado" {{ old('education_level', $latestVerification->education_level ?? '') === 'doctorado' ? 'selected' : '' }}>Doctorado</option>
                </select>
                <span style="font-size: 0.74rem; color: #556860;">Nivel máximo de formación académica alcanzado.</span>
                @error('education_level') <div class="form-error">{{ $message }}</div> @enderror
              </div>

              <!-- 4. Comprobante Oficial con CSS estilizado -->
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #1A2620; margin-bottom: 0.4rem; display: block;">
                  Comprobante Oficial <span style="font-size: 0.74rem; color: #556860; font-weight: normal;">(Opcional)</span>
                </label>
                
                <div class="custom-doc-uploader" id="docUploadBox">
                  <input type="file" name="document" id="docInputFile" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display: none;" onchange="handleDocSelected(this)">
                  
                  <label for="docInputFile" class="doc-upload-clickable-area">
                    <div class="doc-upload-btn-chip">
                      <i class="fa-solid fa-cloud-arrow-up"></i>
                      <span>Elegir archivo</span>
                    </div>
                    <div class="doc-upload-status-text" id="docStatusText">
                      No se eligió ningún archivo
                    </div>
                  </label>

                  <button type="button" class="doc-clear-btn" id="docClearBtn" style="display: none;" onclick="clearSelectedDoc()" title="Quitar archivo">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>

                <span style="font-size: 0.74rem; color: #556860; margin-top: 0.35rem; display: block;">PDF o imagen de tu cédula o título (máx. 5 MB).</span>
                @error('document') <div class="form-error">{{ $message }}</div> @enderror
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1.5rem; padding-top: 1.15rem; border-top: 1px solid #DCE8E0;">
              <button type="submit" class="btn btn-primary" style="gap: 8px; font-weight: 600; padding: 0.65rem 1.6rem; border-radius: 10px; background: #2E5D4B; border-color: #2E5D4B; box-shadow: 0 2px 8px rgba(46, 93, 75, 0.2);">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Enviar Solicitud</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endif

</div>
@endsection

@push('styles')
<style>
  /* ════ ESTILOS PARA CARGADOR DE ARCHIVO Y FORMULARIO DE VERIFICACIÓN ════ */
  .custom-doc-uploader {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FFFFFF;
    border: 1.5px solid #DCE8E0;
    border-radius: 10px;
    padding: 0.35rem 0.65rem;
    min-height: 46px;
    transition: all 0.2s ease;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
  }
  .custom-doc-uploader:hover {
    border-color: #2E5D4B;
    background: #FAFCFA;
  }
  .custom-doc-uploader.is-active {
    border-color: #2E5D4B;
    background: #F3F9F5;
    box-shadow: 0 0 0 3px rgba(46, 93, 75, 0.12);
  }
  .doc-upload-clickable-area {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    flex: 1;
    min-width: 0;
    margin: 0;
  }
  .doc-upload-btn-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F0F5F2;
    color: #2E5D4B;
    border: 1px solid #C2D6CA;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }
  .custom-doc-uploader:hover .doc-upload-btn-chip {
    background: #2E5D4B;
    color: #FFFFFF;
    border-color: #2E5D4B;
  }
  .doc-upload-status-text {
    font-size: 0.82rem;
    color: #556860;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-style: italic;
  }
  .custom-doc-uploader.is-active .doc-upload-status-text {
    color: #1A2620;
    font-weight: 600;
    font-style: normal;
  }
  .doc-clear-btn {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: none;
    background: rgba(192, 57, 43, 0.12);
    color: #C0392B;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 0.5rem;
  }
  .doc-clear-btn:hover {
    background: #C0392B;
    color: #FFFFFF;
  }
  .custom-styled-select:focus {
    border-color: #2E5D4B;
    outline: none;
    box-shadow: 0 0 0 3px rgba(46, 93, 75, 0.12);
  }
</style>
@endpush

@push('scripts')
<script>
  function handleAvatarPreview(input) {
    if (input.files && input.files[0]) {
      const file = input.files[0];
      const reader = new FileReader();
      reader.onload = function(e) {
        const previewImg = document.getElementById('avatarPreviewImg');
        const initials = document.getElementById('avatarInitials');
        const removeFlag = document.getElementById('removeAvatarFlag');
        
        if (removeFlag) removeFlag.value = '0';
        if (previewImg) {
          previewImg.src = e.target.result;
          previewImg.style.display = 'block';
        }
        if (initials) {
          initials.style.display = 'none';
        }
      };
      reader.readAsDataURL(file);
    }
  }

  async function removeCurrentAvatar() {
    let confirmed = true;
    if (typeof window.showZenConfirm === 'function') {
      confirmed = await window.showZenConfirm({
        title: '¿Quitar foto de perfil?',
        message: 'Tu perfil volverá a mostrar tu avatar con iniciales. Recuerda guardar los cambios de perfil para confirmar.',
        confirmText: 'Quitar foto',
        cancelText: 'Conservar',
        type: 'warning',
        icon: 'fa-user-slash'
      });
    }
    if (!confirmed) return;

    const input = document.getElementById('avatarInput');
    const previewImg = document.getElementById('avatarPreviewImg');
    const initials = document.getElementById('avatarInitials');
    const removeFlag = document.getElementById('removeAvatarFlag');
    const btnRemove = document.getElementById('btnRemoveAvatar');

    if (input) input.value = '';
    if (removeFlag) removeFlag.value = '1';
    if (previewImg) previewImg.style.display = 'none';
    if (initials) initials.style.display = 'block';
    if (btnRemove) btnRemove.style.display = 'none';
  }

  function handleDocSelected(input) {
    const statusText = document.getElementById('docStatusText');
    const uploaderBox = document.getElementById('docUploadBox');
    const clearBtn = document.getElementById('docClearBtn');

    if (input.files && input.files[0]) {
      const file = input.files[0];
      const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
      if (statusText) {
        statusText.textContent = `${file.name} (${sizeMb} MB)`;
      }
      if (uploaderBox) {
        uploaderBox.classList.add('is-active');
      }
      if (clearBtn) {
        clearBtn.style.display = 'flex';
      }
    } else {
      clearSelectedDoc();
    }
  }

  function clearSelectedDoc() {
    const input = document.getElementById('docInputFile');
    const statusText = document.getElementById('docStatusText');
    const uploaderBox = document.getElementById('docUploadBox');
    const clearBtn = document.getElementById('docClearBtn');

    if (input) input.value = '';
    if (statusText) {
      statusText.textContent = 'No se eligió ningún archivo';
    }
    if (uploaderBox) {
      uploaderBox.classList.remove('is-active');
    }
    if (clearBtn) {
      clearBtn.style.display = 'none';
    }
  }
</script>
@endpush
