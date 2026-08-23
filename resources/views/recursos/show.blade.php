@extends('layouts.guest')

@section('title', $resource->title . ' — A tu lado')

@section('content')
<div class="container-narrow" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- BREADCRUMB -->
  <div style="margin-bottom: 1.5rem; font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-medium-gray);">
    <a href="{{ route('recursos.index') }}" style="color: var(--sage-base); text-decoration: underline;">← Recursos</a>
    &nbsp;/&nbsp;
    <span>{{ $resource->category_label }}</span>
  </div>

  <!-- ARTICLE CARD -->
  <div class="card" style="margin-bottom: 3rem; overflow: hidden;">
    <div class="card-header-banner" style="height: 180px; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; position: relative;">
      <span class="mono-tag" style="position: absolute; top: 18px; left: 20px; background: rgba(255,255,255,0.9); color: var(--sage-base); padding: 0.35rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
        {{ $resource->category_label }}
      </span>
      <span style="font-size: 3.5rem; color: var(--sage-base);">
        @if($resource->color_theme === 'sage') <i class="fa-solid fa-seedling"></i>
        @elseif($resource->color_theme === 'sky') <i class="fa-solid fa-water"></i>
        @elseif($resource->color_theme === 'lav') <i class="fa-solid fa-spa"></i>
        @elseif($resource->color_theme === 'terra') <i class="fa-solid fa-leaf"></i>
        @elseif($resource->color_theme === 'amber') <i class="fa-solid fa-sun"></i>
        @else <i class="fa-solid fa-circle-exclamation"></i>
        @endif
      </span>
      <span style="position: absolute; bottom: 16px; right: 20px; font-family: var(--font-mono); font-size: 0.78rem; background: rgba(255,255,255,0.9); color: var(--text-near-black); padding: 0.35rem 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
        ⏱ Tiempo estimado: {{ $resource->estimated_time ?? '3 min' }}
      </span>
    </div>

    <div class="card-body" style="padding: 2.5rem 2rem;">
      <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem; line-height: 1.2; color: var(--text-near-black);">
        {{ $resource->title }}
      </h1>

      <p style="font-size: 1.1rem; color: var(--text-medium-gray); font-style: italic; line-height: 1.7; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-light);">
        {{ $resource->summary }}
      </p>

      <!-- CONTENT BODY -->
      <div style="font-size: 1.02rem; color: var(--text-near-black); line-height: 1.85;" class="resource-article-content">
        {!! nl2br(e($resource->content)) !!}
      </div>

      <!-- INTERACTION ACTIONS -->
      <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
          @auth
            <button 
              type="button" 
              class="btn btn-secondary favorite-toggle-btn {{ $isFavorite ? 'is-fav' : '' }}" 
              data-id="{{ $resource->id }}"
              style="gap: 6px;"
            >
              <i class="fa-solid fa-star" style="color: {{ $isFavorite ? 'var(--gold-sparkles)' : 'var(--text-light-gray)' }};"></i>
              <span>{{ $isFavorite ? 'Guardado en Favoritos' : 'Guardar en Favoritos' }}</span>
            </button>

            <form action="{{ route('recursos.complete', $resource) }}" method="POST">
              @csrf
              <button type="submit" class="btn {{ $isCompleted ? 'btn-secondary' : 'btn-primary' }}" style="gap: 6px;">
                <i class="fa-solid fa-check"></i>
                <span>{{ $isCompleted ? 'Completado' : 'Marcar como Completado' }}</span>
              </button>
            </form>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary" style="gap: 6px;">
              <span>Iniciar sesión para guardar tu progreso</span>
              <i class="fa-solid fa-arrow-right"></i>
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
      <h2 style="font-size: 1.4rem; margin-bottom: 1.5rem; color: var(--text-near-black);">Recursos relacionados recomendados</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
        @foreach($relatedResources as $rel)
          <div class="card">
            <div class="card-body">
              <span class="badge badge-sage">{{ $rel->category_label }}</span>
              <h3 style="font-size: 1.1rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                <a href="{{ route('recursos.show', $rel->slug) }}" style="color: var(--text-near-black);">{{ $rel->title }}</a>
              </h3>
              <p style="font-size: 0.82rem; color: var(--text-medium-gray); line-height: 1.6; margin-bottom: 1rem;">
                {{ Str::limit($rel->summary, 90) }}
              </p>
              <a href="{{ route('recursos.show', $rel->slug) }}" style="font-size: 0.82rem; color: var(--sage-base); font-weight: 700; text-decoration: underline;">
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
