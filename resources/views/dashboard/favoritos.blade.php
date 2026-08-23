@extends('layouts.app')

@section('title', 'Mis Recursos Favoritos')

@section('content')
<div style="max-width: 1080px; margin: 0 auto;">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— TU COLECCIÓN PERSONAL</span>
      <h1 style="font-size: 1.85rem; margin-top: 0.15rem; color: var(--text-near-black);">Recursos & Ejercicios Guardados</h1>
      <p style="color: var(--text-medium-gray); font-size: 0.9rem;">Tus herramientas predilectas para acceder rápidamente cuando las necesites.</p>
    </div>
    <a href="{{ route('recursos.index') }}" class="btn btn-primary btn-sm" style="gap: 6px;">
      <i class="fa-solid fa-plus"></i>
      <span>Explorar más recursos</span>
    </a>
  </div>

  @if($favorites->isEmpty())
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
      <i class="fa-regular fa-star" style="font-size: 2.8rem; color: var(--gold-sparkles);"></i>
      <h3 style="margin-top: 1rem; margin-bottom: 0.35rem; font-size: 1.25rem;">Aún no tienes recursos guardados</h3>
      <p style="color: var(--text-medium-gray); max-width: 450px; margin: 0 auto 1.5rem; line-height: 1.6; font-size: 0.9rem;">
        Explora nuestra biblioteca de ejercicios, reflexiones y técnicas DBT. Marca los que más te ayuden para tenerlos siempre a la mano.
      </p>
      <a href="{{ route('recursos.index') }}" class="btn btn-primary" style="gap: 6px;">
        <i class="fa-solid fa-book-bookmark"></i>
        <span>Explorar Biblioteca de Recursos</span>
      </a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
      @foreach($favorites as $res)
        <div class="card">
          <div class="card-header-banner" style="background: var(--bg-subtle); height: 110px; display: flex; align-items: center; justify-content: center; position: relative;">
            <span class="badge badge-sage" style="position: absolute; top: 12px; left: 14px;">
              {{ $res->category_label }}
            </span>
            <span style="font-size: 2rem; color: var(--sage-base);">
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
              <a href="{{ route('recursos.show', $res->slug) }}" style="color: var(--text-near-black);">
                {{ $res->title }}
              </a>
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-medium-gray); line-height: 1.5; margin-bottom: 1rem;">
              {{ Str::limit($res->summary, 85) }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-light);">
              <a href="{{ route('recursos.show', $res->slug) }}" class="btn btn-sm btn-primary" style="gap: 6px;">
                <span>Practicar</span>
                <i class="fa-solid fa-arrow-right"></i>
              </a>

              <button type="button" class="btn btn-sm btn-secondary favorite-toggle-btn is-fav" data-id="{{ $res->id }}" style="color: #8A7332; gap: 4px;" title="Guardado en favoritos">
                <i class="fa-solid fa-star" style="color: var(--gold-sparkles);"></i>
                <span>Guardado</span>
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
