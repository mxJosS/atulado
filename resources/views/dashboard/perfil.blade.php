@extends('layouts.app')

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
      @if($user->isProfessional())
        <x-verified-badge size="22" />
      @endif
    </h1>
    <p style="color: var(--text-medium-gray); font-size: 0.9rem;">Personaliza tu experiencia, contactos de emergencia y credenciales de acceso.</p>
  </div>

  @if($user->isProfessional())
    <div class="card" style="margin-bottom: 1.75rem; background: linear-gradient(135deg, #FFFFFF 0%, #F0F7FF 100%); border: 1.5px solid #0095F6; box-shadow: 0 4px 16px rgba(0, 149, 246, 0.08);">
      <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(0, 149, 246, 0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <x-verified-badge size="26" />
          </div>
          <div>
            <div style="font-weight: 700; color: #1A2620; font-size: 0.98rem; display: flex; align-items: center; gap: 6px;">
              <span>Cuenta Profesional Verificada</span>
            </div>
            <p style="font-size: 0.82rem; color: #556860; margin: 0; margin-top: 0.15rem;">
              Cuentas con la insignia oficial de especialista. Tus artículos y aportes en la Revista Científica contarán con el sello de autenticidad y tu fotografía de autor.
            </p>
          </div>
        </div>
        <a href="{{ route('revista.create') }}" class="btn btn-sm btn-primary" style="background: #0095F6; border-color: #0095F6; gap: 6px; font-size: 0.82rem; white-space: nowrap;">
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
                  <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                  <span id="avatarInitials" style="display: none;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @else
                  <span id="avatarInitials">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                  <img id="avatarPreviewImg" src="" alt="Vista previa" style="display: none; width: 100%; height: 100%; object-fit: cover;">
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

        <!-- Emergency Crisis Contact Quick Fields -->
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

</div>
@endsection

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
</script>
@endpush
