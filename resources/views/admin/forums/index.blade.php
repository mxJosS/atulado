@extends('layouts.admin')

@section('title', 'Moderación y Artículos de Foros — Consola Administrador')

@section('content')
<div style="max-width: 1140px; margin: 0 auto;">

  <!-- HEADER -->
  <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— COMUNIDAD & CONTENIDO CIENTÍFICO</span>
      <h1 style="font-size: 2rem; margin-top: 0.2rem; color: #1A2620; display: flex; align-items: center; gap: 12px; font-family: 'Fraunces', serif;">
        <i class="fa-solid fa-newspaper" style="color: #2E5D4B;"></i>
        <span>Moderación de Foros & Revista</span>
      </h1>
      <p style="color: #556860; font-size: 0.95rem; margin-top: 0.25rem;">
        Revisión de divulgaciones clínicas, debates comunitarios y publicaciones de especialistas.
      </p>
    </div>

    <div style="display: flex; gap: 10px;">
      <a href="{{ route('revista.create') }}" target="_blank" class="btn btn-primary btn-sm" style="gap: 8px; font-size: 0.85rem; padding: 0.6rem 1.1rem; border-radius: 9px;">
        <i class="fa-solid fa-pen-nib"></i>
        <span>Redactar Publicación</span>
      </a>
      <a href="{{ route('revista.index') }}" target="_blank" class="btn btn-secondary btn-sm" style="gap: 8px; font-size: 0.85rem; padding: 0.6rem 1.1rem; border-radius: 9px;">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        <span>Ver Revista Pública</span>
      </a>
    </div>
  </div>

  <!-- FILTROS & BUSCADOR -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <!-- Tabs Estado -->
    <div style="display: flex; gap: 6px; background: #E8EFEA; padding: 4px; border-radius: 10px;">
      <a href="{{ route('admin.forums.index') }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ !request('estado') ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Todos
      </a>
      <a href="{{ route('admin.forums.index', ['estado' => 'published']) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ request('estado') === 'published' ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Publicados
      </a>
      <a href="{{ route('admin.forums.index', ['estado' => 'draft']) }}" 
         style="padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.82rem; font-weight: 600; text-decoration: none; {{ request('estado') === 'draft' ? 'background: white; color: #1A2620; box-shadow: var(--shadow-xs);' : 'color: #556860;' }}">
        Borradores
      </a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="{{ route('admin.forums.index') }}" style="display: flex; gap: 8px; margin: 0;">
      @if(request('estado'))
        <input type="hidden" name="estado" value="{{ request('estado') }}">
      @endif
      <div style="position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8CA399; font-size: 0.85rem;"></i>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por título o autor..." 
               class="form-control" style="padding-left: 34px; width: 280px; font-size: 0.85rem; border-radius: 9px; height: 38px;">
      </div>
      <button type="submit" class="btn btn-secondary btn-sm" style="height: 38px; border-radius: 9px; padding: 0 1rem;">
        Buscar
      </button>
    </form>
  </div>

  <!-- LISTADO DE ARTÍCULOS / FOROS -->
  <div class="card" style="padding: 0; border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.06); overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
        <thead>
          <tr style="background: #F4F7F5; border-bottom: 1px solid rgba(0,0,0,0.06); color: #556860; font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <th style="padding: 1rem 1.25rem;">Artículo / Foro</th>
            <th style="padding: 1rem 1.25rem;">Área Temática</th>
            <th style="padding: 1rem 1.25rem;">Autor / Especialista</th>
            <th style="padding: 1rem 1.25rem;">Estado</th>
            <th style="padding: 1rem 1.25rem; text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody style="divide-y: 1px solid rgba(0,0,0,0.04);">
          @forelse($articles as $art)
            <tr style="border-bottom: 1px solid rgba(0,0,0,0.04); transition: background 0.15s ease;" onmouseover="this.style.background='#FBFDFB'" onmouseout="this.style.background='transparent'">
              <!-- Título & Resumen -->
              <td style="padding: 1rem 1.25rem; max-width: 380px;">
                <div style="font-weight: 700; color: #1A2620; font-size: 0.92rem; line-height: 1.35; margin-bottom: 3px;">
                  <a href="{{ route('revista.show', $art->slug) }}" target="_blank" style="color: inherit; text-decoration: none;">
                    {{ $art->title }}
                  </a>
                </div>
                <div style="font-size: 0.78rem; color: #6E887E; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ $art->summary }}
                </div>
              </td>

              <!-- Área Temática -->
              <td style="padding: 1rem 1.25rem;">
                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 0.25rem 0.65rem; border-radius: 999px; background: #E8EFEA; color: #2E5D4B; font-size: 0.74rem; font-weight: 600;">
                  <i class="fa-solid {{ $art->topicArea->icon ?? 'fa-brain' }}"></i>
                  {{ $art->topicArea->name ?? 'General' }}
                </span>
              </td>

              <!-- Autor -->
              <td style="padding: 1rem 1.25rem;">
                <div style="font-weight: 600; color: #1A2620; font-size: 0.85rem;">
                  {{ $art->author_name }}
                </div>
                <div style="font-size: 0.72rem; color: #6E887E;">
                  {{ $art->author_credentials ? Str::limit($art->author_credentials, 32) : 'Especialista' }}
                </div>
              </td>

              <!-- Estado -->
              <td style="padding: 1rem 1.25rem;">
                @if($art->status === 'published')
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.2rem 0.55rem; border-radius: 6px; background: #D1FAE5; color: #065F46; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-check"></i> Publicado
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 0.2rem 0.55rem; border-radius: 6px; background: #FEF3C7; color: #92400E; font-size: 0.72rem; font-weight: 700;">
                    <i class="fa-solid fa-file-lines"></i> Borrador
                  </span>
                @endif
              </td>

              <!-- Acciones -->
              <td style="padding: 1rem 1.25rem; text-align: right;">
                <a href="{{ route('revista.show', $art->slug) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.75rem; border-radius: 7px;">
                  <i class="fa-solid fa-eye"></i> Leer
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="text-align: center; padding: 3rem; color: #6E887E;">
                No se encontraron artículos en este filtro.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    @if($articles->hasPages())
      <div style="padding: 1rem 1.25rem; background: #F8FAF9; border-top: 1px solid rgba(0,0,0,0.06);">
        {{ $articles->links() }}
      </div>
    @endif
  </div>

</div>
@endsection