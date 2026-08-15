@extends('layouts.app')

@section('title', 'Mis Recursos Favoritos')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sage-600);">Tu Colección Personal</span>
      <h1 style="font-size: 2rem; margin-top: 0.2rem;">Recursos y Ejercicios Guardados</h1>
      <p style="color: var(--ink-600); font-size: 0.95rem;">Tus herramientas predilectas para acceder rápidamente cuando las necesites.</p>
    </div>
    <a href="{{ route('recursos.index') }}" class="btn btn-primary btn-sm">
      + Explorar más recursos
    </a>
  </div>

  @if($favorites->isEmpty())
    <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
      <span style="font-size: 3rem;">⭐</span>
      <h3 style="margin-top: 1rem; margin-bottom: 0.5rem;">Aún no tienes recursos guardados</h3>
      <p style="color: var(--ink-600); max-width: 450px; margin: 0 auto 1.5rem; line-height: 1.6;">
        Explora nuestra biblioteca de ejercicios, reflexiones y técnicas DBT. Marca los que más te ayuden para tenerlos siempre a la mano.
      </p>
      <a href="{{ route('recursos.index') }}" class="btn btn-primary">
        Explorar Biblioteca de Recursos →
      </a>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
      @foreach($favorites as $res)
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
              <a href="{{ route('recursos.show', $res->slug) }}" class="btn btn-sm btn-primary">
                Practicar ahora
              </a>

              <form action="{{ route('recursos.favorite', $res) }}" method="POST">
                @csrf
                <button type="submit" style="background: none; border: none; color: #f59e0b; cursor: pointer; font-size: 1.1rem;" title="Quitar de favoritos">
                  ⭐
                </button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div style="margin-top: 2rem;">
      {{ $favorites->links() }}
    </div>
  @endif

</div>
@endsection
