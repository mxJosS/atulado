@extends('layouts.guest')

@section('title', 'Biblioteca de Recursos y Herramientas — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section style="padding: 4rem 1.5rem; background: #080C0A !important; color: #FFFFFF !important; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 760px; margin: 0 auto;">
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); padding: 0.35rem 0.95rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: #A8E6C0; margin-bottom: 1.5rem;">
      <i class="fa-solid fa-book-bookmark"></i>
      <span>Biblioteca de Bienestar y DBT</span>
    </div>
    <h1 style="color: #FFFFFF !important; font-size: clamp(2rem, 4.5vw, 3.2rem); margin-bottom: 0.85rem; font-weight: 700;">
      Herramientas para <em class="editorial-italic" style="color: #A8E6C0 !important;">hoy</em>
    </h1>
    <p style="color: #C8DDD1 !important; font-size: 0.95rem; margin-bottom: 2rem;">
      Tips rápidos, ejercicios mentales, técnicas terapéuticas de regulación y protocolos de seguridad.
    </p>

    <!-- SEARCH BAR -->
    <form method="GET" action="{{ route('recursos.index') }}" style="max-width: 520px; margin: 0 auto; display: flex; gap: 0.5rem;">
      <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        class="form-control" 
        placeholder="Buscar técnica, emoción o ejercicio..."
        style="background: #FFFFFF; border-radius: 9999px; padding: 0.8rem 1.4rem; color: #1A2620;"
      >
      <button type="submit" class="btn btn-primary" style="border-radius: 9999px; padding: 0.8rem 1.4rem; gap: 6px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Buscar</span>
      </button>
    </form>
  </div>
</section>

<div class="container" style="padding-top: 3rem; padding-bottom: 4.5rem;">

  <!-- CATEGORY FILTER PILLS -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    @php
      $categories = [
        'todos' => 'Todos los recursos',
        'tip' => 'Tips rápidos',
        'ejercicio' => 'Ejercicios',
        'reflexion' => 'Reflexiones',
        'herramienta' => 'Herramientas DBT',
      ];
      $activeCat = request('category', 'todos');
    @endphp

    @foreach($categories as $key => $label)
      <a href="{{ route('recursos.index', ['category' => $key, 'search' => request('search')]) }}" class="btn btn-sm {{ $activeCat === $key ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
        {{ $label }}
      </a>
    @endforeach
  </div>

  <!-- RESOURCES GRID -->
  @if($resources->isEmpty())
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
      <i class="fa-solid fa-magnifying-glass" style="font-size: 2.5rem; color: #8EADA4;"></i>
      <h3 style="margin-top: 1rem; margin-bottom: 0.35rem; font-size: 1.25rem;">No se encontraron recursos</h3>
      <p style="color: #556860; max-width: 450px; margin: 0 auto 1.5rem; font-size: 0.9rem;">
        Intenta buscar con otros términos o seleccionar otra categoría.
      </p>
      <a href="{{ route('recursos.index') }}" class="btn btn-secondary btn-sm">Ver todos los recursos</a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 3.5rem;">
      @foreach($resources as $res)
        <div class="card">
          <div class="card-header-banner" style="background: #EEF4F0; height: 110px; display: flex; align-items: center; justify-content: center; position: relative;">
            <span class="badge badge-sage" style="position: absolute; top: 12px; left: 14px;">
              {{ $res->category_label }}
            </span>
            <span style="font-size: 2rem; color: #2E5D4B;">
              @if($res->color_theme === 'sage') <i class="fa-solid fa-seedling"></i>
              @elseif($res->color_theme === 'sky') <i class="fa-solid fa-water"></i>
              @elseif($res->color_theme === 'lav') <i class="fa-solid fa-spa"></i>
              @elseif($res->color_theme === 'terra') <i class="fa-solid fa-leaf"></i>
              @elseif($res->color_theme === 'amber') <i class="fa-solid fa-sun"></i>
              @else <i class="fa-solid fa-circle-exclamation"></i>
              @endif
            </span>
          </div>
          <div class="card-body" style="padding: 1.25rem;">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.4rem; line-height: 1.3;">
              <a href="{{ route('recursos.show', $res->slug) }}" style="color: #1A2620;">
                {{ $res->title }}
              </a>
            </h3>
            <p style="font-size: 0.85rem; color: #556860; line-height: 1.5; margin-bottom: 1.25rem;">
              {{ Str::limit($res->summary, 85) }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid #DCE8E0;">
              <a href="{{ route('recursos.show', $res->slug) }}" class="btn btn-sm btn-primary" style="gap: 6px;">
                <span>Practicar</span>
                <i class="fa-solid fa-arrow-right"></i>
              </a>

              @auth
                <button type="button" class="btn btn-sm btn-secondary favorite-toggle-btn {{ auth()->user()->favoriteResources->contains($res->id) ? 'is-fav' : '' }}" data-id="{{ $res->id }}" style="padding: 0.4rem 0.65rem;" title="Guardar en favoritos">
                  <i class="fa-solid fa-star" style="color: {{ auth()->user()->favoriteResources->contains($res->id) ? '#C8B87A' : '#8EADA4' }};"></i>
                </button>
              @endauth
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div style="display: flex; justify-content: center;">
      {{ $resources->links() }}
    </div>
  @endif

</div>
@endsection
