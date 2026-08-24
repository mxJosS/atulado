@extends('layouts.guest')

@section('title', $article->title . ' — Revista A tu lado')

@section('content')
<div class="container-narrow" style="padding-top: 3.5rem; padding-bottom: 5rem; max-width: 820px; margin: 0 auto;">

  <!-- BREADCRUMB -->
  <div style="margin-bottom: 1.5rem; font-family: var(--font-mono); font-size: 0.82rem; color: #556860;">
    <a href="{{ route('revista.index') }}" style="color: #2E5D4B; text-decoration: none; font-weight: 600;">
      <i class="fa-solid fa-arrow-left"></i> Revista
    </a>
    &nbsp;/&nbsp;
    <span>{{ $article->topicArea?->name ?? $article->category }}</span>
  </div>

  <!-- COVER IMAGE IF PRESENT -->
  @if($article->cover_image_path)
    <div style="width: 100%; max-height: 380px; border-radius: 16px; overflow: hidden; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
      <img src="{{ $article->cover_image_path }}" alt="{{ $article->title }}" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
  @endif

  <!-- ARTICLE HEADER -->
  <header style="margin-bottom: 2.5rem;">
    <div style="display: flex; gap: 0.65rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
      <span class="badge-blue-category">
        {{ $article->topicArea?->name ?? $article->category }}
      </span>
      <span class="badge-fb-verified">
        <i class="fa-solid fa-circle-check"></i>
        <span>Aporte Profesional Verificado</span>
      </span>
      <span class="mono-tag" style="background: rgba(46, 93, 75, 0.08); color: #2E5D4B; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.74rem;">
        {{ $article->publication_type_label }}
      </span>
      <span class="mono-tag" style="color: #556860; display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem;">
        <i class="fa-regular fa-clock"></i> {{ $article->read_time }} de lectura
      </span>
      <span class="mono-tag" style="color: #556860; font-size: 0.76rem;">· {{ $article->formatted_date }}</span>
    </div>

    <h1 style="font-size: clamp(2rem, 4.5vw, 3rem); line-height: 1.15; margin-bottom: 1.25rem; color: #1A2620; font-weight: 700;">
      {{ $article->title }}
    </h1>

    <div style="background: #F4F8F5; border-left: 4px solid #2E5D4B; padding: 1.2rem 1.4rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem;">
      <div style="font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 0.35rem;">
        Resumen Científico
      </div>
      <p style="font-size: 1.05rem; color: #1A2620; font-style: italic; line-height: 1.65; margin: 0;">
        {{ $article->summary ?: $article->excerpt }}
      </p>
    </div>

    <!-- TARGET AUDIENCE NOTICE -->
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #556860; margin-bottom: 1.5rem;">
      <i class="fa-solid fa-users-viewfinder" style="color: #2E5D4B;"></i>
      <span>Nivel recomendado: <strong>{{ $article->target_audience_label }}</strong></span>
    </div>

    <!-- AUTHOR CARD -->
    <div style="display: flex; align-items: center; gap: 0.85rem; padding-top: 1rem; border-top: 1px solid #DCE8E0;">
      <div style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #0064E0 0%, #0095F6 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(0, 149, 246, 0.25); flex-shrink: 0;">
        @if($article->author_avatar_url)
          <img src="{{ $article->author_avatar_url }}" alt="{{ $article->author_name }}" style="width: 100%; height: 100%; object-fit: cover;">
        @else
          {{ strtoupper(substr($article->author_name, 0, 1)) }}
        @endif
      </div>
      <div>
        <div style="font-size: 1.05rem; font-weight: 700; color: #1A2620; display: flex; align-items: center; gap: 6px;">
          <span>{{ $article->author_name }}</span>
          <x-verified-badge size="19" />
        </div>
        <div style="font-size: 0.84rem; color: #556860;">{{ $article->author_credentials ?: $article->author_role }}</div>
      </div>
    </div>
  </header>

  <!-- ARTICLE BODY CONTENT -->
  <article class="card" style="margin-bottom: 2.5rem; border-radius: 16px; box-shadow: var(--shadow-sm);">
    <div class="card-body" style="padding: 3rem 2.5rem; font-size: 1.08rem; color: #1A2620; line-height: 1.9;">
      {!! nl2br(e($article->content)) !!}

      <!-- DISCUSSION PROMPT BOX -->
      @if($article->discussion_prompt)
        <div style="margin-top: 3rem; background: linear-gradient(135deg, #FFFDF5 0%, #FEF9E7 100%); border: 1.5px solid #F9E79F; border-radius: 14px; padding: 1.5rem 1.75rem;">
          <div style="font-family: var(--font-mono); font-size: 0.74rem; text-transform: uppercase; color: #B7950B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-comments"></i>
            <span>Pregunta para la Reflexión & Debate</span>
          </div>
          <h4 style="font-size: 1.05rem; color: #7D6608; margin: 0; line-height: 1.5; font-weight: 600;">
            "{{ $article->discussion_prompt }}"
          </h4>
        </div>
      @endif

      <!-- REFERENCES LIST (APA) -->
      @if($article->references || $article->references_list)
        <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1.5px solid #DCE8E0;">
          <div style="font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #2E5D4B; font-weight: 700; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-book-journal-whills"></i>
            <span>Referencias Bibliográficas (Formato APA)</span>
          </div>
          <div style="font-size: 0.88rem; color: #556860; line-height: 1.7; background: #F8FAF9; padding: 1.25rem 1.4rem; border-radius: 12px; border: 1px solid #DCE8E0; white-space: pre-line;">
            {{ $article->references ?: $article->references_list }}
          </div>
        </div>
      @endif
    </div>
  </article>

  <!-- RECOMMENDED TOOLS BANNER -->
  @if($recommendedResources->isNotEmpty())
    <div class="card" style="background: #EBF5EF; border: 1.5px solid #C8DDD1; margin-bottom: 3.5rem; border-radius: 16px;">
      <div class="card-body" style="padding: 2rem;">
        <span class="mono-tag" style="color: #2E5D4B; font-weight: 700;">— HERRAMIENTAS PRÁCTICAS RELACIONADAS</span>
        <h3 style="font-size: 1.35rem; margin-top: 0.35rem; margin-bottom: 1.25rem; color: #1A2620;">Pon en práctica lo aprendido hoy:</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
          @foreach($recommendedResources as $r)
            <div class="card" style="background: #ffffff; border-radius: 12px;">
              <div class="card-body" style="padding: 1.25rem;">
                <span class="badge badge-sage">{{ $r->category_label }}</span>
                <h4 style="font-size: 1rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                  <a href="{{ route('recursos.show', $r->slug) }}" style="color: #1A2620; text-decoration: none;">{{ $r->title }}</a>
                </h4>
                <a href="{{ route('recursos.show', $r->slug) }}" style="font-size: 0.8rem; color: #2E5D4B; font-weight: 700; text-decoration: underline;">
                  Practicar técnica →
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  @endif

  <!-- RELATED ARTICLES -->
  @if($relatedArticles->isNotEmpty())
    <div>
      <h2 style="font-size: 1.4rem; margin-bottom: 1.5rem; color: #1A2620;">Otros artículos de la revista</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
        @foreach($relatedArticles as $rel)
          <div class="card" style="border-radius: 14px;">
            <div class="card-body" style="padding: 1.35rem;">
              <span class="badge badge-sage">{{ $rel->topicArea?->name ?? $rel->category }}</span>
              <h3 style="font-size: 1.05rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                <a href="{{ route('revista.show', $rel->slug) }}" style="color: #1A2620; text-decoration: none;">{{ $rel->title }}</a>
              </h3>
              <p style="font-size: 0.82rem; color: #556860; line-height: 1.5; margin-bottom: 1rem;">
                {{ Str::limit($rel->summary ?: $rel->excerpt, 80) }}
              </p>
              <a href="{{ route('revista.show', $rel->slug) }}" style="font-size: 0.82rem; color: #2E5D4B; font-weight: 700; text-decoration: underline;">
                Leer artículo →
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
@endsection
