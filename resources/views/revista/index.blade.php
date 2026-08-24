@extends('layouts.guest')

@section('title', 'Revista & Foro Científico de Bienestar — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section style="padding: 4rem 1.5rem; background: #080C0A !important; color: #FFFFFF !important; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 780px; margin: 0 auto;">
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); padding: 0.35rem 0.95rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: #A8E6C0; margin-bottom: 1.25rem;">
      <i class="fa-solid fa-microscope"></i>
      <span>Comunidad Profesional & Evidencia Clínica</span>
    </div>
    <h1 style="color: #FFFFFF !important; font-size: clamp(2rem, 4.5vw, 3.2rem); margin-bottom: 0.85rem; font-weight: 700;">
      Revista de <em class="editorial-italic" style="color: #A8E6C0 !important;">Salud Mental</em>
    </h1>
    <p style="color: #C8DDD1 !important; font-size: 0.95rem; max-width: 640px; margin: 0 auto 2rem; line-height: 1.6;">
      Un espacio abierto donde profesionales de la psicología, psiquiatría y neurociencia comparten artículos, protocolos DBT y psicoeducación rigurosa.
    </p>

    <!-- ACTION BUTTONS: SEARCH & PUBLISH -->
    <div style="display: flex; gap: 0.85rem; justify-content: center; align-items: center; flex-wrap: wrap;">
      <a href="{{ route('revista.create') }}" class="btn btn-primary btn-lg" style="gap: 8px;">
        <i class="fa-solid fa-pen-nib"></i>
        <span>Publicar Artículo Científico</span>
      </a>
      <a href="#articulosGrid" class="btn btn-outline-white btn-lg" style="gap: 8px;">
        <i class="fa-solid fa-book-open-reader"></i>
        <span>Explorar Artículos</span>
      </a>
    </div>
  </div>
</section>

<div class="container" id="articulosGrid" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- TOP SEARCH & FILTER BAR -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <!-- SEARCH FORM -->
    <form method="GET" action="{{ route('revista.index') }}" style="display: flex; gap: 0.5rem; flex: 1; max-width: 480px;">
      @if(request('topic_area'))
        <input type="hidden" name="topic_area" value="{{ request('topic_area') }}">
      @endif
      <div style="position: relative; width: 100%;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8EADA4; font-size: 0.85rem;"></i>
        <input 
          type="text" 
          name="search" 
          value="{{ request('search') }}" 
          placeholder="Buscar por tema, autor, DBT o palabra clave..." 
          class="form-control" 
          style="padding-left: 2.3rem; font-size: 0.88rem; background: #FFFFFF; border-radius: 10px;"
        >
      </div>
      <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0 1rem; border-radius: 10px;">Buscar</button>
      @if(request('search'))
        <a href="{{ route('revista.index') }}" class="btn btn-sm btn-secondary" title="Limpiar búsqueda" style="border-radius: 10px;">
          <i class="fa-solid fa-xmark"></i>
        </a>
      @endif
    </form>

    <!-- PUBLISH BUTTON SHORTCUT -->
    <a href="{{ route('revista.create') }}" class="btn btn-secondary btn-sm" style="gap: 6px; border-color: #5AB56E; color: #2E5D4B; font-weight: 700; border-radius: 10px;">
      <i class="fa-solid fa-feather-pointed" style="color: #3D7A5F;"></i>
      <span>Aporte Profesional</span>
    </a>
  </div>

  <!-- FEATURED ARTICLE HERO CARD -->
  @if($featuredArticle && !request('search'))
    <div class="card" style="margin-bottom: 3rem; background: linear-gradient(145deg, #FFFFFF, #EBF5FF); border: 1.5px solid #1877F2; border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
      <div class="card-body" style="padding: 2.5rem 2rem;">
        <div style="max-width: 850px;">
          <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
            <span class="badge-blue-category">
              {{ $featuredArticle->topicArea?->name ?? $featuredArticle->category }}
            </span>
            <span class="badge-fb-verified">
              <i class="fa-solid fa-circle-check"></i> Autor Profesional Verificado
            </span>
            <span class="mono-tag" style="background: rgba(24, 119, 242, 0.08); color: #1877F2; border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.72rem; font-weight: 700;">
              {{ $featuredArticle->publication_type_label }}
            </span>
            <span class="mono-tag" style="color: #556860; display: flex; align-items: center; gap: 4px;">
              <i class="fa-regular fa-clock"></i> {{ $featuredArticle->read_time }}
            </span>
          </div>

          <h2 style="font-size: clamp(1.6rem, 3.5vw, 2.3rem); margin-bottom: 0.85rem; line-height: 1.2; color: #1A2620;">
            <a href="{{ route('revista.show', $featuredArticle->slug) }}" style="color: #1A2620; text-decoration: none;">
              {{ $featuredArticle->title }}
            </a>
          </h2>

          <p style="font-size: 1rem; color: #1A2620; line-height: 1.7; margin-bottom: 1.5rem;">
            {{ $featuredArticle->summary ?: $featuredArticle->excerpt }}
          </p>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #0064E0 0%, #0095F6 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 10px rgba(0, 149, 246, 0.25); flex-shrink: 0;">
                @if($featuredArticle->author_avatar_url)
                  <img src="{{ $featuredArticle->author_avatar_url }}" alt="{{ $featuredArticle->author_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                  {{ strtoupper(substr($featuredArticle->author_name, 0, 1)) }}
                @endif
              </div>
              <div>
                <div style="font-size: 0.94rem; font-weight: 700; color: #1A2620; display: flex; align-items: center; gap: 6px;">
                  <span>{{ $featuredArticle->author_name }}</span>
                  <x-verified-badge size="18" />
                </div>
                <div style="font-size: 0.78rem; color: #556860;">{{ $featuredArticle->author_credentials ?: $featuredArticle->author_role }}</div>
              </div>
            </div>

            <a href="{{ route('revista.show', $featuredArticle->slug) }}" class="btn btn-primary" style="gap: 6px; background: #1877F2; border-color: #1877F2; border-radius: 10px;">
              <span>Leer investigación completa</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- TOPIC AREA FILTER PILLS -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; justify-content: center;">
    <a href="{{ route('revista.index') }}" class="btn btn-sm {{ (!request('topic_area') && !request('category')) ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px; {{ (!request('topic_area') && !request('category')) ? 'background: #1877F2; border-color: #1877F2;' : '' }}">
      Todos los temas
    </a>
    @foreach($topicAreas as $area)
      @php
        $isSelected = request('topic_area') === $area->slug || request('category') === $area->name || request('category') === $area->slug;
      @endphp
      <a href="{{ route('revista.index', ['topic_area' => $area->slug, 'search' => request('search')]) }}" class="btn btn-sm {{ $isSelected ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 9999px; {{ $isSelected ? 'background: #1877F2; border-color: #1877F2;' : '' }}">
        {{ $area->name }}
      </a>
    @endforeach
  </div>

  <!-- ARTICLES FORUM GRID -->
  @if($articles->isEmpty())
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem; border-radius: 16px;">
      <i class="fa-solid fa-newspaper" style="font-size: 2.5rem; color: #8EADA4;"></i>
      <h3 style="margin-top: 1rem; margin-bottom: 0.5rem; font-size: 1.25rem;">No se encontraron artículos</h3>
      <p style="color: #556860; font-size: 0.9rem;">Prueba con otros términos de búsqueda o publica un nuevo artículo.</p>
      <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
        <a href="{{ route('revista.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 10px;">Ver todos los artículos</a>
        <a href="{{ route('revista.create') }}" class="btn btn-primary btn-sm" style="background: #1877F2; border-color: #1877F2; border-radius: 10px;">Publicar aporte profesional</a>
      </div>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
      @foreach($articles as $art)
        <div class="article-forum-card" style="border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;">
          @if($art->cover_image_path)
            <div style="height: 160px; overflow: hidden; background: #2E5D4B;">
              <img src="{{ $art->cover_image_path }}" alt="{{ $art->title }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          @endif

          <div class="card-body" style="padding: 1.65rem; display: flex; flex-direction: column; flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 4px;">
              <span class="badge-blue-category">{{ $art->topicArea?->name ?? $art->category }}</span>
              <span class="mono-tag" style="color: #556860; display: flex; align-items: center; gap: 4px; font-size: 0.75rem;">
                <i class="fa-regular fa-clock"></i> {{ $art->read_time }}
              </span>
            </div>

            <h3 style="font-size: 1.22rem; margin-bottom: 0.65rem; line-height: 1.35;">
              <a href="{{ route('revista.show', $art->slug) }}" style="color: #1A2620; text-decoration: none;">
                {{ $art->title }}
              </a>
            </h3>

            <p style="font-size: 0.88rem; color: #556860; line-height: 1.6; margin-bottom: 1.25rem; flex: 1;">
              {{ Str::limit($art->summary ?: $art->excerpt, 115) }}
            </p>

            <!-- Publication Type Pill -->
            <div style="margin-bottom: 0.85rem;">
              <span style="font-size: 0.72rem; font-family: var(--font-mono); color: #2E5D4B; background: rgba(46, 93, 75, 0.08); padding: 0.2rem 0.55rem; border-radius: 6px; font-weight: 600;">
                {{ $art->publication_type_label }}
              </span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.85rem; border-top: 1px solid #DCE8E0; gap: 0.5rem;">
              <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;">
                <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #0064E0 0%, #0095F6 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; flex-shrink: 0;">
                  @if($art->author_avatar_url)
                    <img src="{{ $art->author_avatar_url }}" alt="{{ $art->author_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                  @else
                    {{ strtoupper(substr($art->author_name, 0, 1)) }}
                  @endif
                </div>
                <div style="font-size: 0.78rem; overflow: visible; text-overflow: ellipsis; white-space: nowrap;">
                  <div style="font-weight: 700; color: #1A2620; display: flex; align-items: center; gap: 5px;">
                    <span>{{ $art->author_name }}</span>
                    <x-verified-badge size="16" />
                  </div>
                  <div style="color: #556860; font-size: 0.7rem;">{{ Str::limit($art->author_credentials ?: $art->author_role, 24) }}</div>
                </div>
              </div>

              <a href="{{ route('revista.show', $art->slug) }}" class="btn btn-sm btn-secondary" style="gap: 4px; font-size: 0.8rem; padding: 0.35rem 0.75rem; white-space: nowrap; color: #1877F2; border-color: rgba(24, 119, 242, 0.3); border-radius: 8px;">
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
