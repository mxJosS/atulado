@extends('layouts.app')

@section('title', 'Mi Espacio Seguro')

@section('content')
<div style="max-width: 1080px; margin: 0 auto;">

  <!-- ════ CLEAN MODERN GREETING BANNER ════ -->
  <div style="background: linear-gradient(135deg, #0D1410 0%, #1E2A22 100%) !important; color: #FFFFFF !important; border-radius: 24px; padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.75rem; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: var(--shadow-sm);">
    <div style="display: flex; align-items: center; gap: 1.15rem; flex: 1; min-width: 260px;">
      <div style="width: 52px; height: 52px; min-width: 52px; min-height: 52px; max-width: 52px; max-height: 52px; border-radius: 50%; background: linear-gradient(135deg, rgba(90, 181, 110, 0.35), rgba(46, 93, 75, 0.5)); border: 2.5px solid #A8E6C0; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; font-weight: 700; color: #FFFFFF; flex-shrink: 0; aspect-ratio: 1 / 1; overflow: hidden; box-shadow: 0 0 16px rgba(168, 230, 192, 0.25);">
        @if($user->avatar_url)
          <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        @else
          <span style="line-height: 1;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        @endif
      </div>
      <div style="flex: 1; min-width: 0;">
        <div class="mono-tag" style="color: #A8E6C0; font-size: 0.68rem; letter-spacing: 0.08em;">{{ $greeting }}</div>
        <h1 style="color: #FFFFFF !important; font-size: clamp(1.35rem, 2.5vw, 1.85rem); margin: 0; line-height: 1.2; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
          <span>Hola, {{ $user->name }}</span>
          @if($user->isProfessional())
            <x-verified-badge size="20" />
          @endif
        </h1>
        <p style="color: #C8DDD1 !important; font-size: 0.86rem; margin-top: 0.25rem; font-style: italic; margin-bottom: 0;">
          @if($todayLog)
            "Hoy registraste sentirte {{ strtolower($todayLog->primary_emotion) }}. Gracias por escucharte."
          @else
            "¿Cómo late tu corazón hoy? Tómate un momento para conectar contigo."
          @endif
        </p>
      </div>
    </div>

    <!-- Quick Tool Button -->
    <a href="{{ route('tools.respiracion') }}" class="btn btn-sm btn-outline-white" style="gap: 8px; border-radius: 12px; padding: 0.5rem 1rem;">
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

              <!-- ════ SENSITIVE / CRISIS EMPATHIC FILTER ALERT BANNER ════ -->
              <div id="crisisEmpathicAlert" class="crisis-empathic-alert">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                  <i class="fa-solid fa-hand-holding-heart" style="color: #FFA59C; font-size: 1.5rem; margin-top: 2px;"></i>
                  <div style="flex: 1;">
                    <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem; color: #FFA59C;">
                      Te escuchamos y nos importa tu bienestar
                    </div>
                    <p style="font-size: 0.84rem; color: #FFFFFF; line-height: 1.5; margin-bottom: 0.85rem;">
                      Detectamos que puedes estar experimentando un dolor o agobio intenso. Sentir dolor es abrumador, pero no estás solo(a) y hay alternativas seguras para acompañarte.
                    </p>
                    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                      <a href="tel:8002900024" class="btn btn-sm btn-crisis" style="gap: 6px; font-size: 0.8rem;">
                        <i class="fa-solid fa-phone"></i>
                        <span>Llamar gratis a Línea de la Vida (800 290 0024)</span>
                      </a>
                      <a href="{{ route('safety-plan.show') }}" class="btn btn-sm btn-secondary" style="font-size: 0.8rem; background: rgba(255,255,255,0.15); color: #FFFFFF; border-color: rgba(255,255,255,0.3);">
                        <i class="fa-solid fa-shield-heart"></i>
                        <span>Abrir mi Plan de Seguridad</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
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
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
          <span class="mono-tag" style="color: #944000; font-weight: 700; background: rgba(230, 126, 34, 0.12); padding: 0.25rem 0.65rem; border-radius: 6px; font-size: 0.72rem; letter-spacing: 0.06em;">
            <i class="fa-solid fa-fire" style="color: #E67E22; margin-right: 4px;"></i> TU RACHA DIARIA
          </span>
          @if($streak > 0)
            <span class="badge" style="background: rgba(46, 93, 75, 0.12); color: #1E4A25; font-size: 0.72rem; font-weight: 700; border-radius: 9999px; padding: 0.2rem 0.55rem;">
              <i class="fa-solid fa-bolt" style="color: #E67E22;"></i> Activa
            </span>
          @else
            <span class="badge" style="background: rgba(140, 120, 80, 0.1); color: #7A693D; font-size: 0.72rem; font-weight: 600; border-radius: 9999px; padding: 0.2rem 0.55rem;">
              Comienza hoy
            </span>
          @endif
        </div>
        
        <div style="display: flex; align-items: baseline; gap: 0.6rem; margin-top: 0.4rem;">
          <div class="streak-big-count">
            <span>{{ $streak }}</span>
          </div>
          <div style="font-family: var(--font-mono); font-size: 0.92rem; font-weight: 700; color: #944000; text-transform: uppercase; letter-spacing: 0.06em;">
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
              @if($day['has_log'])
                <i class="fa-solid fa-check" style="font-size: 0.75rem;"></i>
              @else
                {{ $dayLetters[$idx] ?? 'D' }}
              @endif
            </div>
          @endforeach
        </div>

        <!-- MOTIVATIONAL TREE MESSAGE -->
        <p style="font-size: 0.88rem; color: #556860; line-height: 1.5; margin-bottom: 1rem; font-weight: 500;">
          @if($streak >= 7)
            ¡Increíble consistencia! Tu hábito de autocuidado está en su punto más fuerte.
          @elseif($streak >= 3)
            ¡Gran progreso! Cada día de registro fortalece tu bienestar mental y tu árbol.
          @elseif($streak > 0)
            ¡Buen inicio! Vuelve mañana para seguir haciendo crecer tu racha y tu árbol.
          @else
            Completa tu registro diario para encender tu racha y hacer crecer tu árbol.
          @endif
        </p>

        <!-- Dynamic Pixel Tree Preview with JUGAR BUTTON (ITEM 5) -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.85rem; background: rgba(255, 255, 255, 0.85); border-radius: 16px; padding: 0.85rem 1.1rem; border: 1.5px solid rgba(200, 184, 122, 0.35); box-shadow: 0 2px 8px rgba(0,0,0,0.03); flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <svg class="ptree" viewBox="0 0 16 16" width="34" height="34" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 2px 4px rgba(45, 107, 58, 0.2));">
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
              <strong>Árbol de bienestar:</strong> <span style="color: #2E5D4B; font-weight: 700;">Nivel {{ min(5, max(1, intdiv($streak, 3) + 1)) }}</span>
              <div style="font-size: 0.74rem; color: #556860;">Raíces fuertes y follaje activo</div>
            </div>
          </div>

          <!-- BOTÓN JUGAR (ITEM 5) -->
          <button type="button" onclick="openTreeGame()" class="btn btn-sm btn-primary" style="padding: 0.45rem 1rem; font-size: 0.82rem; border-radius: 10px; gap: 6px; background: #2E5D4B !important; color: #FFFFFF !important; box-shadow: 0 4px 12px rgba(46, 93, 75, 0.35);">
            <i class="fa-solid fa-gamepad"></i>
            <span>Jugar</span>
          </button>
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

<!-- ════ MODALES DEL MOTOR CLÍNICO (WHO-5, MDI, ASQ Y CONTENCIÓN) ════ -->
@include('components.clinical-modals')

<!-- ══════════════════════════════════════════════════════════════════════
     MODAL DEL MINIJUEGO: ESTILO PLANTS VS ZOMBIES ZEN GARDEN
     ══════════════════════════════════════════════════════════════════════ -->
<div id="treeGameModalOverlay" class="tree-game-modal-overlay">
  <div class="pvz-garden-modal">

    <!-- 1. LOADING SCREEN STATE WITH BOTANICAL ANIMATION -->
    <div id="gameLoaderScreen" class="game-loader-screen">
      <svg class="sprout-loader-anim" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
        <rect x="14" y="22" width="4" height="6" fill="#6B3A1F"/>
        <rect x="10" y="28" width="12" height="3" fill="#1E4A25"/>
        <rect x="11" y="16" width="10" height="7" fill="#3D8C4F"/>
        <rect x="13" y="12" width="6" height="5" fill="#5AB56E"/>
        <rect x="14" y="10" width="4" height="3" fill="#A8E6C0"/>
        <rect x="18" y="14" width="2" height="2" fill="#C0392B"/>
      </svg>
      <h3 style="font-family: var(--font-display); font-size: 1.45rem; margin-top: 1.25rem; color: #FFFFFF;">
        Preparando el Invernadero Zen...
      </h3>
      <p style="color: #C8DDD1; font-size: 0.88rem; max-width: 420px; margin-top: 0.35rem; line-height: 1.5;">
        Entrando al Jardín de la Sabiduría. Cuida tu árbol con herramientas conscientes y recolecta soles de serenidad.
      </p>
      <div class="loader-progress-track">
        <div id="loaderProgressFill" class="loader-progress-fill"></div>
      </div>
      <div style="font-family: var(--font-mono); font-size: 0.74rem; color: #8EADA4; letter-spacing: 0.08em; text-transform: uppercase;">
        <i class="fa-solid fa-seedling"></i> Sincronizando racha diaria · Nivel {{ min(5, max(1, intdiv($streak, 3) + 1)) }}
      </div>
    </div>

    <!-- 2. PVZ GAMEPLAY SCREEN -->
    <div id="gameplayScreen" style="display: none; flex-direction: column; height: 100%; min-height: 0; overflow: hidden;">
      
      <!-- PVZ TOP HUD BAR -->
      <div class="pvz-hud-bar">
        <!-- Mode Tabs: Invernadero vs Tienda -->
        <div class="pvz-nav-tabs">
          <button type="button" class="pvz-tab-btn active" id="tabGardenBtn" onclick="switchPvzScreen('garden')" title="Ver y cuidar tu planta zen">
            <i class="fa-solid fa-seedling"></i>
            <span>Jardín</span>
          </button>
          <button type="button" class="pvz-tab-btn" id="tabShopBtn" onclick="switchPvzScreen('shop')" title="Tienda de recompensas, plantas y escudo de racha">
            <i class="fa-solid fa-store"></i>
            <span>Tienda</span>
          </button>
        </div>

        <!-- 1. Calm Bank Counter -->
        <div class="pvz-calm-bank" id="calmBankDisplay" title="Soles acumulados para la Tienda">
          <i class="fa-solid fa-sun" style="color: #F1C40F; font-size: 1.05rem;"></i>
          <strong id="pvzTotalCalmPoints">125</strong>
        </div>

        <!-- 2. Daily Task Counter -->
        <div class="pvz-daily-task-pill" id="dailyTaskPillDisplay" title="Tareas completadas hoy">
          <i class="fa-solid fa-list-check" style="color: #5AB56E; font-size: 0.92rem;"></i>
          <span><strong id="pvzDailyTasksCompleted">0</strong>/5</span>
        </div>

        <!-- Plant & Level Indicator -->
        <div class="pvz-plant-level-hud" style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
          <span id="activePlantNameDisplay" style="font-weight: 700; font-size: 0.88rem; color: #FFFFFF; white-space: nowrap;">Árbol Sabiduría</span>

          <span class="pvz-hud-level-tag" id="pvzHudLevelTag" title="Nivel actual del árbol (Nivel máximo: 100)">
            Nv. <strong id="pvzCurrentTreeLevel">1</strong>
          </span>

          <div style="display: flex; align-items: center; gap: 4px;" title="Progreso de experiencia para subir de nivel">
            <div class="pvz-vitality-meter" style="width: 60px; height: 8px;">
              <div class="pvz-vitality-fill" id="pvzHappinessFill" style="width: 0%;"></div>
            </div>
            <span id="pvzHappinessText" style="font-size: 0.7rem; color: #A8E6C0; font-family: var(--font-mono); font-weight: 700;">0%</span>
          </div>
        </div>

        <!-- Right Controls: Streak with Shield, Sound, Close -->
        <div class="pvz-right-controls" style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0; margin-left: auto;">
          <div id="pvzStreakWrap" style="background: rgba(230, 126, 34, 0.15); border: 1px solid rgba(230, 126, 34, 0.4); padding: 0.22rem 0.5rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.75rem; color: #FFA59C; font-weight: 700; display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
            <i class="fa-solid fa-fire" style="color: #E67E22;"></i>
            <span><strong id="gameStreakCount">{{ $streak }}</strong>d</span>
            <span id="shieldIndicator" style="display: none; background: #5DADE2; color: #080C0A; padding: 0.1rem 0.35rem; border-radius: 9999px; font-size: 0.65rem;" title="Escudo de Racha Activo">
              <i class="fa-solid fa-shield-halved"></i>
            </span>
          </div>

          <button type="button" onclick="toggleZenSound()" id="soundToggleBtn" title="Alternar Sonido Zen" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #A8E6C0; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i id="soundIcon" class="fa-solid fa-volume-high" style="font-size: 0.82rem;"></i>
          </button>

          <button type="button" onclick="promptExitTreeGame()" title="Salir del Invernadero" class="pvz-exit-btn" style="background: rgba(231, 76, 60, 0.4); border: 1.5px solid #E74C3C; color: #FFFFFF; width: 32px; height: 32px; min-width: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; box-shadow: 0 0 10px rgba(231, 76, 60, 0.4);">
            <i class="fa-solid fa-xmark" style="font-size: 1rem;"></i>
          </button>
        </div>
      </div>

      <!-- ════ SCREEN 1: JARDÍN ZEN INTERACTIVO ════ -->
      <div id="pvzGardenScreen" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden;">
        <!-- PVZ TOOLBELT TRAY -->
        <div class="pvz-toolbelt-tray">
          <div class="pvz-tool-slot active" id="toolWater" onclick="selectPvzTool('water')" title="Regadera (Coste: 0 Calma / Recarga 1.5s)">
            <iconify-icon icon="game-icons:watering-can" class="pvz-game-icon" style="color: #5DADE2; font-size: 1.45rem;"></iconify-icon>
            <span class="tool-name">Regadera</span>
            <span class="tool-cost">Gratis · Tarea</span>
          </div>

          <div class="pvz-tool-slot" id="toolSun" onclick="selectPvzTool('sun')" title="Luz Solar (Coste: 15 Calma / Recarga 3s)">
            <iconify-icon icon="game-icons:sunbeams" class="pvz-game-icon" style="color: #F39C12; font-size: 1.45rem;"></iconify-icon>
            <span class="tool-name">Luz Solar</span>
            <span class="tool-cost">15 Calma</span>
          </div>

          <div class="pvz-tool-slot" id="toolSpray" onclick="selectPvzTool('spray')" title="Spray Calma (Coste: 20 Calma / Recarga 3.5s)">
            <iconify-icon icon="game-icons:delicate-perfume" class="pvz-game-icon" style="color: #48C9B0; font-size: 1.45rem;"></iconify-icon>
            <span class="tool-name">Spray Calma</span>
            <span class="tool-cost">20 Calma</span>
          </div>

          <div class="pvz-tool-slot" id="toolPhonograph" onclick="selectPvzTool('phonograph')" title="Fonógrafo (Coste: 25 Calma / Recarga 4s)">
            <iconify-icon icon="game-icons:music-spell" class="pvz-game-icon" style="color: #AF7AC5; font-size: 1.45rem;"></iconify-icon>
            <span class="tool-name">Fonógrafo</span>
            <span class="tool-cost">25 Calma</span>
          </div>

          <div class="pvz-tool-slot" id="toolFertilizer" onclick="selectPvzTool('fertilizer')" title="Fertilizante (Coste: 35 Calma / Recarga 5s)">
            <iconify-icon icon="game-icons:fertilizer-bag" class="pvz-game-icon" style="color: #58D68D; font-size: 1.45rem;"></iconify-icon>
            <span class="tool-name">Fertilizante</span>
            <span class="tool-cost">35 Calma</span>
          </div>
        </div>

        <!-- PVZ GREENHOUSE STAGE -->
        <div class="pvz-greenhouse-stage" id="pvzStageCanvas" onclick="handleStageClick(event)">
          <!-- Layer for dropped collectible items and Sky Suns -->
          <div id="pvzCollectiblesLayer" style="position: absolute; inset: 0; pointer-events: auto; z-index: 30;"></div>

          <!-- Layer for flying particle effects -->
          <div id="pvzFxLayer" style="position: absolute; inset: 0; pointer-events: none; z-index: 25;"></div>

          <!-- Ambient Sun Glow -->
          <div id="sunGlow" style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(241, 196, 15, 0.25) 0%, transparent 70%); pointer-events: none; transition: all 0.5s ease;"></div>

          <!-- PvZ Thought Bubble -->
          <div id="pvzThoughtBubble" class="pvz-thought-bubble" onclick="satisfyThoughtBubble(event)">
            <span id="thoughtIconWrap" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;">
              <iconify-icon id="thoughtIcon" icon="game-icons:watering-can" style="color: #5DADE2; font-size: 1.35rem;"></iconify-icon>
            </span>
            <span id="thoughtText">¡Tengo sed! Ríegame con la regadera</span>
          </div>

          <!-- Interactive Plant Pot & Tree Container -->
          <div class="pvz-pot-wrapper pot-terracotta" id="pvzTreePot" onclick="applyActiveToolToTree(event)" title="Haz clic para cuidar con la herramienta seleccionada">
            <!-- Dynamic SVG Plant Container -->
            <div id="pvzPlantSvgContainer" style="display: flex; justify-content: center; align-items: flex-end;">
              <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px; z-index: 5;">
                <rect id="treeTrunk" x="14" y="18" width="4" height="10" fill="#6B3A1F"/>
                <rect x="13" y="21" width="1" height="6" fill="#4A2710"/>
                <rect x="17" y="22" width="1" height="5" fill="#4A2710"/>
                <rect id="foliage1" x="7" y="6" width="18" height="14" fill="#2D6B3A"/>
                <rect id="foliage2" x="5" y="8" width="22" height="12" fill="#2D6B3A"/>
                <rect id="foliage3" x="8" y="7" width="16" height="12" fill="#3D8C4F"/>
                <rect id="foliage4" x="10" y="5" width="12" height="14" fill="#5AB56E"/>
                <rect id="foliage5" x="12" y="4" width="8" height="3" fill="#3D8C4F"/>
                <rect id="foliage6" x="11" y="7" width="5" height="3" fill="#7FD68A"/>
                <rect id="apple1" x="9" y="12" width="2" height="2" fill="#C0392B"/>
                <rect id="apple2" x="20" y="11" width="2" height="2" fill="#C0392B"/>
                <rect id="apple3" x="15" y="15" width="2" height="2" fill="#C0392B"/>
              </svg>
            </div>

            <!-- Planter Pot -->
            <div class="pvz-pot-rim" id="potRimElem"></div>
            <div class="pvz-pot-base" id="potBaseElem"></div>
          </div>

          <!-- Grass Lawn Floor -->
          <div class="pvz-grass-lawn"></div>
        </div>

        <!-- PVZ TREE OF WISDOM SPEECH PARCHMENT (Solo el texto de reflexión) -->
        <div class="pvz-wisdom-parchment">
          <div class="pvz-wisdom-text">
            <i class="fa-solid fa-quote-left"></i>
            <span id="pvzWisdomDialogue">
              "Las emociones son como visitantes temporales: acógelas, escúchalas y déjalas seguir su camino."
            </span>
          </div>
        </div>
      </div>

      <!-- ════ SCREEN 2: TIENDA BOTÁNICA & RECOMPENSAS ════ -->
      <div id="pvzShopScreen" class="pvz-shop-screen" style="display: none;">
        <div style="max-width: 1100px; margin: 0 auto; width: 100%;">
          <!-- Header banner -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.1rem; flex-wrap: wrap; gap: 0.75rem; border-bottom: 2px solid #2D4E37; padding-bottom: 0.75rem;">
            <div>
              <h3 style="font-size: 1.15rem; color: #FFFFFF; display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fa-solid fa-store" style="color: #F1C40F;"></i>
                <span>Tienda Botánica & Canje de Recompensas</span>
              </h3>
              <p style="color: #C8DDD1; font-size: 0.82rem; margin-top: 0.2rem; margin-bottom: 0;">
                Canjea tus Soles por escudos protectores, nuevas especies botánicas y macetas artesanales.
              </p>
            </div>
            <div class="pvz-calm-bank" title="Soles disponibles para comprar">
              <i class="fa-solid fa-sun" style="color: #F1C40F;"></i>
              <strong id="shopCalmBalance">125</strong>
            </div>
          </div>

          <!-- 1. PROTECCIÓN DE RACHA -->
          <div style="margin-bottom: 1.35rem;">
            <div style="font-family: var(--font-mono); font-size: 0.78rem; text-transform: uppercase; color: #5DADE2; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 6px;">
              <i class="fa-solid fa-shield-halved"></i>
              <span>Protección de Racha Diaria</span>
            </div>

            <div class="pvz-shop-card" id="shopCardShield">
              <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <!-- Shield Preview Graphic -->
                <div class="pvz-cosmetic-preview" style="width: 70px; height: 70px; margin-bottom: 0; flex-shrink: 0; background: radial-gradient(circle, rgba(93, 173, 226, 0.25) 0%, rgba(13, 22, 16, 0.8) 70%);">
                  <svg width="48" height="48" viewBox="0 0 32 32">
                    <path d="M16 3 L27 7 V16 C27 23 16 29 16 29 C16 29 5 23 5 16 V7 Z" fill="#5DADE2" stroke="#FFFFFF" stroke-width="1.5"/>
                    <path d="M16 6 L24 9.5 V15 C24 20.5 16 25 16 25 C16 25 8 20.5 8 15 V9.5 Z" fill="#2980B9"/>
                    <path d="M13 16 L15 18 L19 13" stroke="#FFFFFF" stroke-width="2" fill="none" stroke-linecap="round"/>
                  </svg>
                </div>
                <div style="flex: 1; min-width: 200px;">
                  <div style="font-size: 0.96rem; font-weight: 700; color: #FFFFFF;">Escudo Zen de Racha (Streak Freeze)</div>
                  <p style="font-size: 0.8rem; color: #C8DDD1; line-height: 1.4; margin-top: 0.2rem; margin-bottom: 0;">
                    Si un día tienes una emergencia y no puedes conectarte, este amuleto evita que tu racha de días vuelva a cero.
                  </p>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.45rem; min-width: 130px;">
                  <span class="pvz-shop-price-badge" id="priceBadgeShield"><i class="fa-solid fa-sun"></i> 100</span>
                  <button type="button" id="btnShopShield" class="pvz-shop-action-btn" onclick="buyStreakShield()">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                    <span>Canjear Escudo</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. COLECCIÓN DE ESPECIES BOTÁNICAS (CARRUSEL) -->
          <div style="margin-bottom: 1.35rem;">
            <div style="font-family: var(--font-mono); font-size: 0.78rem; text-transform: uppercase; color: #5AB56E; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-leaf"></i>
                <span>Especies Botánicas del Invernadero</span>
              </div>
              <span style="font-size: 0.7rem; color: #A8E6C0;">Desliza horizontalmente</span>
            </div>

            <div class="pvz-carousel-wrapper">
              <button type="button" class="pvz-carousel-btn prev" onclick="scrollPvzCarousel('plantsCarouselTrack', -1)" title="Anterior"><i class="fa-solid fa-chevron-left"></i></button>

              <div class="pvz-carousel-track" id="plantsCarouselTrack">
                <!-- Planta 1: Árbol Sabiduría -->
                <div class="pvz-carousel-card owned" id="cardPlantTree">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <rect x="14" y="20" width="4" height="8" fill="#6B3A1F"/>
                      <rect x="6" y="8" width="20" height="14" rx="3" fill="#2D6B3A"/>
                      <rect x="8" y="6" width="16" height="14" rx="3" fill="#3D8C4F"/>
                      <rect x="10" y="5" width="12" height="14" rx="2" fill="#5AB56E"/>
                      <circle cx="10" cy="12" r="1.5" fill="#C0392B"/>
                      <circle cx="21" cy="11" r="1.5" fill="#C0392B"/>
                      <circle cx="16" cy="15" r="1.5" fill="#C0392B"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Árbol Sabiduría</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeTree" style="display: none;"><i class="fa-solid fa-sun"></i> 0</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Citas de DBT y Mente Sabia. Frutos de serenidad.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelTree">Nv. 1</strong></span>
                      <span id="shopPercentTree">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillTree" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn active-equipped" id="btnPlantTree" onclick="selectPlant('tree')">
                    <i class="fa-solid fa-check"></i>
                    <span>En el Invernadero</span>
                  </button>
                </div>

                <!-- Planta 2: Flor de Loto Serena -->
                <div class="pvz-carousel-card" id="cardPlantLotus">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <ellipse cx="16" cy="26" rx="13" ry="3.5" fill="#145A32"/>
                      <ellipse cx="16" cy="25" rx="11" ry="2.5" fill="#1E8449"/>
                      <path d="M16 8 C12 14 9 20 16 24 C23 20 20 14 16 8 Z" fill="#FADBD8"/>
                      <path d="M16 11 C13 16 11 20 16 23 C21 20 19 16 16 11 Z" fill="#F1948A"/>
                      <path d="M10 16 C7 19 9 23 16 25 C12 23 11 19 10 16 Z" fill="#E8DAEF"/>
                      <path d="M22 16 C25 19 23 23 16 25 C20 23 21 19 22 16 Z" fill="#E8DAEF"/>
                      <circle cx="16" cy="21" r="2.5" fill="#F1C40F"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Loto Serena</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeLotus"><i class="fa-solid fa-sun"></i> 150</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Respiración diafragmática. Abre sus pétalos con luz.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelLotus">Nv. 1</strong></span>
                      <span id="shopPercentLotus">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillLotus" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantLotus" onclick="unlockOrSelectPlant('lotus', 150)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 3: Bonsái de Resiliencia -->
                <div class="pvz-carousel-card" id="cardPlantBonsai">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <path d="M15 27 Q18 21 13 17 Q9 13 16 9 Q14 13 17 17 Q19 22 17 27 Z" fill="#5D4037"/>
                      <ellipse cx="11" cy="14" rx="6" ry="3.5" fill="#1E4A25"/>
                      <ellipse cx="11" cy="13" rx="4.5" ry="2.5" fill="#2E7D32"/>
                      <ellipse cx="21" cy="10" rx="7" ry="3.5" fill="#1E4A25"/>
                      <ellipse cx="21" cy="9" rx="5" ry="2.5" fill="#388E3C"/>
                      <ellipse cx="16" cy="7" rx="5" ry="2.5" fill="#4CAF50"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Bonsái Resiliencia</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeBonsai"><i class="fa-solid fa-sun"></i> 300</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Fuerza interior y tolerancia al malestar.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelBonsai">Nv. 1</strong></span>
                      <span id="shopPercentBonsai">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillBonsai" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantBonsai" onclick="unlockOrSelectPlant('bonsai', 300)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 4: Girasol de Gratitud -->
                <div class="pvz-carousel-card" id="cardPlantSunflower">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <rect x="15" y="15" width="2" height="12" fill="#2E7D32"/>
                      <path d="M15 21 Q10 19 8 22 Q12 24 15 22 Z" fill="#4CAF50"/>
                      <path d="M17 19 Q22 17 24 20 Q20 22 17 20 Z" fill="#4CAF50"/>
                      <circle cx="16" cy="12" r="9" fill="#F39C12"/>
                      <circle cx="16" cy="12" r="7.5" fill="#F1C40F"/>
                      <circle cx="16" cy="12" r="4" fill="#5D4037"/>
                      <circle cx="16" cy="12" r="3" fill="#3E2723"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Girasol Gratitud</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeSunflower"><i class="fa-solid fa-sun"></i> 450</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Reflexiones de psicología positiva y calma.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelSunflower">Nv. 1</strong></span>
                      <span id="shopPercentSunflower">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillSunflower" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantSunflower" onclick="unlockOrSelectPlant('sunflower', 450)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 5: Cactus de Fortaleza -->
                <div class="pvz-carousel-card" id="cardPlantCactus">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <circle cx="16" cy="7" r="3" fill="#E91E63"/>
                      <circle cx="16" cy="7" r="1.5" fill="#F1C40F"/>
                      <rect x="13" y="9" width="6" height="19" rx="3" fill="#2E7D32"/>
                      <path d="M13 18 H9 V13 H11 V16 H13 Z" fill="#2E7D32"/>
                      <path d="M19 16 H23 V11 H22 V14 H19 Z" fill="#2E7D32"/>
                      <circle cx="16" cy="14" r="0.8" fill="#FFF59D"/>
                      <circle cx="16" cy="20" r="0.8" fill="#FFF59D"/>
                      <circle cx="10" cy="14" r="0.6" fill="#FFF59D"/>
                      <circle cx="22" cy="12" r="0.6" fill="#FFF59D"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Cactus Fortaleza</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeCactus"><i class="fa-solid fa-sun"></i> 600</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Florecer en los desiertos más áridos.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelCactus">Nv. 1</strong></span>
                      <span id="shopPercentCactus">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillCactus" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantCactus" onclick="unlockOrSelectPlant('cactus', 600)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 6: Bambú de Paz -->
                <div class="pvz-carousel-card" id="cardPlantBamboo">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <rect x="9" y="8" width="3" height="20" rx="1" fill="#43A047"/>
                      <rect x="8" y="14" width="5" height="1" fill="#1B5E20"/>
                      <rect x="8" y="21" width="5" height="1" fill="#1B5E20"/>
                      <rect x="15" y="5" width="3.5" height="23" rx="1" fill="#66BB6A"/>
                      <rect x="14" y="11" width="5.5" height="1" fill="#2E7D32"/>
                      <rect x="14" y="18" width="5.5" height="1" fill="#2E7D32"/>
                      <rect x="21" y="9" width="3" height="19" rx="1" fill="#43A047"/>
                      <rect x="20" y="16" width="5" height="1" fill="#1B5E20"/>
                      <path d="M18 11 Q24 9 26 12 Q22 13 18 12 Z" fill="#81C784"/>
                      <path d="M12 14 Q6 12 5 15 Q9 16 12 15 Z" fill="#81C784"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Bambú de Paz</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeBamboo"><i class="fa-solid fa-sun"></i> 750</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Flexibilidad ante el viento sin romperse.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelBamboo">Nv. 1</strong></span>
                      <span id="shopPercentBamboo">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillBamboo" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantBamboo" onclick="unlockOrSelectPlant('bamboo', 750)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 7: Lavanda de Calma -->
                <div class="pvz-carousel-card" id="cardPlantLavender">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <line x1="16" y1="12" x2="16" y2="28" stroke="#4CAF50" stroke-width="1.5"/>
                      <line x1="12" y1="15" x2="15" y2="28" stroke="#388E3C" stroke-width="1.2"/>
                      <line x1="20" y1="15" x2="17" y2="28" stroke="#388E3C" stroke-width="1.2"/>
                      <ellipse cx="16" cy="7" rx="3" ry="4" fill="#9C27B0"/>
                      <ellipse cx="16" cy="11" rx="3.5" ry="3" fill="#BA68C8"/>
                      <ellipse cx="16" cy="15" rx="3" ry="2.5" fill="#CE93D8"/>
                      <ellipse cx="11" cy="11" rx="2.5" ry="3.5" fill="#8E24AA"/>
                      <ellipse cx="12" cy="15" rx="2.8" ry="2.5" fill="#AB47BC"/>
                      <ellipse cx="21" cy="11" rx="2.5" ry="3.5" fill="#8E24AA"/>
                      <ellipse cx="20" cy="15" rx="2.8" ry="2.5" fill="#AB47BC"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Lavanda Calma</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeLavender"><i class="fa-solid fa-sun"></i> 900</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Esencias relajantes para reducir ansiedad.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelLavender">Nv. 1</strong></span>
                      <span id="shopPercentLavender">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillLavender" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantLavender" onclick="unlockOrSelectPlant('lavender', 900)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Planta 8: Orquídea de Armonía -->
                <div class="pvz-carousel-card" id="cardPlantOrchid">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="56" viewBox="0 0 32 32">
                      <path d="M12 28 Q15 18 20 8" stroke="#2E7D32" stroke-width="2" fill="none"/>
                      <ellipse cx="10" cy="27" rx="6" ry="2" fill="#388E3C" transform="rotate(-20 10 27)"/>
                      <ellipse cx="16" cy="27" rx="6" ry="2" fill="#388E3C" transform="rotate(20 16 27)"/>
                      <circle cx="16" cy="15" r="4.5" fill="#E1BEE7"/>
                      <ellipse cx="13" cy="14" rx="3" ry="2" fill="#BA68C8"/>
                      <ellipse cx="19" cy="14" rx="3" ry="2" fill="#BA68C8"/>
                      <circle cx="16" cy="16" r="2" fill="#E91E63"/>
                      <circle cx="16" cy="16" r="0.8" fill="#FDD835"/>
                      <circle cx="20" cy="8" r="3.5" fill="#F3E5F5"/>
                      <circle cx="20" cy="9" r="1.5" fill="#D81B60"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.9rem;">Orquídea Armonía</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeOrchid"><i class="fa-solid fa-sun"></i> 1100</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; line-height: 1.25; margin: 0;">
                      Belleza en la paciencia y florecimiento.
                    </p>
                    <div class="pvz-shop-level-pill">
                      <span><i class="fa-solid fa-seedling" style="color: #5AB56E;"></i> <strong id="shopLevelOrchid">Nv. 1</strong></span>
                      <span id="shopPercentOrchid">0%</span>
                    </div>
                    <div class="pvz-shop-level-bar">
                      <div class="pvz-shop-level-fill" id="shopLevelFillOrchid" style="width: 0%;"></div>
                    </div>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPlantOrchid" onclick="unlockOrSelectPlant('orchid', 1100)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>
              </div>

              <button type="button" class="pvz-carousel-btn next" onclick="scrollPvzCarousel('plantsCarouselTrack', 1)" title="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>

          <!-- 3. PERSONALIZACIÓN DE MACETAS (CARRUSEL) -->
          <div>
            <div style="font-family: var(--font-mono); font-size: 0.78rem; text-transform: uppercase; color: #C8B87A; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-palette"></i>
                <span>Macetas Artesanales Zen</span>
              </div>
              <span style="font-size: 0.7rem; color: #F9E79F;">Desliza horizontalmente</span>
            </div>

            <div class="pvz-carousel-wrapper">
              <button type="button" class="pvz-carousel-btn prev" onclick="scrollPvzCarousel('potsCarouselTrack', -1)" title="Anterior"><i class="fa-solid fa-chevron-left"></i></button>

              <div class="pvz-carousel-track" id="potsCarouselTrack">
                <!-- Maceta 1: Terracota -->
                <div class="pvz-carousel-card owned" id="cardPotTerracotta">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#BA6B33" stroke="#9C5727" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#7A431D" stroke="#9C5727" stroke-width="1"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Barro Terracota</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeTerracotta" style="display: none;"><i class="fa-solid fa-sun"></i> 0</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Maceta clásica de arcilla tradicional.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn active-equipped" id="btnPotTerracotta" onclick="selectPot('terracotta')">
                    <i class="fa-solid fa-check"></i>
                    <span>Equipada</span>
                  </button>
                </div>

                <!-- Maceta 2: Jade -->
                <div class="pvz-carousel-card" id="cardPotJade">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#27AE60" stroke="#58D68D" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#1E8449" stroke="#2ECC71" stroke-width="1"/>
                      <circle cx="16" cy="14" r="2.5" fill="#F1C40F"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Cerámica Jade</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeJade"><i class="fa-solid fa-sun"></i> 80</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Esmaltada en verde esmeralda y río.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPotJade" onclick="unlockOrSelectPot('jade', 80)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Maceta 3: Kintsugi Oro -->
                <div class="pvz-carousel-card" id="cardPotKintsugi">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#F1C40F" stroke="#FFFFFF" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#2C3E50" stroke="#F1C40F" stroke-width="1"/>
                      <path d="M11 8 L14 14 L17 12 L19 22" stroke="#F1C40F" stroke-width="1.5" fill="none"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Kintsugi de Oro</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeKintsugi"><i class="fa-solid fa-sun"></i> 160</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Filosofía de reparar grietas con oro.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPotKintsugi" onclick="unlockOrSelectPot('kintsugi', 160)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Maceta 4: Mármol Zen -->
                <div class="pvz-carousel-card" id="cardPotMarble">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#F8F9F9" stroke="#BDC3C7" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#EAEDED" stroke="#BDC3C7" stroke-width="1"/>
                      <path d="M10 10 L14 18 M18 10 L21 16" stroke="#BDC3C7" stroke-width="1" stroke-linecap="round"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Mármol Zen</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeMarble"><i class="fa-solid fa-sun"></i> 240</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Piedra pulida blanca y serenidad pura.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPotMarble" onclick="unlockOrSelectPot('marble', 240)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Maceta 5: Obsidiana Volcánica -->
                <div class="pvz-carousel-card" id="cardPotObsidian">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#342247" stroke="#BB8FCE" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#1C1924" stroke="#8E44AD" stroke-width="1"/>
                      <circle cx="16" cy="15" r="2" fill="#9B59B6"/>
                      <circle cx="16" cy="15" r="1" fill="#E8DAEF"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Obsidiana Mística</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeObsidian"><i class="fa-solid fa-sun"></i> 360</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Piedra volcánica con runas violetas.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPotObsidian" onclick="unlockOrSelectPot('obsidian', 360)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>

                <!-- Maceta 6: Madera de Roble -->
                <div class="pvz-carousel-card" id="cardPotWood">
                  <div class="pvz-cosmetic-preview">
                    <svg width="56" height="42" viewBox="0 0 32 24">
                      <rect x="5" y="4" width="22" height="4" rx="2" fill="#875A29" stroke="#D68910" stroke-width="1"/>
                      <path d="M7 8 L9 20 Q9 22 11 22 L21 22 Q23 22 23 20 L25 8 Z" fill="#6E4720" stroke="#B9770E" stroke-width="1"/>
                      <line x1="8" y1="12" x2="24" y2="12" stroke="#B9770E" stroke-width="1"/>
                      <line x1="9" y1="17" x2="23" y2="17" stroke="#B9770E" stroke-width="1"/>
                    </svg>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                      <span style="font-weight: 700; color: #FFFFFF; font-size: 0.88rem;">Roble Rústico</span>
                      <span class="pvz-shop-price-badge" id="priceBadgeWood"><i class="fa-solid fa-sun"></i> 480</span>
                    </div>
                    <p style="font-size: 0.75rem; color: #C8DDD1; margin: 0;">Madera tallada con aros de bronce.</p>
                  </div>
                  <button type="button" class="pvz-shop-action-btn" id="btnPotWood" onclick="unlockOrSelectPot('wood', 480)">
                    <i class="fa-solid fa-lock"></i>
                    <span>Desbloquear</span>
                  </button>
                </div>
              </div>

              <button type="button" class="pvz-carousel-btn next" onclick="scrollPvzCarousel('potsCarouselTrack', 1)" title="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>


      <!-- ════ PVZ EXIT CONFIRMATION MODAL ════ -->
      <div id="pvzExitConfirmOverlay" class="pvz-exit-confirm-overlay">
        <div class="pvz-exit-card">
          <div style="width: 54px; height: 54px; border-radius: 50%; background: rgba(90, 181, 110, 0.15); border: 2px solid #5AB56E; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.9rem; color: #5AB56E; font-size: 1.5rem;">
            <i class="fa-solid fa-seedling"></i>
          </div>
          <h3 style="font-size: 1.15rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.4rem;">¿Deseas salir del Invernadero?</h3>
          <p style="font-size: 0.82rem; color: #C8DDD1; line-height: 1.45; margin-bottom: 1.25rem;">
            Todo tu progreso, racha y los Soles de Calma recolectados se han guardado con éxito.
          </p>
          <div style="display: flex; gap: 0.65rem; justify-content: center;">
            <button type="button" onclick="cancelExitTreeGame()" class="btn btn-secondary" style="flex: 1; padding: 0.5rem 0.9rem; border-radius: 9px; font-size: 0.8rem; justify-content: center;">
              <i class="fa-solid fa-arrow-left"></i>
              <span>Seguir Cuidando</span>
            </button>
            <button type="button" onclick="confirmExitTreeGame()" class="btn btn-primary" style="flex: 1; padding: 0.5rem 0.9rem; border-radius: 9px; font-size: 0.8rem; justify-content: center;">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              <span>Salir al Inicio</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ════ PVZ IN-GAME NOTIFICATION MODAL (NO BROWSER ALERTS) ════ -->
      <div id="pvzNotificationModal" class="pvz-exit-confirm-overlay" style="z-index: 60;">
        <div class="pvz-exit-card" style="border-color: #F1C40F; box-shadow: 0 20px 50px rgba(0,0,0,0.9), 0 0 35px rgba(241, 196, 15, 0.25);">
          <div id="pvzNotifIconWrap" style="width: 58px; height: 58px; border-radius: 50%; background: rgba(241, 196, 15, 0.15); border: 2px solid #F1C40F; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.9rem; color: #F1C40F; font-size: 1.8rem; transition: all 0.3s ease;">
            <span id="pvzNotifIconContainer" style="display: inline-flex; align-items: center; justify-content: center; line-height: 1;">
              <iconify-icon id="pvzNotifIcon" icon="game-icons:sunbeams" style="font-size: 1.9rem;"></iconify-icon>
            </span>
          </div>
          <h3 id="pvzNotifTitle" style="font-size: 1.2rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.45rem; letter-spacing: 0.02em;">Soles Insuficientes</h3>
          <p id="pvzNotifMessage" style="font-size: 0.84rem; color: #C8DDD1; line-height: 1.5; margin-bottom: 1.25rem;">
            Necesitas más Soles de Calma para esta recompensa.
          </p>
          <button type="button" onclick="closeZenNotification()" class="btn btn-primary" style="width: 100%; padding: 0.6rem 1.1rem; border-radius: 9px; font-size: 0.85rem; justify-content: center; font-weight: 700; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i>
            <span>Entendido</span>
          </button>
        </div>
      </div>

    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  // SENSITIVE & SUICIDAL TENDENCY WORDS FILTER (ZERO EMOJIS)
  const SENSITIVE_KEYWORDS = [
    'armas', 'arma', 'pistola', 'bala', 'morirme', 'quiero morir', 'no quiero vivir',
    'suicidio', 'suicidarme', 'acabar con todo', 'hacerme daño', 'ahorcarme', 'ahorcar',
    'cortarme', 'pastillas para dormir', 'desaparecer', 'no vale la pena vivir',
    'matarme', 'me voy a morir', 'terminar con mi vida', 'quitarme la vida', 'ya no puedo más',
    'autolesion', 'autolesionarme'
  ];

  const journalInput = document.getElementById('journal_entry');
  const crisisAlert = document.getElementById('crisisEmpathicAlert');

  if (journalInput && crisisAlert) {
    journalInput.addEventListener('input', function() {
      const text = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      let detected = false;

      for (const kw of SENSITIVE_KEYWORDS) {
        const normalizedKw = kw.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        if (text.includes(normalizedKw)) {
          detected = true;
          break;
        }
      }

      crisisAlert.style.display = detected ? 'block' : 'none';
    });
  }

  // Smilies selector & Emotion tag buttons
  document.querySelectorAll('.smily-card-option').forEach(card => {
    card.addEventListener('click', function() {
      document.querySelectorAll('.smily-card-option').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  document.querySelectorAll('.emotion-tag-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.emotion-tag-btn').forEach(b => b.classList.remove('active-tag'));
      this.classList.add('active-tag');
      const hiddenInput = document.getElementById('primaryEmotionInput');
      if (hiddenInput) hiddenInput.value = this.getAttribute('data-val');
    });
  });

  // Auto-open game if requested via URL param or hash
  try {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_game') === '1' || window.location.hash === '#tree-game') {
      setTimeout(() => {
        if (typeof window.openTreeGame === 'function') {
          window.openTreeGame();
          try {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
          } catch(e) {}
        }
      }, 300);
    }
  } catch(e) {}
</script>
@endpush
