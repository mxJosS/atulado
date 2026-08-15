@extends('layouts.guest')

@section('title', 'Revista de Salud Mental y Bienestar — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section class="hero-wrapper" style="padding: 4rem 1.5rem 5rem; background: var(--dark-900);">
  <div class="hero-halo" style="top: 35%; left: 50%;"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">
      <span>📖</span> Psicoeducación Basada en Evidencia
    </div>
    <h1 class="hero-title">
      Revista de <em>Bienestar</em>
    </h1>
    <p class="hero-subtitle">
      Artículos rigurosos, comprensibles y compasivos sobre regulación emocional, neurociencia, DBT y salud mental cotidiana.
    </p>
  </div>
</section>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- FEATURED ARTICLE HERO CARD -->
  @if($featuredArticle)
    <div class="card" style="margin-bottom: 3.5rem; background: linear-gradient(145deg, #ffffff, var(--sage-50)); border: 1.5px solid var(--sage-200);">
      <div class="card-body" style="padding: 3rem 2.5rem;">
        <div style="max-width: 850px;">
          <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem;">
            <span class="badge badge-sage">Artículo Destacado</span>
            <span class="mono-tag" style="color: var(--ink-400);">⏱ Lectura: {{ $featuredArticle->read_time }}</span>
          </div>

          <h2 style="font-size: clamp(1.8rem, 4vw, 2.6rem); margin-bottom: 1rem; line-height: 1.15;">
            <a href="{{ route('revista.show', $featuredArticle->slug) }}" style="color: var(--ink-900);">
              {{ $featuredArticle->title }}
            </a>
          </h2>

          <p style="font-size: 1.05rem; color: var(--ink-600); line-height: 1.75; margin-bottom: 1.75rem;">
            {{ $featuredArticle->excerpt }}
          </p>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--sage-500); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                {{ strtoupper(substr($featuredArticle->author_name, 0, 1)) }}
              </div>
              <div>
                <div style="font-size: 0.9rem; font-weight: 700; color: var(--ink-900);">{{ $featuredArticle->author_name }}</div>
                <div style="font-size: 0.75rem; color: var(--ink-400);">{{ $featuredArticle->author_role }}</div>
              </div>
            </div>

            <a href="{{ route('revista.show', $featuredArticle->slug) }}" class="btn btn-primary">
              Leer artículo completo →
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- CATEGORY FILTER -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    <a href="{{ route('revista.index') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: var(--radius-full);">
      Todos los temas
    </a>
    @foreach($categories as $cat)
      <a href="{{ route('revista.index', ['category' => $cat]) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: var(--radius-full);">
        {{ $cat }}
      </a>
    @endforeach
  </div>

  <!-- ARTICLES GRID -->
  @if($articles->isEmpty())
    <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
      <span style="font-size: 3rem;">📖</span>
      <h3 style="margin-top: 1rem; margin-bottom: 0.5rem;">No hay artículos en esta categoría</h3>
      <a href="{{ route('revista.index') }}" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Ver todos los artículos</a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.75rem; margin-bottom: 3.5rem;">
      @foreach($articles as $art)
        <div class="card">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <span class="badge badge-{{ $art->color_tag }}">{{ $art->category }}</span>
              <span class="mono-tag" style="color: var(--ink-400);">⏱ {{ $art->read_time }}</span>
            </div>

            <h3 style="font-size: 1.25rem; margin-bottom: 0.65rem; line-height: 1.3;">
              <a href="{{ route('revista.show', $art->slug) }}" style="color: var(--ink-900);">
                {{ $art->title }}
              </a>
            </h3>

            <p style="font-size: 0.88rem; color: var(--ink-600); line-height: 1.65; margin-bottom: 1.5rem;">
              {{ $art->excerpt }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--ink-100);">
              <span style="font-size: 0.8rem; color: var(--ink-600);">Por {{ $art->author_name }}</span>
              <a href="{{ route('revista.show', $art->slug) }}" style="color: var(--sage-600); font-weight: 700; font-size: 0.85rem; text-decoration: underline;">
                Leer más →
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div>
      {{ $articles->links() }}
    </div>
  @endif

</div>
@endsection
