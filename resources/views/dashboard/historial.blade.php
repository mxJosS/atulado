@extends('layouts.app')

@section('title', 'Historial y Analíticas Emocionales')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sage-600);">Evolución Personal</span>
      <h1 style="font-size: 2rem; margin-top: 0.2rem;">Historial y Analíticas</h1>
      <p style="color: var(--ink-600); font-size: 0.95rem;">Observa tus patrones emocionales, tus anclas de gratitud y tu constancia.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
      ← Volver al Dashboard
    </a>
  </div>

  <!-- STATS OVERVIEW CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card">
      <div class="card-body" style="text-align: center;">
        <span class="mono-tag" style="color: var(--sage-600);">Total de Check-ins</span>
        <div style="font-size: 2.5rem; font-family: var(--font-display); font-weight: 900; color: var(--sage-800); margin-top: 0.25rem;">
          {{ $totalLogs }}
        </div>
        <div style="font-size: 0.8rem; color: var(--ink-400);">Días registrados</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body" style="text-align: center;">
        <span class="mono-tag" style="color: var(--lav-600);">Promedio General</span>
        <div style="font-size: 2.5rem; font-family: var(--font-display); font-weight: 900; color: var(--lav-700); margin-top: 0.25rem;">
          {{ $averageScore }} <span style="font-size: 1.2rem; color: var(--ink-400);">/ 5.0</span>
        </div>
        <div style="font-size: 0.8rem; color: var(--ink-400);">Índice de bienestar</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body" style="text-align: center;">
        <span class="mono-tag" style="color: var(--amber-600);">Emoción más Frecuente</span>
        @php
          $topEmotion = $emotionDistribution->sortDesc()->keys()->first() ?? 'Sin registros';
        @endphp
        <div style="font-size: 1.6rem; font-family: var(--font-display); font-weight: 700; color: var(--amber-700); margin-top: 0.6rem;">
          {{ $topEmotion }}
        </div>
        <div style="font-size: 0.8rem; color: var(--ink-400);">Predominante en tu camino</div>
      </div>
    </div>
  </div>

  <!-- 30-DAY INTERACTIVE TIMELINE CHART (SVG BASED) -->
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
          <span class="mono-tag" style="color: var(--sage-600);">Tendencia</span>
          <h2 style="font-size: 1.35rem; margin-top: 0.2rem;">Últimos 30 días</h2>
        </div>
        <div style="display: flex; gap: 0.75rem; font-size: 0.75rem; font-family: var(--font-mono);">
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #4d7c5f;"></span> Alto (4-5)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #4a7fa5;"></span> Medio (3)</span>
          <span style="display: flex; align-items: center; gap: 0.3rem;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #b86b4a;"></span> Bajo (1-2)</span>
        </div>
      </div>

      <!-- SVG Line & Bar Chart -->
      <div style="width: 100%; overflow-x: auto;">
        <div style="min-width: 600px; height: 180px; display: flex; align-items: flex-end; gap: 8px; padding-bottom: 2rem; border-bottom: 1px solid var(--ink-100); position: relative;">
          @foreach($last30Days as $pt)
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; position: relative;" title="{{ $pt['date'] }}: {{ $pt['emotion'] ?? 'Sin registro' }} ({{ $pt['score'] ?? '-' }}/5)">
              @if($pt['score'])
                @php
                  $hPercent = ($pt['score'] / 5) * 100;
                  $barColor = match($pt['score']) {
                    1 => '#c0392b',
                    2 => '#b86b4a',
                    3 => '#4a7fa5',
                    4 => '#7a6faa',
                    5 => '#4d7c5f',
                    default => '#4d7c5f'
                  };
                @endphp
                <div style="width: 100%; max-width: 18px; height: {{ $hPercent }}%; background: {{ $barColor }}; border-radius: 4px 4px 0 0; transition: height 0.4s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.1);"></div>
              @else
                <div style="width: 100%; max-width: 18px; height: 4px; background: var(--ink-100); border-radius: 2px;"></div>
              @endif
              <div style="position: absolute; bottom: -24px; font-family: var(--font-mono); font-size: 0.65rem; color: var(--ink-400); white-space: nowrap; transform: rotate(-45deg); transform-origin: left top;">
                {{ $loop->iteration % 4 === 1 ? $pt['date'] : '' }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <!-- FILTER TOOLBAR -->
  <div class="card" style="margin-bottom: 2rem; background: var(--bg-surface);">
    <div class="card-body" style="padding: 1.25rem;">
      <form method="GET" action="{{ route('mood.history') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 180px;">
          <label for="score" class="form-label" style="margin: 0; white-space: nowrap; font-size: 0.82rem;">Filtrar nivel:</label>
          <select name="score" id="score" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
            <option value="">Todos los niveles</option>
            <option value="5" {{ request('score') == 5 ? 'selected' : '' }}>5 - Muy bien ✨</option>
            <option value="4" {{ request('score') == 4 ? 'selected' : '' }}>4 - Bien 🌤️</option>
            <option value="3" {{ request('score') == 3 ? 'selected' : '' }}>3 - En equilibrio 🌱</option>
            <option value="2" {{ request('score') == 2 ? 'selected' : '' }}>2 - Difícil ☁️</option>
            <option value="1" {{ request('score') == 1 ? 'selected' : '' }}>1 - Muy difícil 🌧️</option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 180px;">
          <label for="month" class="form-label" style="margin: 0; white-space: nowrap; font-size: 0.82rem;">Mes:</label>
          <input type="month" name="month" id="month" value="{{ request('month') }}" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
        </div>

        @if(request()->hasAny(['score', 'month', 'emotion']))
          <a href="{{ route('mood.history') }}" class="btn btn-sm btn-secondary">
            Limpiar filtros ✕
          </a>
        @endif
      </form>
    </div>
  </div>

  <!-- LOGS TIMELINE LIST -->
  <div>
    <h2 style="font-size: 1.35rem; margin-bottom: 1.25rem;">Registros detallados</h2>

    @if($logs->isEmpty())
      <div class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
        <span style="font-size: 3rem;">🌱</span>
        <h3 style="margin-top: 0.85rem;">No se encontraron registros</h3>
        <p style="color: var(--ink-600); max-width: 400px; margin: 0.5rem auto 1.5rem;">
          No hay registros con los filtros seleccionados.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Hacer un check-in ahora</a>
      </div>
    @else
      <div style="display: flex; flex-direction: column; gap: 1rem;">
        @foreach($logs as $log)
          <div class="card" style="border-left: 5px solid {{ $log->score_color }};">
            <div class="card-body">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                  <span style="font-size: 1.8rem; line-height: 1;">{{ $log->emoji }}</span>
                  <div>
                    <div style="font-weight: 700; font-size: 1.05rem; color: var(--ink-900);">
                      {{ $log->primary_emotion }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--ink-600); font-family: var(--font-mono);">
                      {{ $log->emotion_label }} · Nivel {{ $log->score }}/5
                    </div>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem;">
                  <div style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--ink-600); background: var(--bg-subtle); padding: 0.3rem 0.75rem; border-radius: var(--radius-sm);">
                    {{ $log->formatted_date }}
                  </div>

                  <form action="{{ route('mood.destroy', $log) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: var(--ink-300); cursor: pointer; padding: 0.25rem;" title="Eliminar registro">
                      🗑️
                    </button>
                  </form>
                </div>
              </div>

              @if($log->journal_entry)
                <p style="font-size: 0.92rem; color: var(--ink-800); line-height: 1.7; margin-bottom: 0.75rem;">
                  {{ $log->journal_entry }}
                </p>
              @endif

              @if($log->gratitude_note)
                <div style="background: var(--amber-50); border: 1px solid var(--amber-200); border-radius: var(--radius-md); padding: 0.65rem 0.95rem; font-size: 0.85rem; color: var(--amber-800); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                  <span>✨</span>
                  <span><strong>Gratitud:</strong> {{ $log->gratitude_note }}</span>
                </div>
              @endif

              <div style="display: flex; gap: 1rem; font-size: 0.78rem; color: var(--ink-400); margin-top: 0.5rem; font-family: var(--font-mono);">
                @if($log->energy_level)
                  <span>⚡ Energía: {{ $log->energy_level }}/5</span>
                @endif
                @if($log->sleep_hours)
                  <span>🌙 Sueño: {{ $log->sleep_hours }} hrs</span>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div style="margin-top: 2rem;">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
