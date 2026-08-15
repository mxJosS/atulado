@extends('layouts.guest')

@section('title', $article->title . ' — Revista A tu lado')

@section('content')
<div class="container-narrow" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- BREADCRUMB -->
  <div style="margin-bottom: 1.5rem; font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink-400);">
    <a href="{{ route('revista.index') }}" style="color: var(--sage-600); text-decoration: underline;">← Revista</a>
    &nbsp;/&nbsp;
    <span>{{ $article->category }}</span>
  </div>

  <!-- ARTICLE HEADER -->
  <header style="margin-bottom: 2.5rem;">
    <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem;">
      <span class="badge badge-{{ $article->color_tag }}">{{ $article->category }}</span>
      <span class="mono-tag" style="color: var(--ink-400);">⏱ {{ $article->read_time }} de lectura</span>
      <span class="mono-tag" style="color: var(--ink-400);">· {{ $article->formatted_date }}</span>
    </div>

    <h1 style="font-size: clamp(2rem, 4.5vw, 3rem); line-height: 1.15; margin-bottom: 1.25rem;">
      {{ $article->title }}
    </h1>

    <p style="font-size: 1.15rem; color: var(--ink-600); font-style: italic; line-height: 1.7; padding-bottom: 1.5rem; border-bottom: 1px solid var(--ink-100);">
      {{ $article->excerpt }}
    </p>

    <!-- AUTHOR CARD -->
    <div style="display: flex; align-items: center; gap: 0.85rem; margin-top: 1.25rem;">
      <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--sage-500); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
        {{ strtoupper(substr($article->author_name, 0, 1)) }}
      </div>
      <div>
        <div style="font-size: 0.95rem; font-weight: 700; color: var(--ink-900);">{{ $article->author_name }}</div>
        <div style="font-size: 0.8rem; color: var(--ink-400);">{{ $article->author_role }}</div>
      </div>
    </div>
  </header>

  <!-- ARTICLE BODY CONTENT -->
  <article class="card" style="margin-bottom: 3.5rem;">
    <div class="card-body" style="padding: 3rem 2.5rem; font-size: 1.08rem; color: var(--ink-800); line-height: 1.9;">
      {!! nl2br(e($article->content)) !!}
    </div>
  </article>

  <!-- RECOMMENDED TOOLS BANNER -->
  @if($recommendedResources->isNotEmpty())
    <div class="card" style="background: var(--sage-50); border: 1px solid var(--sage-200); margin-bottom: 3.5rem;">
      <div class="card-body" style="padding: 2rem;">
        <span class="mono-tag" style="color: var(--sage-700);">Herramientas prácticas relacionadas</span>
        <h3 style="font-size: 1.35rem; margin-top: 0.35rem; margin-bottom: 1.25rem;">Pon en práctica lo aprendido hoy:</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
          @foreach($recommendedResources as $r)
            <div class="card" style="background: #ffffff;">
              <div class="card-body" style="padding: 1.25rem;">
                <span class="badge badge-{{ $r->color_theme }}">{{ $r->category_label }}</span>
                <h4 style="font-size: 1rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                  <a href="{{ route('recursos.show', $r->slug) }}">{{ $r->title }}</a>
                </h4>
                <a href="{{ route('recursos.show', $r->slug) }}" style="font-size: 0.8rem; color: var(--sage-600); font-weight: 700; text-decoration: underline;">
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
      <h2 style="font-size: 1.4rem; margin-bottom: 1.5rem;">Otros artículos de la revista</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
        @foreach($relatedArticles as $rel)
          <div class="card">
            <div class="card-body">
              <span class="badge badge-{{ $rel->color_tag }}">{{ $rel->category }}</span>
              <h3 style="font-size: 1.05rem; margin-top: 0.5rem; margin-bottom: 0.35rem;">
                <a href="{{ route('revista.show', $rel->slug) }}">{{ $rel->title }}</a>
              </h3>
              <p style="font-size: 0.82rem; color: var(--ink-600); line-height: 1.5; margin-bottom: 1rem;">
                {{ Str::limit($rel->excerpt, 80) }}
              </p>
              <a href="{{ route('revista.show', $rel->slug) }}" style="font-size: 0.82rem; color: var(--sage-600); font-weight: 700; text-decoration: underline;">
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
