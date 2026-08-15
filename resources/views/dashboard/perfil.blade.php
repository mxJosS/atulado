@extends('layouts.app')

@section('title', 'Mi Perfil y Preferencias')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">

  <div style="margin-bottom: 2rem;">
    <span class="mono-tag" style="color: var(--sage-600);">Configuración de Cuenta</span>
    <h1 style="font-size: 2rem; margin-top: 0.2rem;">Mi Perfil</h1>
    <p style="color: var(--ink-600); font-size: 0.95rem;">Personaliza tu experiencia, contactos de emergencia y credenciales de acceso.</p>
  </div>

  <!-- PROFILE DETAILS FORM -->
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
      <h2 style="font-size: 1.3rem; margin-bottom: 1.25rem;">Datos Personales</h2>

      <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

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
          <input type="text" name="bio" id="bio" value="{{ old('bio', $user->bio) }}" class="form-control" placeholder="Ej. Un día a la vez 🌱">
          @error('bio') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <!-- Avatar Color Choice -->
        <div class="form-group" style="margin-top: 1.25rem;">
          <label class="form-label">Color de tu árbol avatar:</label>
          <div style="display: flex; gap: 1rem; margin-top: 0.35rem;">
            @php
              $colors = [
                'sage' => ['label' => 'Menta', 'bg' => '#4d7c5f'],
                'terra' => ['label' => 'Terra', 'bg' => '#b86b4a'],
                'lav' => ['label' => 'Lavanda', 'bg' => '#7a6faa'],
                'sky' => ['label' => 'Cielo', 'bg' => '#4a7fa5'],
                'amber' => ['label' => 'Ámbar', 'bg' => '#c4901a'],
              ];
              $currentColor = old('avatar_color', $user->avatar_color ?? 'sage');
            @endphp
            @foreach($colors as $val => $c)
              <label style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 0.3rem;">
                <input type="radio" name="avatar_color" value="{{ $val }}" {{ $currentColor === $val ? 'checked' : '' }} style="display: none;" class="avatar-radio">
                <span class="avatar-swatch" style="width: 36px; height: 36px; border-radius: 50%; background: {{ $c['bg'] }}; display: flex; align-items: center; justify-content: center; border: 2px solid {{ $currentColor === $val ? 'var(--ink-900)' : 'transparent' }};">
                  <span class="check-mark" style="color: #ffffff; font-size: 0.85rem; display: {{ $currentColor === $val ? 'block' : 'none' }};">✓</span>
                </span>
                <span style="font-size: 0.72rem; color: var(--ink-600); font-family: var(--font-mono);">{{ $c['label'] }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <!-- Emergency Crisis Contact Quick Fields -->
        <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1.5rem;">
          <h3 style="font-size: 1.05rem; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🚨</span>
            <span>Contacto Principal de Emergencia</span>
          </h3>
          <p style="font-size: 0.82rem; color: var(--ink-600); margin-bottom: 1rem;">
            Esta persona podrá ser llamada rápidamente desde tu botón de auxilio personal.
          </p>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
              <label for="crisis_contact_name" class="form-label">Nombre y parentesco:</label>
              <input type="text" name="crisis_contact_name" id="crisis_contact_name" value="{{ old('crisis_contact_name', $user->crisis_contact_name) }}" class="form-control" placeholder="Ej. Sofía López (Hermana)">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="crisis_contact_phone" class="form-label">Teléfono:</label>
              <input type="text" name="crisis_contact_phone" id="crisis_contact_phone" value="{{ old('crisis_contact_phone', $user->crisis_contact_phone) }}" class="form-control" placeholder="Ej. 55 1234 5678">
            </div>
          </div>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
          <button type="submit" class="btn btn-primary">
            Guardar Cambios de Perfil
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- PASSWORD CHANGER FORM -->
  <div class="card">
    <div class="card-body">
      <h2 style="font-size: 1.3rem; margin-bottom: 1.25rem;">Seguridad y Contraseña</h2>

      <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="current_password" class="form-label">Contraseña actual</label>
          <input type="password" name="current_password" id="current_password" class="form-control" required>
          @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="new_password" class="form-label">Nueva contraseña (mínimo 6 caracteres)</label>
          <input type="password" name="password" id="new_password" class="form-control" required>
          @error('password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="new_password_confirmation" class="form-label">Confirma la nueva contraseña</label>
          <input type="password" name="password_confirmation" id="new_password_confirmation" class="form-control" required>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
          <button type="submit" class="btn btn-secondary">
            Actualizar Contraseña
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.avatar-radio').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.avatar-swatch').forEach(sw => {
        sw.style.borderColor = 'transparent';
        sw.querySelector('.check-mark').style.display = 'none';
      });
      const selectedSwatch = radio.closest('label').querySelector('.avatar-swatch');
      selectedSwatch.style.borderColor = 'var(--ink-900)';
      selectedSwatch.querySelector('.check-mark').style.display = 'block';
    });
  });
</script>
@endpush
