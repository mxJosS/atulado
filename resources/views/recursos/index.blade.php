@extends('layouts.guest')

@section('title', 'Biblioteca de Recursos y Herramientas — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section class="hero-wrapper" style="padding: 4rem 1.5rem 5rem; background: var(--dark-900);">
  <div class="hero-halo" style="top: 35%; left: 50%;"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">
      <span>📚</span> Biblioteca de Bienestar y DBT
    </div>
    <h1 class="hero-title">
      Herramientas para <em>hoy</em>
    </h1>
    <p class="hero-subtitle">
      Tips rápidos, ejercicios mentales, técnicas terapéuticas de regulación y protocolos de seguridad. Todo en un solo lugar accesible.
    </p>

    <!-- SEARCH BAR -->
    <form method="GET" action="{{ route('recursos.index') }}" style="max-width: 520px; margin: 0 auto; display: flex; gap: 0.5rem;">
      <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        class="form-control" 
        placeholder="Buscar técnica, emoción o ejercicio..."
        style="background: rgba(255,255,255,0.9); border-radius: var(--radius-full); padding: 0.85rem 1.4rem;"
      >
      <button type="submit" class="btn btn-primary" style="border-radius: var(--radius-full); padding: 0.85rem 1.5rem;">
        Buscar
      </button>
    </form>
  </div>
</section>

<div class="container" style="padding-top: 3rem; padding-bottom: 4.5rem;">

  <!-- CATEGORY FILTER PILLS -->
  <div style="display: flex; gap: 0.65rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
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
      <a href="{{ route('recursos.index', ['category' => $key, 'search' => request('search')]) }}" class="btn btn-sm {{ $activeCat === $key ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: var(--radius-full);">
        {{ $label }}
      </a>
    @endforeach
  </div>

  <!-- RESOURCES GRID -->
  @if($resources->isEmpty())
    <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
      <span style="font-size: 3rem;">🔍</span>
      <h3 style="margin-top: 1rem; margin-bottom: 0.5rem;">No se encontraron recursos</h3>
      <p style="color: var(--ink-600); max-width: 450px; margin: 0 auto 1.5rem;">
        Intenta buscar con otros términos o seleccionar otra categoría.
      </p>
      <a href="{{ route('recursos.index') }}" class="btn btn-secondary btn-sm">Ver todos los recursos</a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
      @foreach($resources as $res)
        <div class="card">
          <div class="card-header-banner theme-{{ $res->color_theme }}">
            <span class="mono-tag" style="position: absolute; top: 12px; left: 14px; background: rgba(255,255,255,0.7); padding: 0.2rem 0.55rem; border-radius: var(--radius-sm);">
              {{ $res->category_label }}
            </span>
            <span style="font-size: 2.5rem;">
              @if($res->color_theme === 'sage') 🌸
              @elseif($res->color_theme === 'sky') 🌊
              @elseif($res->color_theme === 'lav') 🪷
              @elseif($res->color_theme === 'terra') 🍂
              @elseif($res->color_theme === 'amber') ✨
              @else 🛑
              @endif
            </span>
            <span style="position: absolute; bottom: 10px; right: 14px; font-family: var(--font-mono); font-size: 0.72rem; color: var(--ink-600); background: rgba(255,255,255,0.85); padding: 0.2rem 0.5rem; border-radius: var(--radius-sm);">
              ⏱ {{ $res->estimated_time ?? '2 min' }}
            </span>
          </div>

          <div class="card-body">
            <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem; line-height: 1.3;">
              <a href="{{ route('recursos.show', $res->slug) }}" style="color: var(--ink-900);">
                {{ $res->title }}
              </a>
            </h3>

            <p style="font-size: 0.85rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
              {{ $res->summary }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--ink-100);">
              <a href="{{ route('recursos.show', $res->slug) }}" class="btn btn-sm btn-secondary">
                Ver ejercicio →
              </a>

              @auth
                @php
                  $isFav = in_array($res->id, $userFavorites);
                @endphp
                <button 
                  type="button" 
                  onclick="toggleResourceFavorite({{ $res->id }}, this)" 
                  class="btn btn-sm {{ $isFav ? 'btn-secondary is-favorited' : 'btn-secondary' }}"
                  style="border-radius: var(--radius-full); font-size: 0.76rem;"
                >
                  {{ $isFav ? '⭐ Guardado' : '☆ Guardar' }}
                </button>
              @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-secondary" style="border-radius: var(--radius-full); font-size: 0.76rem;">
                  ☆ Guardar
                </a>
              @endauth
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div style="margin-bottom: 3.5rem;">
      {{ $resources->links() }}
    </div>
  @endif

  <!-- CRISIS FAST ACCESS SECTION -->
  <div style="background: var(--dark-900); color: #ffffff; border-radius: var(--radius-xl); padding: 3rem 2rem; border-top: 4px solid var(--crisis-red);">
    <div style="max-width: 750px; margin: 0 auto; text-align: center;">
      <span class="mono-tag" style="color: #ff9999;">Líneas de Crisis 24 Horas</span>
      <h2 style="color: #ffffff; font-size: 1.8rem; margin-top: 0.4rem; margin-bottom: 0.75rem;">
        ¿Necesitas apoyo humano inmediato?
      </h2>
      <p style="color: rgba(255,255,255,0.65); font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.75rem;">
        Líneas de atención en crisis gratuitas, anónimas y activas los 365 días del año.
      </p>
      <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="tel:8002900024" class="btn btn-crisis btn-lg">
          Llamar a Línea de la Vida (800 290 0024)
        </a>
        <a href="{{ route('crisis') }}" class="btn btn-outline-white btn-lg">
          Ver Directorio Completo
        </a>
      </div>
    </div>
  </div>

</div>
@endsection
