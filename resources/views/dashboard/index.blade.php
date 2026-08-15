@extends('layouts.app')

@section('title', 'Mi Espacio Seguro')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

  <!-- WELCOME & STREAK BANNER -->
  <div class="checkin-hero-card">
    <div class="hero-halo" style="top: 20%; right: -10%;"></div>
    <div class="checkin-hero-inner">
      <div style="display: flex; align-items: center; gap: 1.5rem;">
        <div class="tree-floating-wrapper">
          <svg class="ptree" viewBox="0 0 16 16" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#5a9060"/>
            <rect x="3" y="2" width="10" height="2" fill="#6B8F71"/>
            <rect x="2" y="4" width="12" height="2" fill="#7ab870"/>
            <rect x="3" y="6" width="10" height="2" fill="#6B8F71"/>
            <rect x="5" y="8" width="6" height="2" fill="#5a9060"/>
            <rect x="7" y="10" width="2" height="4" fill="#8B5e30"/>
            <rect x="4" y="1" width="1" height="1" fill="#e84040"/>
            <rect x="11" y="3" width="1" height="1" fill="#e84040"/>
            <rect x="9" y="7" width="1" height="1" fill="#e84040"/>
          </svg>
        </div>
        <div>
          <div class="mono-tag" style="color: var(--sage-300); margin-bottom: 0.35rem;">{{ $greeting }}</div>
          <h1 style="color: #ffffff; font-size: clamp(1.6rem, 3.5vw, 2.3rem); margin-bottom: 0.35rem;">
            Hola, {{ $user->name }} 👋
          </h1>
          <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem; font-style: italic;">
            @if($todayLog)
              "Hoy registraste sentirte {{ strtolower($todayLog->primary_emotion) }}. Gracias por escucharte."
            @else
              "¿Cómo late tu corazón hoy? Tómate un respiro y conecta contigo."
            @endif
          </p>
        </div>
      </div>

      <!-- Streak Badge -->
      <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: var(--radius-lg); padding: 1rem 1.5rem; text-align: center; flex-shrink: 0; min-width: 140px; backdrop-filter: blur(10px);">
        <div style="font-size: 2.2rem; line-height: 1; font-weight: 900; color: #ffd166; font-family: var(--font-display);">
          {{ $streak }}
        </div>
        <div class="mono-tag" style="color: rgba(255, 255, 255, 0.7); margin-top: 0.25rem;">
          {{ $streak === 1 ? 'Día de racha' : 'Días de racha' }}
        </div>
        <div style="font-size: 0.72rem; color: #ffd166; margin-top: 0.2rem;">✨ Constancia</div>
      </div>
    </div>
  </div>

  <!-- MAIN TWO-COLUMN GRID -->
  <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 1.75rem; align-items: start;">

    <!-- LEFT COLUMN: TODAY'S CHECK-IN & STEPPER -->
    <div>
      <div class="card" style="margin-bottom: 1.75rem;">
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
              <span class="mono-tag" style="color: var(--sage-600);">Registro Emocional</span>
              <h2 style="font-size: 1.45rem; margin-top: 0.2rem;">¿Cómo te sientes hoy?</h2>
            </div>
            @if($todayLog)
              <span class="badge badge-sage">Completado hoy ✓</span>
            @endif
          </div>

          <!-- CHECK-IN FORM / STEPPER -->
          <form id="moodCheckinForm" method="POST" action="{{ route('mood.store') }}">
            @csrf

            <!-- STEP 1: MOOD SCORE (1 TO 5) -->
            <div class="mood-selector-row">
              @php
                $scores = [
                  1 => ['label' => 'Muy difícil', 'emoji' => '🌧️', 'color' => '#c0392b'],
                  2 => ['label' => 'Difícil', 'emoji' => '☁️', 'color' => '#b86b4a'],
                  3 => ['label' => 'Equilibrio', 'emoji' => '🌱', 'color' => '#4a7fa5'],
                  4 => ['label' => 'Bien', 'emoji' => '🌤️', 'color' => '#7a6faa'],
                  5 => ['label' => 'Muy bien', 'emoji' => '✨', 'color' => '#4d7c5f'],
                ];
                $currentScore = old('score', $todayLog?->score ?? 3);
              @endphp

              @foreach($scores as $val => $info)
                <label class="mood-choice-btn {{ $currentScore == $val ? 'selected' : '' }}" data-score="{{ $val }}">
                  <input type="radio" name="score" value="{{ $val }}" {{ $currentScore == $val ? 'checked' : '' }} style="display: none;">
                  <span class="mood-emoji-icon">{{ $info['emoji'] }}</span>
                  <span class="mood-label-text">{{ $info['label'] }}</span>
                </label>
              @endforeach
            </div>

            <!-- STEP 2: PRIMARY EMOTION KEYWORD -->
            <div class="form-group" style="margin-top: 1.5rem;">
              <label class="form-label">Emoción predominante:</label>
              <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="emotionPills">
                @php
                  $emotions = ['Calma', 'Ansiedad', 'Esperanza', 'Tristeza', 'Gratitud', 'Estrés', 'Paz', 'Enojo', 'Cansancio', 'Motivación'];
                  $selectedEmotion = old('primary_emotion', $todayLog?->primary_emotion ?? 'Calma');
                @endphp
                @foreach($emotions as $emo)
                  <button type="button" class="btn btn-sm btn-secondary emotion-tag-btn {{ $selectedEmotion === $emo ? 'active-tag' : '' }}" data-val="{{ $emo }}" style="border-radius: var(--radius-full);">
                    {{ $emo }}
                  </button>
                @endforeach
              </div>
              <input type="hidden" name="primary_emotion" id="primaryEmotionInput" value="{{ $selectedEmotion }}">
            </div>

            <!-- STEP 3: REFLECTION JOURNAL ENTRY -->
            <div class="form-group" style="margin-top: 1.25rem;">
              <label for="journal_entry" class="form-label">Reflexión o notas del día (opcional):</label>
              <textarea 
                name="journal_entry" 
                id="journal_entry" 
                rows="3" 
                class="form-control" 
                placeholder="¿Qué ocurrió hoy? ¿Qué sensaciones corporales o pensamientos identificas?"
              >{{ old('journal_entry', $todayLog?->journal_entry) }}</textarea>
            </div>

            <!-- STEP 4: GRATITUDE (3 THINGS) -->
            <div class="form-group" style="margin-top: 1.25rem; background: var(--amber-50); border: 1px solid var(--amber-200); border-radius: var(--radius-md); padding: 1.1rem;">
              <label for="gratitude_note" class="form-label" style="color: var(--amber-700); display: flex; align-items: center; gap: 0.4rem;">
                <span>✨</span>
                <span>Un momento de gratitud hoy:</span>
              </label>
              <input 
                type="text" 
                name="gratitude_note" 
                id="gratitude_note" 
                class="form-control" 
                style="background: #ffffff;"
                placeholder="Nombra algo pequeño que hoy agradezcas (el café, una llamada, descansar...)"
                value="{{ old('gratitude_note', $todayLog?->gratitude_note) }}"
              >
            </div>

            <!-- Energy & Sleep Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.25rem;">
              <div class="form-group">
                <label for="energy_level" class="form-label">Nivel de energía (1 al 5):</label>
                <select name="energy_level" id="energy_level" class="form-control">
                  <option value="1" {{ old('energy_level', $todayLog?->energy_level) == 1 ? 'selected' : '' }}>⚡ 1 - Muy baja</option>
                  <option value="2" {{ old('energy_level', $todayLog?->energy_level) == 2 ? 'selected' : '' }}>⚡ 2 - Baja</option>
                  <option value="3" {{ old('energy_level', $todayLog?->energy_level ?? 3) == 3 ? 'selected' : '' }}>⚡ 3 - Moderada</option>
                  <option value="4" {{ old('energy_level', $todayLog?->energy_level) == 4 ? 'selected' : '' }}>⚡ 4 - Buena</option>
                  <option value="5" {{ old('energy_level', $todayLog?->energy_level) == 5 ? 'selected' : '' }}>⚡ 5 - Plena</option>
                </select>
              </div>

              <div class="form-group">
                <label for="sleep_hours" class="form-label">Horas de sueño:</label>
                <input 
                  type="number" 
                  name="sleep_hours" 
                  id="sleep_hours" 
                  min="0" 
                  max="24" 
                  class="form-control" 
                  placeholder="Ej. 7 u 8 horas"
                  value="{{ old('sleep_hours', $todayLog?->sleep_hours) }}"
                >
              </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
              <button type="submit" class="btn btn-primary" id="saveMoodBtn">
                <span>{{ $todayLog ? 'Actualizar registro de hoy' : 'Guardar registro del día' }}</span>
                <span>🌱</span>
              </button>
              <a href="{{ route('mood.history') }}" style="font-size: 0.85rem; color: var(--sage-600); text-decoration: underline;">
                Ver historial completo →
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- RECENT JOURNAL REFLECTIONS -->
      @if($recentLogs->isNotEmpty())
        <div class="card">
          <div class="card-body">
            <span class="mono-tag" style="color: var(--lav-600);">Tu bitácora reciente</span>
            <h3 style="font-size: 1.25rem; margin-top: 0.2rem; margin-bottom: 1rem;">Reflexiones pasadas</h3>
            
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
              @foreach($recentLogs as $log)
                <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 1rem; border-left: 4px solid {{ $log->score_color }};">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <div style="font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.4rem;">
                      <span>{{ $log->emoji }}</span>
                      <span>{{ $log->primary_emotion }}</span>
                      <span style="font-size: 0.75rem; color: var(--ink-400); font-weight: 400;">({{ $log->emotion_label }})</span>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--ink-600);">
                      {{ $log->formatted_date }}
                    </div>
                  </div>
                  @if($log->journal_entry)
                    <p style="font-size: 0.86rem; color: var(--ink-700); line-height: 1.6;">
                      {{ $log->journal_entry }}
                    </p>
                  @endif
                  @if($log->gratitude_note)
                    <div style="font-size: 0.8rem; color: var(--amber-700); margin-top: 0.35rem; font-style: italic;">
                      ✨ Gratitud: {{ $log->gratitude_note }}
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </div>

    <!-- RIGHT COLUMN: WEEKLY PROGRESS, SAFETY PLAN & QUICK TOOLS -->
    <div>
      <!-- WEEKLY PROGRESS CARD -->
      <div class="card" style="margin-bottom: 1.75rem;">
        <div class="card-body">
          <span class="mono-tag" style="color: var(--sage-600);">Esta Semana</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.2rem; margin-bottom: 1.25rem;">Ritmo y Consistencia</h3>

          <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.45rem; text-align: center;">
            @foreach($weeklyData as $day)
              <div style="background: {{ $day['is_today'] ? 'var(--sage-100)' : ($day['has_log'] ? 'var(--sage-50)' : 'var(--bg-subtle)') }}; border: 1.5px solid {{ $day['is_today'] ? 'var(--sage-500)' : ($day['has_log'] ? 'var(--sage-200)' : 'transparent') }}; border-radius: var(--radius-md); padding: 0.65rem 0.2rem;">
                <div style="font-family: var(--font-mono); font-size: 0.68rem; color: var(--ink-400); text-transform: uppercase;">
                  {{ $day['day_name'] }}
                </div>
                <div style="font-size: 1.15rem; margin-top: 0.3rem;">
                  @if($day['has_log'])
                    {{ $day['emoji'] }}
                  @else
                    <span style="color: var(--ink-300); font-size: 0.85rem;">○</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>

          <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--ink-100); display: flex; justify-content: space-between; font-size: 0.85rem;">
            <span style="color: var(--ink-600);">Total de registros: <strong>{{ $totalLogs }}</strong></span>
            @if($averageScore)
              <span style="color: var(--sage-700);">Promedio: <strong>{{ $averageScore }} / 5.0</strong></span>
            @endif
          </div>
        </div>
      </div>

      <!-- SAFETY PLAN QUICK ACCESS -->
      <div class="card" style="margin-bottom: 1.75rem; background: linear-gradient(145deg, #ffffff, var(--sky-50));">
        <div class="card-body">
          <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.5rem;">
            <span style="font-size: 1.4rem;">🛡️</span>
            <div>
              <span class="mono-tag" style="color: var(--sky-600);">Prevención y Cuidado</span>
              <h3 style="font-size: 1.2rem;">Plan de Seguridad Digital</h3>
            </div>
          </div>
          <p style="font-size: 0.85rem; color: var(--ink-600); margin-bottom: 1rem; line-height: 1.6;">
            Tu protocolo de calma con señales tempranas de alerta, estrategias internas y contactos de auxilio.
          </p>
          <a href="{{ route('safety-plan.show') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
            {{ $hasSafetyPlan ? 'Ver y editar mi plan →' : 'Configurar mi plan de seguridad →' }}
          </a>
        </div>
      </div>

      <!-- INTERACTIVE TOOLS SHORTCUTS -->
      <div class="card" style="margin-bottom: 1.75rem;">
        <div class="card-body">
          <span class="mono-tag" style="color: var(--sage-600);">Pausas Activas</span>
          <h3 style="font-size: 1.2rem; margin-top: 0.2rem; margin-bottom: 1rem;">Herramientas para Ahora</h3>

          <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('tools.respiracion') }}" class="card" style="padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.85rem; text-decoration: none; border-color: var(--sage-200);">
              <span style="font-size: 1.6rem;">🌸</span>
              <div style="flex: 1;">
                <div style="font-weight: 700; font-size: 0.92rem; color: var(--ink-900);">Respiración 4-7-8</div>
                <div style="font-size: 0.78rem; color: var(--ink-600);">Desactiva la alerta biológica en 2 min</div>
              </div>
              <span style="color: var(--sage-600); font-weight: 700;">→</span>
            </a>

            <a href="{{ route('tools.grounding') }}" class="card" style="padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.85rem; text-decoration: none; border-color: var(--sky-200);">
              <span style="font-size: 1.6rem;">🌊</span>
              <div style="flex: 1;">
                <div style="font-weight: 700; font-size: 0.92rem; color: var(--ink-900);">Grounding 5-4-3-2-1</div>
                <div style="font-size: 0.78rem; color: var(--ink-600);">Ancla tus 5 sentidos al presente</div>
              </div>
              <span style="color: var(--sky-600); font-weight: 700;">→</span>
            </a>

            <a href="{{ route('tools.stop') }}" class="card" style="padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.85rem; text-decoration: none; border-color: var(--lav-200);">
              <span style="font-size: 1.6rem;">🛑</span>
              <div style="flex: 1;">
                <div style="font-weight: 700; font-size: 0.92rem; color: var(--ink-900);">Técnica STOP (DBT)</div>
                <div style="font-size: 0.78rem; color: var(--ink-600);">Para momentos de desbordamiento</div>
              </div>
              <span style="color: var(--lav-600); font-weight: 700;">→</span>
            </a>
          </div>
        </div>
      </div>

      <!-- CRISIS HELPLINE BOX -->
      <div style="background: var(--dark-900); color: #ffffff; border-radius: var(--radius-lg); padding: 1.25rem; text-align: center;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem;">
          <span class="nav-crisis-dot"></span>
          <span class="mono-tag" style="color: #ff9999;">Línea de Crisis Directa</span>
        </div>
        <p style="font-size: 0.82rem; color: rgba(255,255,255,0.6); margin-bottom: 0.85rem;">
          Si estás en crisis aguda, no estás solo. Llama gratis 24h.
        </p>
        <a href="tel:8002900024" class="btn btn-crisis btn-sm" style="width: 100%; justify-content: center; font-size: 0.9rem;">
          Llamar al 800 290 0024
        </a>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Emotion tag selector in checkin form
  document.querySelectorAll('.emotion-tag-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.emotion-tag-btn').forEach(b => {
        b.style.background = 'var(--bg-surface)';
        b.style.color = 'var(--ink-900)';
        b.style.borderColor = 'var(--ink-100)';
      });
      btn.style.background = 'var(--sage-600)';
      btn.style.color = '#ffffff';
      btn.style.borderColor = 'var(--sage-600)';
      document.getElementById('primaryEmotionInput').value = btn.getAttribute('data-val');
    });
  });

  // Mood Choice Radio Buttons Visual Selection
  document.querySelectorAll('.mood-choice-btn').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.mood-choice-btn').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      const radio = card.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      window.zenAudio.playChime(523.25); // C5 sound
    });
  });
</script>
@endpush
