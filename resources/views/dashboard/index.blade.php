@extends('layouts.app')

@section('title', 'Mi Espacio Seguro')

@section('content')
<div style="max-width: 1080px; margin: 0 auto;">

  <!-- ════ CLEAN MODERN GREETING BANNER ════ -->
  <div style="background: linear-gradient(135deg, #0D1410 0%, #1E2A22 100%) !important; color: #FFFFFF !important; border-radius: 24px; padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.75rem; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: var(--shadow-sm);">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(90, 181, 110, 0.2); border: 2px solid #A8E6C0; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700; color: #FFFFFF;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
      </div>
      <div>
        <div class="mono-tag" style="color: #A8E6C0; font-size: 0.68rem;">{{ $greeting }}</div>
        <h1 style="color: #FFFFFF !important; font-size: clamp(1.35rem, 2.5vw, 1.85rem); margin: 0; line-height: 1.2;">
          Hola, {{ $user->name }}
        </h1>
        <p style="color: #C8DDD1 !important; font-size: 0.86rem; margin-top: 0.2rem; font-style: italic;">
          @if($todayLog)
            "Hoy registraste sentirte {{ strtolower($todayLog->primary_emotion) }}. Gracias por escucharte."
          @else
            "¿Cómo late tu corazón hoy? Tómate un momento para conectar contigo."
          @endif
        </p>
      </div>
    </div>

    <!-- Quick Tool Button -->
    <a href="{{ route('tools.respiracion') }}" class="btn btn-sm btn-outline-white" style="gap: 8px;">
      <i class="fa-solid fa-lungs" style="color: #A8E6C0;"></i>
      <span>Respira 1 min</span>
    </a>
  </div>

  <!-- ════ MAIN 2-COLUMN GRID ════ -->
  <div class="dash-main-grid">

    <!-- LEFT COLUMN: REGISTRO EMOCIONAL (ITEM 4 OBLIGATORIO) -->
    <div>
      <div class="card" style="border-top: 5px solid #2E5D4B;">
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <div>
              <span class="mono-tag" style="color: #2E5D4B;">— REGISTRO EMOCIONAL</span>
              <h2 style="font-size: 1.4rem; margin-top: 0.2rem; color: #1A2620;">¿Cómo estuvo tu día hoy?</h2>
              <p style="font-size: 0.88rem; color: #556860; margin-top: 0.15rem;">
                Escribe cómo te sientes y elige lo que más se acerca.
              </p>
            </div>
            @if($todayLog)
              <span class="badge badge-sage">
                <i class="fa-solid fa-check"></i> Registrado hoy
              </span>
            @endif
          </div>

          <form id="moodCheckinForm" method="POST" action="{{ route('mood.store') }}">
            @csrf

            <!-- 1. TEXTAREA: ESCRIBE CÓMO TE SIENTES... (OPCIONAL) -->
            <div class="form-group" style="margin-top: 1.25rem;">
              <textarea 
                name="journal_entry" 
                id="journal_entry" 
                rows="3" 
                class="form-control" 
                placeholder="Escribe cómo te sientes... (opcional)"
                style="resize: vertical; font-size: 0.92rem; line-height: 1.6; background: #FFFFFF; color: #1A2620;"
              >{{ old('journal_entry', $todayLog?->journal_entry) }}</textarea>
            </div>

            <!-- 2. 5 CARITAS EMOCIONALES OBLIGATORIAS (ITEM 4) -->
            <div class="form-group" style="margin-top: 1rem;">
              <label class="form-label" style="font-size: 0.84rem; color: #1A2620; font-weight: 600;">
                Selecciona tu estado emocional general:
              </label>

              <div class="smilies-5-selector" id="smilies5Grid">
                @php
                  $smiliesList = [
                    ['score' => 5, 'label' => 'Excelente', 'icon' => 'fa-regular fa-face-laugh-beam', 'color' => '#1E8449', 'def_emo' => 'Excelente'],
                    ['score' => 4, 'label' => 'Bien', 'icon' => 'fa-regular fa-face-smile', 'color' => '#3D7A5F', 'def_emo' => 'Bien'],
                    ['score' => 3, 'label' => 'Regular', 'icon' => 'fa-regular fa-face-meh', 'color' => '#D4AC0D', 'def_emo' => 'Regular'],
                    ['score' => 2, 'label' => 'Mal', 'icon' => 'fa-regular fa-face-frown', 'color' => '#5B4A8A', 'def_emo' => 'Mal'],
                    ['score' => 1, 'label' => 'Terrible', 'icon' => 'fa-regular fa-face-angry', 'color' => '#C0392B', 'def_emo' => 'Terrible'],
                  ];
                  $currentScore = old('score', $todayLog?->score ?? 4);
                @endphp

                @foreach($smiliesList as $s)
                  <label class="smily-card-option {{ $currentScore == $s['score'] ? 'selected' : '' }}" data-score="{{ $s['score'] }}" data-emo="{{ $s['def_emo'] }}">
                    <input type="radio" name="score" value="{{ $s['score'] }}" {{ $currentScore == $s['score'] ? 'checked' : '' }} style="display: none;">
                    <i class="{{ $s['icon'] }}" style="color: {{ $s['color'] }};"></i>
                    <span style="color: {{ $s['color'] }};">{{ $s['label'] }}</span>
                  </label>
                @endforeach
              </div>
            </div>

            <!-- 3. EMOTION TAG PILLS (OPTIONAL QUICK SELECTION) -->
            <div class="form-group" style="margin-top: 1rem;">
              <label class="form-label" style="font-size: 0.82rem; color: #556860; font-weight: 600; margin-bottom: 0.5rem; display: block;">Matiz o emoción específica:</label>
              <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="emotionPills">
                @php
                  $emotions = ['Tranquilo', 'Ansioso', 'Esperanza', 'Triste', 'Agradecido', 'Enojado', 'Agotado', 'En Paz', 'Confundido'];
                  $selectedEmotion = old('primary_emotion', $todayLog?->primary_emotion ?? 'Tranquilo');
                @endphp
                @foreach($emotions as $emo)
                  <button type="button" class="emotion-tag-btn {{ $selectedEmotion === $emo ? 'active-tag' : '' }}" data-val="{{ $emo }}">
                    {{ $emo }}
                  </button>
                @endforeach
              </div>
              <input type="hidden" name="primary_emotion" id="primaryEmotionInput" value="{{ $selectedEmotion }}">
            </div>

            <!-- 4. GRATITUDE NOTE (ANCLAJE) -->
            <div class="form-group" style="margin-top: 1rem; background: #FBF9F2; border: 1px solid rgba(200, 184, 122, 0.4); border-radius: 16px; padding: 0.85rem 1rem;">
              <label for="gratitude_note" class="form-label" style="color: #8A7332; font-size: 0.82rem; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem; font-weight: 600;">
                <i class="fa-solid fa-star" style="color: #C8B87A;"></i>
                <span>Gratitud: Algo bueno que ocurrió hoy</span>
              </label>
              <input 
                type="text" 
                name="gratitude_note" 
                id="gratitude_note" 
                value="{{ old('gratitude_note', $todayLog?->gratitude_note) }}" 
                class="form-control" 
                placeholder="Ej. Una comida rica, el cielo despejado, un abrazo..."
                style="background: #FFFFFF; font-size: 0.86rem; color: #1A2620;"
              >
            </div>

            <!-- 5. SUBMIT BUTTON -->
            <div style="margin-top: 1.5rem;">
              <button type="submit" id="saveCheckinBtn" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 8px; font-size: 0.95rem; padding: 0.85rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span id="saveCheckinBtnText">{{ $todayLog ? 'Actualizar registro de hoy' : 'Guardar registro emocional' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: TU RACHA (ITEM 4 OBLIGATORIO) & TOOLS -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

      <!-- ════ WIDGET TU RACHA (ITEM 4 OBLIGATORIO) ════ -->
      <div class="streak-racha-box">
        <span class="mono-tag" style="color: #8A7332; display: block; margin-bottom: 0.4rem;">— TU RACHA</span>
        
        <div style="display: flex; align-items: baseline; gap: 0.6rem;">
          <div class="streak-big-count">{{ $streak }}</div>
          <div style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #8A7332; text-transform: uppercase; letter-spacing: 0.08em;">
            {{ $streak === 1 ? 'DÍA SEGUIDO' : 'DÍAS SEGUIDOS' }}
          </div>
        </div>

        <!-- 7 DAY BUBBLES L M X J V S D -->
        <div class="streak-day-bubbles">
          @php
            $dayLetters = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
          @endphp
          @foreach($weeklyData as $idx => $day)
            <div class="day-circle-bubble {{ $day['has_log'] ? 'completed' : '' }} {{ $day['is_today'] ? 'today-active' : '' }}" title="{{ $day['day_name'] }} ({{ $day['date'] }}): {{ $day['has_log'] ? 'Registrado' : 'Pendiente' }}">
              {{ $dayLetters[$idx] ?? 'D' }}
            </div>
          @endforeach
        </div>

        <!-- MOTIVATIONAL TREE MESSAGE -->
        <p style="font-size: 0.92rem; color: #556860; line-height: 1.6; margin-bottom: 1rem; font-weight: 500;">
          ¡Sigue así! Tu árbol crece con cada día que te cuidas.
        </p>

        <!-- Dynamic Pixel Tree Preview -->
        <div style="display: flex; align-items: center; gap: 0.85rem; background: rgba(255, 255, 255, 0.7); border-radius: 16px; padding: 0.75rem 1rem; border: 1px solid rgba(200, 184, 122, 0.3);">
          <svg class="ptree" viewBox="0 0 16 16" width="34" height="34" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
            <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
            <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
            <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
            <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
            <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
            <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
            <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
            <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
          </svg>
          <div style="font-size: 0.82rem; color: #1A2620;">
            <strong>Árbol de bienestar:</strong> <span style="color: #2E5D4B;">Nivel {{ min(5, max(1, intdiv($streak, 3) + 1)) }}</span>
            <div style="font-size: 0.74rem; color: #556860;">Raíces fuertes y follaje activo</div>
          </div>
        </div>
      </div>

      <!-- SAFETY PLAN QUICK ACCESS -->
      <div class="card" style="background: #D4EDE2; border-color: #5AB56E;">
        <div class="card-body" style="padding: 1.35rem;">
          <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <i class="fa-solid fa-shield-heart" style="color: #2E5D4B; font-size: 1.5rem; margin-top: 2px;"></i>
            <div style="flex: 1;">
              <h4 style="font-size: 1rem; margin-bottom: 0.2rem; color: #2E5D4B;">Plan de Seguridad Digital</h4>
              <p style="font-size: 0.84rem; color: #1A2620; line-height: 1.45; margin-bottom: 0.85rem;">
                Tu protocolo de calma con contactos de auxilio y estrategias inmediatas.
              </p>
              <a href="{{ route('safety-plan.show') }}" class="btn btn-sm btn-secondary" style="font-size: 0.8rem; background: #ffffff;">
                Ver y editar mi plan →
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- QUICK TOOLS GRID -->
      <div class="card">
        <div class="card-body" style="padding: 1.35rem;">
          <h4 style="font-size: 1rem; margin-bottom: 0.85rem; color: #1A2620;">Herramientas Inmediatas</h4>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
            <a href="{{ route('tools.respiracion') }}" class="btn btn-sm btn-secondary" style="justify-content: flex-start; gap: 6px; font-size: 0.8rem;">
              <i class="fa-solid fa-lungs" style="color: #2E5D4B;"></i>
              <span>Respira 4-7-8</span>
            </a>
            <a href="{{ route('tools.grounding') }}" class="btn btn-sm btn-secondary" style="justify-content: flex-start; gap: 6px; font-size: 0.8rem;">
              <i class="fa-solid fa-hand-holding-heart" style="color: #5B4A8A;"></i>
              <span>Grounding 5-4-3-2-1</span>
            </a>
            <a href="{{ route('tools.stop') }}" class="btn btn-sm btn-secondary" style="justify-content: flex-start; gap: 6px; font-size: 0.8rem;">
              <i class="fa-solid fa-circle-pause" style="color: #C0392B;"></i>
              <span>Técnica STOP</span>
            </a>
            <a href="{{ route('recursos.index') }}" class="btn btn-sm btn-secondary" style="justify-content: flex-start; gap: 6px; font-size: 0.8rem;">
              <i class="fa-solid fa-book-bookmark" style="color: #8A7332;"></i>
              <span>Biblioteca</span>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
