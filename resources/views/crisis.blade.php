@extends('layouts.guest')

@section('title', 'Líneas de Crisis y Ayuda 24 Horas — A tu lado')

@section('content')
{{-- Sólo se animan los textos: el botón de llamada y las líneas se ven desde el primer instante. --}}
<x-marca.hero eyebrow="Apoyo inmediato 24/7" icono="fa-solid fa-phone-volume" :crisis="true">
  <h1 class="m-titulo" data-aparecer="palabras">Si necesitas hablar con alguien <em>ahora</em></h1>
  <p class="m-bajada" data-aparecer>Gratuitas. Confidenciales. Disponibles las 24 horas del día, los 365 días del año. No tienes que estar "suficientemente mal" para pedir ayuda.</p>
  <div class="m-acciones" style="margin-top: 2rem;">
    <a href="tel:8009112000" class="m-btn m-btn-crisis" style="min-height: 58px; font-size: 1.05rem; padding: 0 2rem;">
      <i class="fa-solid fa-phone"></i>
      <span>México: 800 911 2000 (Línea de la Vida)</span>
    </a>
  </div>
</x-marca.hero>
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
        <div class="card" data-foco style="--tono: #A93226; border: 1px solid rgba(169, 50, 38, 0.2); background: linear-gradient(160deg, #FFFFFF 40%, #FFF3F1);">
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
