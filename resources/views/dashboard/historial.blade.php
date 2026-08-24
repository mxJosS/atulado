@extends('layouts.app')

@section('title', 'Historial y Analíticas Emocionales')

@section('content')
<div style="max-width: 1080px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— EVOLUCIÓN PERSONAL</span>
      <h1 style="font-size: 1.85rem; margin-top: 0.15rem; color: var(--text-near-black);">Historial & Analíticas</h1>
      <p style="color: var(--text-medium-gray); font-size: 0.9rem;">Observa tus patrones emocionales, tus anclas de gratitud y tu constancia.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="gap: 6px;">
      <i class="fa-solid fa-arrow-left"></i>
      <span>Volver al Dashboard</span>
    </a>
  </div>

  <!-- STATS OVERVIEW CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="card" style="border-top: 4px solid var(--sage-base);">
      <div class="card-body" style="text-align: center; padding: 1.25rem;">
        <span class="mono-tag" style="color: var(--sage-base);">Total de Check-ins</span>
        <div style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 800; color: var(--sage-base); margin-top: 0.2rem; line-height: 1;">
          {{ $totalLogs }}
        </div>
        <div style="font-size: 0.78rem; color: var(--text-medium-gray); margin-top: 0.25rem;">Días registrados</div>
      </div>
    </div>

    <div class="card" style="border-top: 4px solid var(--violet-base);">
      <div class="card-body" style="text-align: center; padding: 1.25rem;">
        <span class="mono-tag" style="color: var(--violet-base);">Promedio General</span>
        <div style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 800; color: var(--violet-base); margin-top: 0.2rem; line-height: 1;">
          {{ $averageScore ?? '—' }} <span style="font-size: 1.1rem; color: var(--text-light-gray);">/ 5.0</span>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-medium-gray); margin-top: 0.25rem;">Índice de bienestar</div>
      </div>
    </div>

    <div class="card" style="border-top: 4px solid #8A7332;">
      <div class="card-body" style="text-align: center; padding: 1.25rem;">
        <span class="mono-tag" style="color: #8A7332;">Emoción Frecuente</span>
        @php
          $topEmotion = $emotionDistribution->sortDesc()->keys()->first() ?? 'Sin registros';
        @endphp
        <div style="font-size: 1.5rem; font-family: var(--font-display); font-weight: 700; color: #8A7332; margin-top: 0.4rem; line-height: 1.1;">
          {{ $topEmotion }}
        </div>
        <div style="font-size: 0.78rem; color: var(--text-medium-gray); margin-top: 0.25rem;">Predominante en tu camino</div>
      </div>
    </div>
  </div>

  <!-- 30-DAY INTERACTIVE TIMELINE CHART (SVG BASED) -->
  <div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-body">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
          <span class="mono-tag" style="color: var(--sage-base);">Tendencia</span>
          <h2 style="font-size: 1.25rem; margin-top: 0.15rem;">Últimos 30 días</h2>
        </div>
        <div style="display: flex; gap: 0.75rem; font-size: 0.75rem; font-family: var(--font-mono); flex-wrap: wrap;">
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #1E8449;"></span> Excelente (5)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #3D7A5F;"></span> Bien (4)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #D4AC0D;"></span> Regular (3)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #5B4A8A;"></span> Mal (2)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #C0392B;"></span> Terrible (1)</span>
        </div>
      </div>

      <!-- Line & Bar Chart -->
      <div style="width: 100%; overflow-x: auto;">
        <div style="min-width: 600px; height: 160px; display: flex; align-items: flex-end; gap: 8px; padding-bottom: 2rem; border-bottom: 1px solid var(--border-light); position: relative;">
          @foreach($last30Days as $pt)
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; position: relative;" title="{{ $pt['date'] }}: {{ $pt['emotion'] ?? 'Sin registro' }} ({{ $pt['score'] ?? '-' }}/5)">
              @if($pt['score'])
                @php
                  $hPercent = ($pt['score'] / 5) * 100;
                  $barColor = match($pt['score']) {
                    5 => '#1E8449',
                    4 => '#3D7A5F',
                    3 => '#D4AC0D',
                    2 => '#5B4A8A',
                    1 => '#C0392B',
                    default => '#3D7A5F'
                  };
                @endphp
                <div style="width: 100%; max-width: 16px; height: {{ $hPercent }}%; background: {{ $barColor }}; border-radius: 4px 4px 0 0; transition: height 0.4s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.08);"></div>
              @else
                <div style="width: 100%; max-width: 16px; height: 4px; background: var(--border-light); border-radius: 2px;"></div>
              @endif
              <div style="position: absolute; bottom: -22px; font-family: var(--font-mono); font-size: 0.65rem; color: var(--text-medium-gray); white-space: nowrap; transform: rotate(-45deg); transform-origin: left top;">
                {{ $loop->iteration % 4 === 1 ? $pt['date'] : '' }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <!-- FILTER TOOLBAR -->
  <div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
      <form method="GET" action="{{ route('mood.history') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 180px;">
          <label for="score" class="form-label" style="margin: 0; white-space: nowrap; font-size: 0.8rem;">Filtrar nivel:</label>
          <select name="score" id="score" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.82rem;" onchange="this.form.submit()">
            <option value="">Todos los niveles</option>
            <option value="5" {{ request('score') == 5 ? 'selected' : '' }}>Excelente (5)</option>
            <option value="4" {{ request('score') == 4 ? 'selected' : '' }}>Bien (4)</option>
            <option value="3" {{ request('score') == 3 ? 'selected' : '' }}>Regular (3)</option>
            <option value="2" {{ request('score') == 2 ? 'selected' : '' }}>Mal (2)</option>
            <option value="1" {{ request('score') == 1 ? 'selected' : '' }}>Terrible (1)</option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 180px;">
          <label for="month" class="form-label" style="margin: 0; white-space: nowrap; font-size: 0.8rem;">Mes:</label>
          <input type="month" name="month" id="month" value="{{ request('month') }}" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.82rem;" onchange="this.form.submit()">
        </div>

        @if(request()->hasAny(['score', 'month', 'emotion']))
          <a href="{{ route('mood.history') }}" class="btn btn-sm btn-secondary" style="gap: 4px;">
            <i class="fa-solid fa-xmark"></i>
            <span>Limpiar filtros</span>
          </a>
        @endif
      </form>
    </div>
  </div>

  <!-- LOGS TIMELINE LIST -->
  <div>
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Registros detallados</h2>

    @if($logs->isEmpty())
      <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
        <i class="fa-regular fa-clipboard" style="font-size: 2.5rem; color: var(--text-light-gray);"></i>
        <h3 style="margin-top: 1rem; font-size: 1.2rem;">No hay registros con los filtros seleccionados</h3>
        <p style="color: var(--text-medium-gray); font-size: 0.88rem; margin-top: 0.35rem;">Realiza tu check-in diario en el dashboard para ver aquí tu evolución.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm" style="margin-top: 1.25rem;">Ir al Dashboard</a>
      </div>
    @else
      <div style="display: flex; flex-direction: column; gap: 1rem;">
        @foreach($logs as $log)
          @php
            $cardBorderColor = match($log->score) {
              5 => '#1E8449',
              4 => '#3D7A5F',
              3 => '#D4AC0D',
              2 => '#5B4A8A',
              1 => '#C0392B',
              default => '#3D7A5F'
            };
          @endphp
          <div class="card" style="border-left: 5px solid {{ $cardBorderColor }};">
            <div class="card-body" style="padding: 1.25rem 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div>
                  <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span class="mono-tag" style="color: var(--text-medium-gray);">{{ $log->formatted_date }}</span>
                    <span class="badge" style="background: rgba({{ hexdec(substr($cardBorderColor,1,2)) }}, {{ hexdec(substr($cardBorderColor,3,2)) }}, {{ hexdec(substr($cardBorderColor,5,2)) }}, 0.15); color: {{ $cardBorderColor }};">
                      {{ $log->score }} / 5 · {{ $log->emotion_label }}
                    </span>
                  </div>
                  <h3 style="font-size: 1.15rem; margin-top: 0.4rem; margin-bottom: 0.25rem; color: var(--text-near-black);">
                    {{ $log->primary_emotion }}
                  </h3>
                </div>

                <form action="{{ route('mood.destroy', $log) }}" method="POST" class="delete-mood-form">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-secondary" title="Eliminar registro" style="color: var(--clinical-red); padding: 0.3rem 0.6rem;">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>

              @if($log->journal_entry)
                <p style="font-size: 0.9rem; color: var(--text-near-black); line-height: 1.6; margin-top: 0.65rem; background: var(--bg-subtle); padding: 0.75rem 1rem; border-radius: var(--radius-sm);">
                  "{{ $log->journal_entry }}"
                </p>
              @endif

              @if($log->gratitude_note)
                <div style="margin-top: 0.65rem; font-size: 0.85rem; color: #8A7332; display: flex; align-items: center; gap: 6px;">
                  <i class="fa-solid fa-star" style="color: #C8B87A;"></i>
                  <span><strong>Gratitud:</strong> {{ $log->gratitude_note }}</span>
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      <div style="display: flex; justify-content: center; margin-top: 2rem;">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
