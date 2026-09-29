@extends('layouts.guest')

@section('title', 'Tu consentimiento — A tu lado')

@section('content')
<div class="acceso">
  <x-marca.acceso-panel eyebrow="Antes de empezar">
    <h2>Tus datos son <em>tuyos</em></h2>
    <p>Necesitamos tu consentimiento expreso para cuidar tu información de bienestar.</p>
  </x-marca.acceso-panel>

  <div class="acceso-formulario">
  <div class="acceso-tarjeta" data-aparecer>
    <div class="m-tarjeta" style="padding: clamp(1.75rem, 3vw, 2.5rem); color: #14201A;">

      <div style="text-align: center; margin-bottom: 1.4rem;">
        <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #2E5D4B;">lado</em></span>
        <h1 class="acceso-tarjeta-titulo">
          Antes de empezar
        </h1>
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5; margin: 0;">
          Hola, <b>{{ auth()->user()->name }}</b>. A tu lado trabaja con información sobre cómo te sientes, que la ley considera
          <b>datos sensibles</b>. Para usarlo necesitamos tu consentimiento expreso.
        </p>
      </div>

      <form method="POST" action="{{ route('consentimiento.guardar') }}" novalidate>
        @csrf
        @include('auth.partials.consentimiento')

        <button type="submit" class="m-btn m-btn-verde m-btn-ancho">
          Aceptar y continuar
        </button>
      </form>

      <div style="margin-top: 1.25rem; border-top: 1px solid #EEF3F0; padding-top: 1rem; font-size: 0.82rem; color: #556860; text-align: center; line-height: 1.5;">
        Si no estás de acuerdo, puedes cerrar sesión, o eliminar tu cuenta y la información que tenga.
        <div style="display: flex; gap: 0.6rem; justify-content: center; margin-top: 0.7rem; flex-wrap: wrap;">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: none; border: 1.5px solid #C2D6CA; color: #1A2620; border-radius: 10px; padding: 0.45rem 0.9rem; font-size: 0.82rem; cursor: pointer;">Cerrar sesión</button>
          </form>
          <button type="button" onclick="document.getElementById('eliminar-desde-consentimiento').hidden = false; this.hidden = true;"
                  style="background: none; border: 1.5px solid #E9B7B0; color: #B02418; border-radius: 10px; padding: 0.45rem 0.9rem; font-size: 0.82rem; cursor: pointer;">Eliminar mi cuenta</button>
        </div>
        <div id="eliminar-desde-consentimiento" @unless($errors->eliminarCuenta->any()) hidden @endunless style="margin-top: 0.9rem; text-align: left;">
          @include('dashboard.partials.eliminar-cuenta')
        </div>
      </div>
    </div>
  </div>
</div>
</div>
@endsection
