@extends('layouts.guest')

@section('title', 'Líneas de Crisis y Ayuda 24 Horas — A tu lado')

@section('content')
<div style="background: #080C0A !important; color: #FFFFFF !important; padding: 4.5rem 1.5rem; border-bottom: 3px solid #A93226; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(169,50,38,0.28) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="max-width: 900px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
    <div class="badge badge-crisis" style="margin-bottom: 1rem; gap: 6px; padding: 0.45rem 1rem; background: rgba(169,50,38,0.2) !important; color: #FFA59C !important; border: 1px solid rgba(169,50,38,0.4);">
      <i class="fa-solid fa-phone-volume"></i>
      <span>Apoyo Inmediato 24/7</span>
    </div>
    <h1 style="color: #FFFFFF !important; font-size: clamp(2rem, 4.5vw, 3.2rem); margin-bottom: 1rem; font-weight: 700;">
      Si necesitas hablar con alguien <em class="editorial-italic" style="color: #A8E6C0 !important;">ahora</em>
    </h1>
    <p style="color: #C8DDD1 !important; max-width: 600px; margin: 0 auto; font-size: 1.05rem; line-height: 1.7; font-weight: 300;">
      Gratuitas. Confidenciales. Disponibles las 24 horas del día, los 365 días del año. No tienes que estar "suficientemente mal" para pedir ayuda.
    </p>

    <div style="margin-top: 2rem;">
      <a href="tel:8002900024" class="btn btn-crisis btn-lg" style="font-size: 1.05rem; padding: 1rem 2.25rem; border-radius: 9999px; gap: 8px;">
        <i class="fa-solid fa-phone"></i>
        <span>México: 800 290 0024 (Línea de la Vida)</span>
      </a>
    </div>
  </div>
</div>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 4rem;">

  <!-- COUNTRY FILTER -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    <a href="{{ route('crisis') }}" class="btn btn-sm {{ !request('country') || request('country') == 'todos' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
      Todos los países
    </a>
    @foreach($countries as $c)
      <a href="{{ route('crisis', ['country' => $c->country_code]) }}" class="btn btn-sm {{ request('country') === $c->country_code ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
        {{ $c->country }}
      </a>
    @endforeach
  </div>

  <!-- CRISIS DIRECTORY CARDS GRID -->
  @if($crisisLines->isEmpty())
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem; margin-bottom: 3.5rem;">
      <i class="fa-solid fa-phone-slash" style="font-size: 2.5rem; color: #C0392B;"></i>
      <h3 style="margin-top: 1rem; margin-bottom: 0.35rem; font-size: 1.25rem;">No se encontraron líneas para este filtro</h3>
      <p style="color: #556860; margin-bottom: 1.5rem; font-size: 0.9rem;">Puedes llamar a la Línea de la Vida internacional o consultar todos los países.</p>
      <a href="{{ route('crisis') }}" class="btn btn-secondary btn-sm">Ver todas las líneas</a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
      @foreach($crisisLines as $line)
        <div class="card" style="border: 1.5px solid rgba(169, 50, 38, 0.25); background: linear-gradient(145deg, #FFFFFF, #FFF5F4);">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
              <span class="mono-tag" style="color: #A93226;">{{ $line->country }}</span>
              <span class="badge" style="background: rgba(169,50,38,0.12); color: #A93226; font-size: 0.68rem;">{{ $line->hours }}</span>
            </div>

            <h2 style="font-size: 1.25rem; margin-bottom: 0.35rem; color: #1A2620;">
              {{ $line->service_name }}
            </h2>

            <div style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 800; color: #A93226; margin-bottom: 0.75rem;">
              {{ $line->phone_number }}
            </div>

            <p style="font-size: 0.86rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem;">
              {{ $line->description }}
            </p>

            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $line->phone_number) }}" class="btn btn-crisis btn-sm" style="width: 100%; justify-content: center; gap: 8px;">
              <i class="fa-solid fa-phone"></i>
              <span>Llamar ahora gratis</span>
            </a>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
