@extends('layouts.guest')

@section('title', 'Líneas de Crisis y Ayuda 24 Horas — A tu lado')

@section('content')
<div style="background: var(--dark-950); color: #ffffff; padding: 4.5rem 1.5rem; border-bottom: 3px solid var(--crisis-red); position: relative; overflow: hidden;">
  <div class="hero-halo" style="top: 40%; left: 50%; background: radial-gradient(circle, rgba(192,57,43,0.3) 0%, transparent 70%);"></div>

  <div style="max-width: 900px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
    <div class="badge badge-crisis" style="margin-bottom: 1rem;">
      <span class="nav-crisis-dot"></span> Apoyo Inmediato 24/7
    </div>
    <h1 style="color: #ffffff; font-size: clamp(2.2rem, 5vw, 3.4rem); margin-bottom: 1rem;">
      Si necesitas hablar con alguien <em>ahora</em>
    </h1>
    <p style="color: rgba(255, 255, 255, 0.7); max-width: 580px; margin: 0 auto; font-size: 1.05rem; line-height: 1.75; font-weight: 300;">
      Gratuitas. Confidenciales. Disponibles las 24 horas del día, los 365 días del año. No tienes que estar "suficientemente mal" para pedir ayuda.
    </p>

    <div style="margin-top: 2rem;">
      <a href="tel:8002900024" class="btn btn-crisis btn-lg" style="font-size: 1.15rem; padding: 1.1rem 2.5rem; border-radius: var(--radius-full);">
        <span class="nav-crisis-dot"></span>
        <span>México: 800 290 0024 (Línea de la Vida)</span>
      </a>
    </div>
  </div>
</div>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 4rem;">

  <!-- COUNTRY FILTER -->
  <div style="display: flex; gap: 0.65rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    <a href="{{ route('crisis') }}" class="btn btn-sm {{ !request('country') || request('country') == 'todos' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: var(--radius-full);">
      Todos los países
    </a>
    @foreach($countries as $c)
      <a href="{{ route('crisis', ['country' => $c->country_code]) }}" class="btn btn-sm {{ request('country') === $c->country_code ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: var(--radius-full);">
        {{ $c->country }}
      </a>
    @endforeach
  </div>

  <!-- CRISIS DIRECTORY CARDS GRID -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
    @foreach($crisisLines as $line)
      <div class="card" style="border: 1.5px solid rgba(192, 57, 43, 0.25); background: linear-gradient(145deg, #ffffff, var(--crisis-bg));">
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <span class="mono-tag" style="color: var(--crisis-dark);">{{ $line->country }}</span>
            <span class="badge" style="background: rgba(192,57,43,0.1); color: var(--crisis-dark); font-size: 0.68rem;">{{ $line->hours }}</span>
          </div>

          <h2 style="font-size: 1.25rem; margin-bottom: 0.35rem; color: var(--ink-900);">
            {{ $line->service_name }}
          </h2>

          <div style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 900; color: var(--crisis-red); margin-bottom: 0.75rem;">
            {{ $line->phone_number }}
          </div>

          <p style="font-size: 0.86rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
            {{ $line->description }}
          </p>

          <a href="tel:{{ preg_replace('/[^0-9+]/', '', $line->phone_number) }}" class="btn btn-crisis btn-sm" style="width: 100%; justify-content: center;">
            📞 Llamar ahora gratis
          </a>
        </div>
      </div>
    @endforeach
  </div>

  <!-- HELPFUL TIPS WHEN CALLING -->
  <div class="card" style="background: var(--sage-50); border: 1px solid var(--sage-200);">
    <div class="card-body" style="padding: 2rem;">
      <span class="mono-tag" style="color: var(--sage-700);">Información importante</span>
      <h3 style="font-size: 1.35rem; margin-top: 0.25rem; margin-bottom: 0.75rem;">¿Qué pasa cuando llamas a una línea de crisis?</h3>
      <ul style="color: var(--ink-700); font-size: 0.92rem; line-height: 1.8; margin-left: 1.25rem;">
        <li>Te atenderá un profesional de la salud mental o un orientador certificado en intervención en crisis.</li>
        <li><strong>No te juzgarán</strong> ni te dirán qué sentir. Su objetivo es escucharte con respeto y ayudarte a recuperar la calma.</li>
        <li>La llamada es <strong>100% anónima y confidencial</strong>; no necesitas dar tu nombre completo ni datos personales.</li>
        <li>Si estás acompañando a un amigo o familiar en crisis, también puedes llamar para recibir orientación sobre cómo proceder.</li>
      </ul>
    </div>
  </div>

</div>
@endsection
