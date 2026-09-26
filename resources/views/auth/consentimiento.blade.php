@extends('layouts.guest')

@section('title', 'Tu consentimiento — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>

  <div style="width: 100%; max-width: 520px; position: relative; z-index: 2;">
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.25rem 2rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">

      <div style="text-align: center; margin-bottom: 1.4rem;">
        <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #2E5D4B;">lado</em></span>
        <h1 style="font-family: var(--font-display); font-size: 1.55rem; font-weight: 700; color: #1A2620; line-height: 1.2; margin: 0.6rem 0 0.45rem;">
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

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; border-radius: 12px; background: #2E5D4B !important; color: #FFFFFF !important;">
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
@endsection
