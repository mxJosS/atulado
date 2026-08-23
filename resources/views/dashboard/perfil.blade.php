@extends('layouts.app')

@section('title', 'Mi Perfil y Preferencias')

@php
  $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

@section('content')
<div style="max-width: 800px; margin: 0 auto;">

  <div style="margin-bottom: 1.75rem;">
    <span class="mono-tag" style="color: var(--sage-base);">— CONFIGURACIÓN DE CUENTA</span>
    <h1 style="font-size: 1.85rem; margin-top: 0.15rem; color: var(--text-near-black);">Mi Perfil</h1>
    <p style="color: var(--text-medium-gray); font-size: 0.9rem;">Personaliza tu experiencia, contactos de emergencia y credenciales de acceso.</p>
  </div>

  <!-- PROFILE DETAILS FORM -->
  <div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-body" style="padding: 1.5rem;">
      <h2 style="font-size: 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-near-black);">
        <i class="fa-solid fa-user-gear" style="color: var(--sage-base);"></i>
        <span>Datos Personales</span>
      </h2>

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
          <input type="text" name="bio" id="bio" value="{{ old('bio', $user->bio) }}" class="form-control" placeholder="Ej. Un día a la vez, con paciencia y compasión...">
          @error('bio') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <!-- Avatar Color Choice -->
        <div class="form-group" style="margin-top: 1.25rem;">
          <label class="form-label">Tono de tu avatar:</label>
          <div style="display: flex; gap: 0.85rem; margin-top: 0.35rem;">
            @php
              $colors = [
                'sage' => ['label' => 'Sage', 'bg' => '#2E5D4B'],
                'mint' => ['label' => 'Menta', 'bg' => '#5AB56E'],
                'lav' => ['label' => 'Lavanda', 'bg' => '#5B4A8A'],
                'amber' => ['label' => 'Dorado', 'bg' => '#C8B87A'],
                'terra' => ['label' => 'Tierra', 'bg' => '#6B3A1F'],
              ];
              $currentColor = old('avatar_color', $user->avatar_color ?? 'sage');
            @endphp
            @foreach($colors as $val => $c)
              <label style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                <input type="radio" name="avatar_color" value="{{ $val }}" {{ $currentColor === $val ? 'checked' : '' }} style="display: none;" class="avatar-radio">
                <span class="avatar-swatch" style="width: 34px; height: 34px; border-radius: 50%; background: {{ $c['bg'] }}; display: flex; align-items: center; justify-content: center; border: 2px solid {{ $currentColor === $val ? 'var(--sage-base)' : 'transparent' }};">
                  <i class="fa-solid fa-check check-mark" style="color: #ffffff; font-size: 0.75rem; display: {{ $currentColor === $val ? 'block' : 'none' }};"></i>
                </span>
                <span style="font-size: 0.7rem; color: var(--text-medium-gray); font-family: var(--font-mono);">{{ $c['label'] }}</span>
              </label>
            @endforeach
          </div>
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
