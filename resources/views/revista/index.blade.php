@extends('layouts.guest')

@section('title', 'Revista de Salud Mental y Bienestar — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section style="padding: 4rem 1.5rem; background: #080C0A !important; color: #FFFFFF !important; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 760px; margin: 0 auto;">
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); padding: 0.35rem 0.95rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: #A8E6C0; margin-bottom: 1.5rem;">
      <i class="fa-solid fa-newspaper"></i>
      <span>Psicoeducación Basada en Evidencia</span>
    </div>
    <h1 style="color: #FFFFFF !important; font-size: clamp(2rem, 4.5vw, 3.2rem); margin-bottom: 0.85rem; font-weight: 700;">
      Revista de <em class="editorial-italic" style="color: #A8E6C0 !important;">Bienestar</em>
    </h1>
    <p style="color: #C8DDD1 !important; font-size: 0.95rem;">
      Artículos rigurosos, comprensibles y compasivos sobre regulación emocional, neurociencia, DBT y salud mental cotidiana.
    </p>
  </div>
</section>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- FEATURED ARTICLE HERO CARD -->
  @if($featuredArticle)
    <div class="card" style="margin-bottom: 3.5rem; background: linear-gradient(145deg, #FFFFFF, #D4EDE2); border: 1.5px solid #5AB56E;">
      <div class="card-body" style="padding: 2.5rem 2rem;">
        <div style="max-width: 850px;">
          <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
            <span class="badge badge-sage">Artículo Destacado</span>
            <span class="mono-tag" style="color: #556860; display: flex; align-items: center; gap: 4px;">
              <i class="fa-regular fa-clock"></i> Lectura: {{ $featuredArticle->read_time }}
            </span>
          </div>

          <h2 style="font-size: clamp(1.6rem, 3.5vw, 2.3rem); margin-bottom: 0.85rem; line-height: 1.2; color: #1A2620;">
            <a href="{{ route('revista.show', $featuredArticle->slug) }}" style="color: #1A2620;">
              {{ $featuredArticle->title }}
            </a>
          </h2>

          <p style="font-size: 1rem; color: #1A2620; line-height: 1.7; margin-bottom: 1.5rem;">
            {{ $featuredArticle->excerpt }}
          </p>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <div style="width: 36px; height: 36px; border-radius: 50%; background: #2E5D4B; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem;">
                {{ strtoupper(substr($featuredArticle->author_name, 0, 1)) }}
              </div>
              <div>
                <div style="font-size: 0.88rem; font-weight: 700; color: #1A2620;">{{ $featuredArticle->author_name }}</div>
                <div style="font-size: 0.75rem; color: #556860;">{{ $featuredArticle->author_role }}</div>
              </div>
            </div>

            <a href="{{ route('revista.show', $featuredArticle->slug) }}" class="btn btn-primary" style="gap: 6px;">
              <span>Leer artículo completo</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- CATEGORY FILTER -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    <a href="{{ route('revista.index') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
      Todos los temas
    </a>
    @foreach($categories as $cat)
      <a href="{{ route('revista.index', ['category' => $cat]) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px;">
        {{ $cat }}
      </a>
    @endforeach
  </div>

  <!-- ARTICLES GRID -->
  @if($articles->isEmpty())
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
      <i class="fa-solid fa-newspaper" style="font-size: 2.5rem; color: #8EADA4;"></i>
      <h3 style="margin-top: 1rem; margin-bottom: 0.5rem; font-size: 1.25rem;">No hay artículos en esta categoría</h3>
      <a href="{{ route('revista.index') }}" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Ver todos los artículos</a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
      @foreach($articles as $art)
        <div class="card">
          <div class="card-body" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
              <span class="badge badge-sage">{{ $art->category }}</span>
              <span class="mono-tag" style="color: #556860; display: flex; align-items: center; gap: 4px;">
                <i class="fa-regular fa-clock"></i> {{ $art->read_time }}
              </span>
            </div>

            <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; line-height: 1.3;">
              <a href="{{ route('revista.show', $art->slug) }}" style="color: #1A2620;">
                {{ $art->title }}
              </a>
            </h3>

            <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem;">
              {{ Str::limit($art->excerpt, 110) }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid #DCE8E0;">
              <span style="font-size: 0.78rem; color: #556860;">Por {{ $art->author_name }}</span>
              <a href="{{ route('revista.show', $art->slug) }}" class="btn btn-sm btn-secondary" style="gap: 4px;">
                <span>Leer</span>
                <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- PAGINATION -->
    <div style="display: flex; justify-content: center;">
      {{ $articles->links() }}
    </div>
  @endif

</div>
@endsection
