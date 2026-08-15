@extends('layouts.guest')

@section('title', $resource->title . ' — A tu lado')

@section('content')
<div class="container-narrow" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- BREADCRUMB -->
  <div style="margin-bottom: 1.5rem; font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink-400);">
    <a href="{{ route('recursos.index') }}" style="color: var(--sage-600); text-decoration: underline;">← Recursos</a>
    &nbsp;/&nbsp;
    <span>{{ $resource->category_label }}</span>
  </div>

  <!-- ARTICLE CARD -->
  <div class="card" style="margin-bottom: 3rem; overflow: hidden;">
    <div class="card-header-banner theme-{{ $resource->color_theme }}" style="height: 180px;">
      <span class="mono-tag" style="position: absolute; top: 18px; left: 20px; background: rgba(255,255,255,0.85); padding: 0.3rem 0.75rem; border-radius: var(--radius-sm);">
        {{ $resource->category_label }}
      </span>
      <span style="font-size: 4rem;">
        @if($resource->color_theme === 'sage') 🌸
        @elseif($resource->color_theme === 'sky') 🌊
        @elseif($resource->color_theme === 'lav') 🪷
        @elseif($resource->color_theme === 'terra') 🍂
        @elseif($resource->color_theme === 'amber') ✨
        @else 🛑
        @endif
      </span>
      <span style="position: absolute; bottom: 16px; right: 20px; font-family: var(--font-mono); font-size: 0.8rem; background: rgba(255,255,255,0.9); padding: 0.3rem 0.7rem; border-radius: var(--radius-sm);">
        ⏱ Tiempo estimado: {{ $resource->estimated_time ?? '3 min' }}
      </span>
    </div>

    <div class="card-body" style="padding: 2.5rem 2rem;">
      <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem; line-height: 1.2;">
        {{ $resource->title }}
      </h1>

      <p style="font-size: 1.1rem; color: var(--ink-600); font-style: italic; line-height: 1.7; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--ink-100);">
        {{ $resource->summary }}
      </p>

      <!-- CONTENT BODY -->
      <div style="font-size: 1.02rem; color: var(--ink-800); line-height: 1.85;" class="resource-article-content">
        {!! nl2br(e($resource->content)) !!}
      </div>

      <!-- INTERACTION ACTIONS -->
      <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--ink-100); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem;">
          @auth
            <button 
              type="button" 
              onclick="toggleResourceFavorite({{ $resource->id }}, this)" 
              class="btn btn-secondary {{ $isFavorite ? 'is-favorited' : '' }}"
            >
              {{ $isFavorite ? '⭐ Guardado en Favoritos' : '☆ Guardar en Favoritos' }}
            </button>

            <form action="{{ route('recursos.complete', $resource) }}" method="POST">
              @csrf
              <button type="submit" class="btn {{ $isCompleted ? 'btn-secondary' : 'btn-primary' }}">
                {{ $isCompleted ? '✓ Ejercicio Completado' : 'Marcar como Completado' }}
              </button>
            </form>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary">
              Iniciar sesión para guardar tu progreso →
            </a>
          @endauth
        </div>

        <a href="{{ route('recursos.index') }}" class="btn btn-secondary btn-sm">
          Explorar más ejercicios
        </a>
      </div>
    </div>
  </div>

  <!-- RELATED RESOURCES -->
  @if($relatedResources->isNotEmpty())
    <div>
      <h2 style="font-size: 1.4rem; margin-bottom: 1.5rem;">Recursos relacionados recomendados</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
        @foreach($relatedResources as $rel)
          <div class="card">
            <div class="card-body">
              <span class="badge badge-{{ $rel->color_theme }}">{{ $rel->category_label }}</span>
              <h3 style="font-size: 1.1rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                <a href="{{ route('recursos.show', $rel->slug) }}">{{ $rel->title }}</a>
              </h3>
              <p style="font-size: 0.82rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1rem;">
                {{ Str::limit($rel->summary, 90) }}
              </p>
              <a href="{{ route('recursos.show', $rel->slug) }}" style="font-size: 0.82rem; color: var(--sage-600); font-weight: 700; text-decoration: underline;">
                Leer más →
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
@endsection
