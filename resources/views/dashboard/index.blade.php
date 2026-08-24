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
        <h1 style="color: #FFFFFF !important; font-size: clamp(1.35rem, 2.5vw, 1.85rem); margin: 0; line-height: 1.2; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
          <span>Hola, {{ $user->name }}</span>
          @if($user->isProfessional())
            <x-verified-badge size="20" />
          @endif
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

        <!-- Dynamic Pixel Tree Preview with JUGAR BUTTON (ITEM 5) -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.85rem; background: rgba(255, 255, 255, 0.7); border-radius: 16px; padding: 0.75rem 1rem; border: 1px solid rgba(200, 184, 122, 0.3); flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
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

        <!-- 1. Calm Bank Counter (Solo icono de sol y número) -->
        <div class="pvz-calm-bank" id="calmBankDisplay" title="Soles acumulados para la Tienda">
          <i class="fa-solid fa-sun" style="color: #F1C40F; font-size: 1.05rem;"></i>
          <strong id="pvzTotalCalmPoints">125</strong>
        </div>

        <!-- 2. Daily Task Counter (0 / 5) -->
        <div class="pvz-daily-task-pill" id="dailyTaskPillDisplay" title="Tareas completadas hoy">
          <i class="fa-solid fa-list-check" style="color: #5AB56E; font-size: 0.92rem;"></i>
          <span>Tareas: <strong id="pvzDailyTasksCompleted">0</strong> / 5</span>
        </div>

        <!-- Plant & Level Indicator (Nombre, Nv. y barra de porcentaje para subir de nivel) -->
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-shrink: 0;">
          <span id="activePlantNameDisplay" style="font-weight: 700; font-size: 0.95rem; color: #FFFFFF; white-space: nowrap;">Árbol Sabiduría</span>

          <span class="pvz-hud-level-tag" id="pvzHudLevelTag" title="Nivel actual del árbol (Nivel máximo: 100)">
            Nv. <strong id="pvzCurrentTreeLevel">1</strong>
          </span>

          <div style="display: flex; align-items: center; gap: 6px;" title="Progreso de experiencia para subir de nivel">
            <div class="pvz-vitality-meter" style="width: 80px; height: 9px;">
              <div class="pvz-vitality-fill" id="pvzHappinessFill" style="width: 0%;"></div>
            </div>
            <span id="pvzHappinessText" style="font-size: 0.74rem; color: #A8E6C0; font-family: var(--font-mono); font-weight: 700;">0%</span>
          </div>
        </div>

        <!-- Right Controls: Streak with Shield, Sound, Close (Con espaciado seguro) -->
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; margin-right: 0.2rem;">
          <div id="pvzStreakWrap" style="background: rgba(230, 126, 34, 0.15); border: 1px solid rgba(230, 126, 34, 0.4); padding: 0.3rem 0.75rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.8rem; color: #FFA59C; font-weight: 700; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-fire" style="color: #E67E22;"></i>
            <span><strong id="gameStreakCount">{{ $streak }}</strong>d</span>
            <span id="shieldIndicator" style="display: none; background: #5DADE2; color: #080C0A; padding: 0.1rem 0.45rem; border-radius: 9999px; font-size: 0.7rem; margin-left: 2px;" title="Escudo de Racha Activo">
              <i class="fa-solid fa-shield-halved"></i> Escudo
            </span>
          </div>

          <button type="button" onclick="toggleZenSound()" id="soundToggleBtn" title="Alternar Sonido Zen" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #A8E6C0; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <i id="soundIcon" class="fa-solid fa-volume-high" style="font-size: 0.88rem;"></i>
          </button>

          <button type="button" onclick="promptExitTreeGame()" title="Salir del Invernadero" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #FFFFFF; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
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
  // ══════════════════════════════════════════════════════════════════════
  // SENSITIVE & SUICIDAL TENDENCY WORDS FILTER (ZERO EMOJIS)
  // ══════════════════════════════════════════════════════════════════════
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

  // ══════════════════════════════════════════════════════════════════════
  // ══════════════════════════════════════════════════════════════════════
  // PLANTS VS ZOMBIES ZEN GARDEN & PROGRESSIVE BOTANICAL LEVELING (1-100)
  // ══════════════════════════════════════════════════════════════════════
  let soundEnabled = true;
  let audioCtx = null;
  let activeTool = 'water';

  // 1. Soles Totales (Monedero acumulativo persistente para la tienda)
  let totalCalmPoints = 125;
  try {
    const savedTotal = localStorage.getItem('atulado_zen_total_calm');
    if (savedTotal !== null) {
      totalCalmPoints = parseInt(savedTotal, 10) || 0;
    }
  } catch(e) {}

  // 2. Contador de Tareas Diarias (0 / 5, reiniciado cada nuevo día)
  let dailyTasksCompleted = 0;
  const DAILY_TASKS_MAX = 5;
  try {
    const todayStr = new Date().toISOString().slice(0, 10);
    const storedDate = localStorage.getItem('atulado_zen_daily_date');
    if (storedDate === todayStr) {
      dailyTasksCompleted = parseInt(localStorage.getItem('atulado_zen_daily_tasks'), 10) || 0;
    } else {
      dailyTasksCompleted = 0;
      localStorage.setItem('atulado_zen_daily_date', todayStr);
      localStorage.setItem('atulado_zen_daily_tasks', '0');
    }
  } catch(e) {}

  let currentThought = 'water';
  let skySunInterval = null;

  // Species metadata
  const ALL_SPECIES = [
    { id: 'Tree', key: 'tree', name: 'Árbol Sabiduría', cost: 0 },
    { id: 'Lotus', key: 'lotus', name: 'Loto Serena', cost: 150 },
    { id: 'Bonsai', key: 'bonsai', name: 'Bonsái Resiliencia', cost: 300 },
    { id: 'Sunflower', key: 'sunflower', name: 'Girasol Gratitud', cost: 450 },
    { id: 'Cactus', key: 'cactus', name: 'Cactus Fortaleza', cost: 600 },
    { id: 'Bamboo', key: 'bamboo', name: 'Bambú de Paz', cost: 750 },
    { id: 'Lavender', key: 'lavender', name: 'Lavanda Calma', cost: 900 },
    { id: 'Orchid', key: 'orchid', name: 'Orquídea Armonía', cost: 1100 }
  ];

  const SPECIES_NAMES = {
    tree: 'Árbol Sabiduría',
    lotus: 'Loto Serena',
    bonsai: 'Bonsái Resiliencia',
    sunflower: 'Girasol Gratitud',
    cactus: 'Cactus Fortaleza',
    bamboo: 'Bambú de Paz',
    lavender: 'Lavanda Calma',
    orchid: 'Orquídea Armonía'
  };

  // 3. Multi-plant Individual Leveling System (Nv. 1 to Nv. 100)
  let plantLevels = {
    tree: { level: 1, xp: 0 },
    lotus: { level: 1, xp: 0 },
    bonsai: { level: 1, xp: 0 },
    sunflower: { level: 1, xp: 0 },
    cactus: { level: 1, xp: 0 },
    bamboo: { level: 1, xp: 0 },
    lavender: { level: 1, xp: 0 },
    orchid: { level: 1, xp: 0 }
  };

  try {
    const savedLevels = localStorage.getItem('atulado_zen_plant_levels');
    if (savedLevels) {
      const parsed = JSON.parse(savedLevels);
      Object.keys(plantLevels).forEach(k => {
        if (parsed[k]) {
          plantLevels[k] = {
            level: Math.max(1, Math.min(100, parseInt(parsed[k].level, 10) || 1)),
            xp: Math.max(0, parseInt(parsed[k].xp, 10) || 0)
          };
        }
      });
    }
  } catch(e) {}

  function saveZenPlantLevels() {
    try {
      localStorage.setItem('atulado_zen_plant_levels', JSON.stringify(plantLevels));
    } catch(e) {}
  }

  // Exponential XP required per level: Nv 1 takes ~5 min (~50 XP). Nv 100 requires ~1 month of daily mindful care.
  function getXpRequired(level) {
    if (level >= 100) return 0;
    const base = 40;
    const growth = Math.pow(1 + (level - 1) * 0.082, 2.25);
    return Math.round(base * growth + (level * 10));
  }

  function addPlantXp(plantKey, amount, showPopup = false) {
    if (!plantLevels[plantKey]) {
      plantLevels[plantKey] = { level: 1, xp: 0 };
    }

    const p = plantLevels[plantKey];
    if (p.level >= 100) {
      p.level = 100;
      p.xp = 0;
      saveZenPlantLevels();
      updatePlantHudDisplay();
      updateShopUI();
      return;
    }

    p.xp += amount;
    let req = getXpRequired(p.level);
    let leveledUp = false;

    while (p.xp >= req && p.level < 100) {
      p.xp -= req;
      p.level += 1;
      leveledUp = true;
      req = getXpRequired(p.level);
    }

    if (p.level >= 100) {
      p.level = 100;
      p.xp = 0;
    }

    saveZenPlantLevels();
    updatePlantHudDisplay();
    updateShopUI();

    if (showPopup && amount > 0) {
      spawnScorePopup(`<iconify-icon icon="game-icons:ground-sprout" style="color: #5AB56E; vertical-align: middle;"></iconify-icon> +${amount} XP (${SPECIES_NAMES[plantKey] || 'Planta'})`);
    }

    if (leveledUp) {
      triggerLevelUpCelebration(plantKey, p.level);
    }
  }

  function triggerLevelUpCelebration(plantKey, newLevel) {
    playMusicChord();
    setTimeout(playCollectChime, 250);
    createSunFx();
    createFertilizerFx();

    const plantName = SPECIES_NAMES[plantKey] || 'Tu árbol';
    if (newLevel >= 100) {
      showZenNotification(
        "¡Nivel 100 Máximo!",
        `¡Felicidades! ${plantName} ha alcanzado el nivel 100 máximo tras tu constancia y cuidado diario. Has cultivado la máxima serenidad y armonía.`,
        "game-icons:laurel-crown",
        "#F1C40F"
      );
    } else {
      showZenNotification(
        `¡${plantName} Subió a Nivel ${newLevel}!`,
        `Tu constancia da frutos. Cada nivel requiere más cuidado y dedicación. Sigue cuidando tu árbol para llevarlo al Nivel 100.`,
        "game-icons:party-popper",
        "#5AB56E"
      );
    }
  }

  function updatePlantHudDisplay() {
    const activeKey = zenInventory.activePlant || 'tree';
    const pData = plantLevels[activeKey] || { level: 1, xp: 0 };

    const nameElem = document.getElementById('activePlantNameDisplay');
    const levelElem = document.getElementById('pvzCurrentTreeLevel');
    const levelTag = document.getElementById('pvzHudLevelTag');
    const fillElem = document.getElementById('pvzHappinessFill');
    const textElem = document.getElementById('pvzHappinessText');

    if (nameElem) nameElem.innerText = SPECIES_NAMES[activeKey] || 'Árbol Sabiduría';
    if (levelElem) levelElem.innerText = pData.level;

    if (levelTag) {
      if (pData.level >= 100) {
        levelTag.classList.add('max-level');
        levelTag.innerHTML = '<i class="fa-solid fa-crown"></i> Nv. <strong>100 MAX</strong>';
      } else {
        levelTag.classList.remove('max-level');
        levelTag.innerHTML = `Nv. <strong id="pvzCurrentTreeLevel">${pData.level}</strong>`;
      }
    }

    let percent = 0;
    if (pData.level >= 100) {
      percent = 100;
    } else {
      const req = getXpRequired(pData.level);
      percent = req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0;
    }

    if (fillElem) fillElem.style.width = percent + '%';
    if (textElem) textElem.innerText = percent + '%';
  }

  // Persistent User Zen Inventory (Stored in LocalStorage)
  let zenInventory = {
    shield: false,
    unlockedPlants: ['tree'],
    activePlant: 'tree',
    unlockedPots: ['terracotta'],
    activePot: 'terracotta'
  };

  try {
    const saved = localStorage.getItem('atulado_zen_inventory');
    if (saved) {
      const parsed = JSON.parse(saved);
      zenInventory = Object.assign(zenInventory, parsed);
    }
  } catch(e) {}

  function saveZenInventory() {
    try {
      localStorage.setItem('atulado_zen_inventory', JSON.stringify(zenInventory));
    } catch(e) {}
  }

  function saveZenEconomy() {
    try {
      localStorage.setItem('atulado_zen_total_calm', totalCalmPoints.toString());
      localStorage.setItem('atulado_zen_daily_tasks', dailyTasksCompleted.toString());
      localStorage.setItem('atulado_zen_daily_date', new Date().toISOString().slice(0, 10));
    } catch(e) {}
  }

  // Tool specifications: Costs, Cooldowns (ms), Calma gains, XP gains
  const TOOL_SPECS = {
    water: { cost: 0, cooldown: 1500, calmGain: 15, xpGain: 15, name: 'Regadera' },
    sun: { cost: 15, cooldown: 3000, calmGain: 25, xpGain: 20, name: 'Luz Solar' },
    spray: { cost: 20, cooldown: 3500, calmGain: 30, xpGain: 25, name: 'Spray Calma' },
    phonograph: { cost: 25, cooldown: 4000, calmGain: 35, xpGain: 30, name: 'Fonógrafo' },
    fertilizer: { cost: 35, cooldown: 5000, calmGain: 45, xpGain: 45, name: 'Fertilizante' }
  };

  const toolCooldowns = { water: false, sun: false, spray: false, phonograph: false, fertilizer: false };

  const WISDOM_BANK = [
    "Las emociones son como olas: alcanzan un punto máximo y luego se disipan suavemente.",
    "La aceptación radical no significa resignarse, sino dejar de gastar energía en luchar contra lo que ya es.",
    "Inhala en 4 tiempos, sostén en 7 y exhala en 8. Tu sistema nervioso siempre sabe cómo regresar al centro.",
    "Tu mente es un jardín. No puedes evitar que lleguen malas hierbas, pero sí decides cuáles pensamientos regar.",
    "Cuando sientas una tormenta, ancla tus pies en el suelo: 5 cosas que ves, 4 que tocas, 3 que escuchas.",
    "La autocompasión es tratarte con la misma amabilidad con la que cuidarías a un buen amigo en apuros.",
    "No tienes que resolver toda tu vida hoy; solo necesitas dar el siguiente paso amable hacia ti.",
    "Sentir dolor es parte de la condición humana, pero sufrir en soledad no tiene por qué serlo."
  ];

  const THOUGHT_CONFIG = {
    water: { icon: 'game-icons:watering-can', color: '#5DADE2', text: '¡Tengo sed! Ríegame con la regadera' },
    fertilizer: { icon: 'game-icons:fertilizer-bag', color: '#58D68D', text: '¡Necesito nutrientes! Aplica fertilizante' },
    phonograph: { icon: 'game-icons:music-spell', color: '#AF7AC5', text: '¡Toca música relajante en el fonógrafo!' },
    spray: { icon: 'game-icons:delicate-perfume', color: '#48C9B0', text: '¡Disipa la tensión con spray de calma!' },
    sun: { icon: 'game-icons:sunbeams', color: '#F39C12', text: '¡Dame un baño de luz solar dorada!' }
  };

  // Web Audio Tone Synthesizer
  function playPvzTone(freq, type = 'sine', duration = 0.35) {
    if (!soundEnabled) return;
    try {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') audioCtx.resume();

      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();

      osc.type = type;
      osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

      gain.gain.setValueAtTime(0.001, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.18, audioCtx.currentTime + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);

      osc.connect(gain);
      gain.connect(audioCtx.destination);

      osc.start();
      osc.stop(audioCtx.currentTime + duration);
    } catch(e) {}
  }

  function playCollectChime() {
    playPvzTone(1046.50, 'sine', 0.15); // C6
    setTimeout(() => playPvzTone(1318.51, 'sine', 0.2), 70); // E6
    setTimeout(() => playPvzTone(1567.98, 'sine', 0.25), 140); // G6
  }

  function playMusicChord() {
    playPvzTone(523.25, 'triangle', 0.7); // C5
    setTimeout(() => playPvzTone(659.25, 'triangle', 0.7), 100); // E5
    setTimeout(() => playPvzTone(783.99, 'triangle', 0.7), 200); // G5
    setTimeout(() => playPvzTone(1046.50, 'sine', 1.0), 300); // C6
  }

  function playBuySuccessTone() {
    playPvzTone(784, 'triangle', 0.2);
    setTimeout(() => playPvzTone(1046.5, 'triangle', 0.3), 100);
    setTimeout(() => playPvzTone(1568, 'sine', 0.5), 220);
  }

  window.toggleZenSound = function() {
    soundEnabled = !soundEnabled;
    const icon = document.getElementById('soundIcon');
    if (soundEnabled) {
      if (icon) icon.className = 'fa-solid fa-volume-high';
      playPvzTone(660, 'sine', 0.3);
    } else {
      if (icon) icon.className = 'fa-solid fa-volume-xmark';
    }
  };

  // Carousel Smooth Scrolling Function
  window.scrollPvzCarousel = function(trackId, direction) {
    const track = document.getElementById(trackId);
    if (track) {
      track.scrollBy({ left: direction * 230, behavior: 'smooth' });
      playPvzTone(520, 'sine', 0.08);
    }
  };

  // Navigation Screen Switcher (Jardín vs Tienda)
  window.switchPvzScreen = function(screenName) {
    const gardenScreen = document.getElementById('pvzGardenScreen');
    const shopScreen = document.getElementById('pvzShopScreen');
    const tabGardenBtn = document.getElementById('tabGardenBtn');
    const tabShopBtn = document.getElementById('tabShopBtn');

    if (screenName === 'garden') {
      if (gardenScreen) gardenScreen.style.display = 'flex';
      if (shopScreen) shopScreen.style.display = 'none';
      if (tabGardenBtn) tabGardenBtn.classList.add('active');
      if (tabShopBtn) tabShopBtn.classList.remove('active');
      updatePlantHudDisplay();
      playPvzTone(600, 'sine', 0.15);
    } else {
      if (gardenScreen) gardenScreen.style.display = 'none';
      if (shopScreen) shopScreen.style.display = 'block';
      if (tabGardenBtn) tabGardenBtn.classList.remove('active');
      if (tabShopBtn) tabShopBtn.classList.add('active');
      updateShopUI();
      playPvzTone(750, 'sine', 0.15);
    }
  };

  // Modal Open / Close & Exit Confirmation
  window.openTreeGame = function() {
    const modal = document.getElementById('treeGameModalOverlay');
    const loader = document.getElementById('gameLoaderScreen');
    const gameplay = document.getElementById('gameplayScreen');
    const fill = document.getElementById('loaderProgressFill');
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');

    if (!modal) {
      return;
    }

    if (exitOverlay) exitOverlay.style.display = 'none';

    document.body.style.overflow = 'hidden';
    modal.classList.add('active');
    if (loader) loader.style.display = 'flex';
    if (gameplay) gameplay.style.display = 'none';
    if (fill) fill.style.width = '0%';

    playPvzTone(432, 'sine', 0.5);

    setTimeout(() => { if (fill) fill.style.width = '50%'; }, 200);
    setTimeout(() => { if (fill) fill.style.width = '85%'; }, 500);
    setTimeout(() => {
      if (fill) fill.style.width = '100%';
      playPvzTone(528, 'sine', 0.7);
    }, 750);

    setTimeout(() => {
      if (loader) loader.style.display = 'none';
      if (gameplay) {
        gameplay.style.display = 'flex';
        gameplay.style.flexDirection = 'column';
      }
      renderActivePlant();
      renderActivePot();
      updateCalmDisplays();
      updatePlantHudDisplay();
      updateShieldDisplay();
      pickNewThought();
      spawnInitialSuns();
      startSkySuns();
    }, 950);
  };

  window.promptExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) {
      exitOverlay.style.display = 'flex';
      playPvzTone(520, 'triangle', 0.2);
    } else {
      window.confirmExitTreeGame();
    }
  };

  window.cancelExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) {
      exitOverlay.style.display = 'none';
      playPvzTone(660, 'sine', 0.15);
    }
  };

  window.confirmExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) exitOverlay.style.display = 'none';

    document.body.style.overflow = '';
    const modal = document.getElementById('treeGameModalOverlay');
    if (modal) modal.classList.remove('active');
    if (skySunInterval) clearInterval(skySunInterval);
    playPvzTone(380, 'sine', 0.25);
  };

  window.closeTreeGame = function() {
    window.promptExitTreeGame();
  };

  // Tool Selection
  window.selectPvzTool = function(toolName) {
    if (toolCooldowns[toolName]) return;

    activeTool = toolName;
    document.querySelectorAll('.pvz-tool-slot').forEach(slot => slot.classList.remove('active'));

    const slotId = 'tool' + toolName.charAt(0).toUpperCase() + toolName.slice(1);
    const slot = document.getElementById(slotId);
    if (slot) slot.classList.add('active');

    playPvzTone(700, 'sine', 0.1);
  };

  // Thought Bubble Mechanics
  window.pickNewThought = function() {
    const tools = ['water', 'sun', 'spray', 'phonograph', 'fertilizer'];
    currentThought = tools[Math.floor(Math.random() * tools.length)];
    const cfg = THOUGHT_CONFIG[currentThought];

    const bubble = document.getElementById('pvzThoughtBubble');
    const iconWrap = document.getElementById('thoughtIconWrap');
    const text = document.getElementById('thoughtText');

    if (bubble && text) {
      if (iconWrap) {
        iconWrap.innerHTML = `<iconify-icon icon="${cfg.icon}" style="color: ${cfg.color}; font-size: 1.4rem; vertical-align: middle;"></iconify-icon>`;
      }
      text.innerText = cfg.text;
      bubble.style.display = 'inline-flex';
    }
  };

  window.satisfyThoughtBubble = function(e) {
    if (e) e.stopPropagation();
    window.selectPvzTool(currentThought);
    window.applyActiveToolToTree();
  };

  window.handleStageClick = function(e) {
    if (e.target.closest('.pvz-collectible-item') || e.target.closest('.pvz-sky-sun') || e.target.closest('.pvz-thought-bubble')) return;
    window.applyActiveToolToTree();
  };

  function spawnScorePopup(text, left = '50%', top = '45%') {
    const canvas = document.getElementById('pvzStageCanvas');
    if (!canvas) return;

    const popup = document.createElement('div');
    popup.className = 'pvz-score-popup';
    popup.innerHTML = text;
    popup.style.left = left;
    popup.style.top = top;
    canvas.appendChild(popup);

    setTimeout(() => popup.remove(), 900);
  }

  // Care Application with Calm Costs, XP, and Cooldowns
  window.applyActiveToolToTree = function(e) {
    if (e) e.stopPropagation();

    const spec = TOOL_SPECS[activeTool];
    if (!spec) return;

    if (toolCooldowns[activeTool]) return;

    startToolCooldown(activeTool, spec.cooldown);

    const treeSvg = document.getElementById('interactiveTreeSvg');
    const dialogue = document.getElementById('pvzWisdomDialogue');

    if (treeSvg) {
      treeSvg.classList.remove('pvz-plant-joy');
      void treeSvg.offsetWidth;
      treeSvg.classList.add('pvz-plant-joy');
    }

    const randQuote = WISDOM_BANK[Math.floor(Math.random() * WISDOM_BANK.length)];
    if (dialogue) dialogue.innerText = `"${randQuote}"`;

    // Tool audio-visual actions
    if (activeTool === 'water') {
      playPvzTone(587.33, 'sine', 0.6);
      createWaterFx();
    } else if (activeTool === 'sun') {
      playPvzTone(784, 'sine', 0.8);
      createSunFx();
    } else if (activeTool === 'spray') {
      playPvzTone(440, 'sine', 0.6);
      createSprayFx();
    } else if (activeTool === 'phonograph') {
      playMusicChord();
      createMusicFx();
    } else if (activeTool === 'fertilizer') {
      playPvzTone(659.25, 'triangle', 0.7);
      createFertilizerFx();
    }

    const activeKey = zenInventory.activePlant || 'tree';
    let earnedXp = spec.xpGain || 15;

    // Task fulfillment logic (0/5 up to 5/5)
    if (activeTool === currentThought) {
      if (dailyTasksCompleted < DAILY_TASKS_MAX) {
        dailyTasksCompleted += 1;
        totalCalmPoints += 20;
        earnedXp += 40; // Bonus XP for matching plant's desire
        saveZenEconomy();
        updateCalmDisplays();
        spawnScorePopup(`<iconify-icon icon="game-icons:sunbeams" style="color: #F1C40F; vertical-align: middle;"></iconify-icon> +20 Soles (${dailyTasksCompleted}/${DAILY_TASKS_MAX})`);
      } else {
        earnedXp += 25;
        spawnScorePopup(`<iconify-icon icon="game-icons:sparkles" style="color: #F1C40F; vertical-align: middle;"></iconify-icon> ¡Deseo cumplido! (+${earnedXp} XP)`);
      }

      spawnDroppingSun(true);
      setTimeout(pickNewThought, 2800);
    } else {
      spawnDroppingSun(false);
    }

    // Award XP to active plant
    addPlantXp(activeKey, earnedXp, true);
  }

  function startToolCooldown(toolName, durationMs) {
    toolCooldowns[toolName] = true;
    const slotId = 'tool' + toolName.charAt(0).toUpperCase() + toolName.slice(1);
    const slot = document.getElementById(slotId);
    if (slot) slot.classList.add('cooling');

    setTimeout(() => {
      toolCooldowns[toolName] = false;
      if (slot) slot.classList.remove('cooling');
    }, durationMs);
  }

  // ══════════════════════════════════════════════════════════════════════
  // IN-GAME MODAL NOTIFICATIONS (NO BROWSER ALERTS / NO LOGS)
  // ══════════════════════════════════════════════════════════════════════
  window.showZenNotification = function(title, message, icon = 'game-icons:sunbeams', color = '#F1C40F') {
    const modal = document.getElementById('pvzNotificationModal');
    const titleElem = document.getElementById('pvzNotifTitle');
    const msgElem = document.getElementById('pvzNotifMessage');
    const iconContainer = document.getElementById('pvzNotifIconContainer');
    const iconWrap = document.getElementById('pvzNotifIconWrap');

    if (titleElem) titleElem.innerText = title;
    if (msgElem) msgElem.innerText = message;

    if (iconContainer) {
      if (icon.startsWith('game-icons:') || icon.startsWith('ra ') || icon.startsWith('fa-')) {
        if (icon.startsWith('game-icons:')) {
          iconContainer.innerHTML = `<iconify-icon icon="${icon}" style="font-size: 2.1rem; color: ${color}; line-height: 1;"></iconify-icon>`;
        } else if (icon.startsWith('ra ')) {
          iconContainer.innerHTML = `<i class="${icon}" style="font-size: 1.9rem; color: ${color};"></i>`;
        } else {
          iconContainer.innerHTML = `<i class="${icon}" style="font-size: 1.7rem; color: ${color};"></i>`;
        }
      } else {
        iconContainer.innerHTML = `<iconify-icon icon="game-icons:${icon}" style="font-size: 2.1rem; color: ${color}; line-height: 1;"></iconify-icon>`;
      }
    }

    if (iconWrap) {
      iconWrap.style.color = color;
      iconWrap.style.borderColor = color;
      iconWrap.style.background = color + '22';
      iconWrap.style.boxShadow = `0 0 24px ${color}55`;
    }

    if (modal) modal.style.display = 'flex';
    playPvzTone(350, 'triangle', 0.2);
  };

  window.closeZenNotification = function() {
    const modal = document.getElementById('pvzNotificationModal');
    if (modal) modal.style.display = 'none';
    playPvzTone(600, 'sine', 0.15);
  };

  // ══════════════════════════════════════════════════════════════════════
  // ZEN REWARDS & SHOP LOGIC
  // ══════════════════════════════════════════════════════════════════════
  window.buyStreakShield = function() {
    if (zenInventory.shield) {
      showZenNotification("Escudo Ya Activo", "Ya cuentas con un Escudo Zen activo que protege tu racha diaria.", "game-icons:shield-reflect", "#5DADE2");
      return;
    }

    if (totalCalmPoints < 100) {
      playPvzTone(260, 'sawtooth', 0.25);
      showZenNotification("Soles Insuficientes", "Necesitas 100 Soles para canjear el Escudo Protector de Racha. ¡Completa tareas o recolecta soles en el jardín!", "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= 100;
    zenInventory.shield = true;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    updateShieldDisplay();
    updateShopUI();

    showZenNotification("¡Escudo Activado!", "Tu racha diaria ahora está blindada contra ausencias. Se ha añadido la insignia de protección en tu barra superior.", "game-icons:shield-reflect", "#5DADE2");
  };

  window.unlockOrSelectPlant = function(plantKey, cost) {
    if (zenInventory.unlockedPlants.includes(plantKey)) {
      selectPlant(plantKey);
      return;
    }

    if (totalCalmPoints < cost) {
      playPvzTone(260, 'sawtooth', 0.25);
      showZenNotification("Soles Insuficientes", `Necesitas ${cost} Soles para desbloquear esta especie. Sigue completando tareas para acumular más soles.`, "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= cost;
    zenInventory.unlockedPlants.push(plantKey);
    zenInventory.activePlant = plantKey;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    renderActivePlant();
    updatePlantHudDisplay();
    updateShopUI();

    showZenNotification("¡Nueva Especie Desbloqueada!", `Has desbloqueado ${SPECIES_NAMES[plantKey] || 'esta planta'} y ahora se encuentra en tu Invernadero Zen lista para subir de nivel.`, "game-icons:ground-sprout", "#5AB56E");
  };

  window.selectPlant = function(plantKey) {
    zenInventory.activePlant = plantKey;
    saveZenInventory();
    playPvzTone(660, 'sine', 0.2);
    renderActivePlant();
    updatePlantHudDisplay();
    updateShopUI();
  };

  window.unlockOrSelectPot = function(potKey, cost) {
    if (zenInventory.unlockedPots.includes(potKey)) {
      selectPot(potKey);
      return;
    }

    if (totalCalmPoints < cost) {
      playPvzTone(260, 'sawtooth', 0.25);
      showZenNotification("Soles Insuficientes", `Necesitas ${cost} Soles para desbloquear esta maceta artesanal.`, "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= cost;
    zenInventory.unlockedPots.push(potKey);
    zenInventory.activePot = potKey;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    renderActivePot();
    updateShopUI();

    showZenNotification("¡Maceta Equipada!", "Has desbloqueado y equipado tu nueva maceta artesanal.", "game-icons:flower-pot", "#C8B87A");
  };

  window.selectPot = function(potKey) {
    zenInventory.activePot = potKey;
    saveZenInventory();
    playPvzTone(600, 'sine', 0.2);
    renderActivePot();
    updateShopUI();
  };

  function updateShieldDisplay() {
    const shieldInd = document.getElementById('shieldIndicator');
    if (shieldInd) {
      shieldInd.style.display = zenInventory.shield ? 'inline-flex' : 'none';
    }
  }

  function renderActivePot() {
    const potWrapper = document.getElementById('pvzTreePot');
    if (!potWrapper) return;

    potWrapper.className = 'pvz-pot-wrapper pot-' + zenInventory.activePot;
  }

  function renderActivePlant() {
    const container = document.getElementById('pvzPlantSvgContainer');
    const plantNameDisplay = document.getElementById('activePlantNameDisplay');
    if (!container) return;

    const p = zenInventory.activePlant;
    let svgHtml = '';

    if (p === 'lotus') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Loto Serena';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <ellipse cx="16" cy="27" rx="14" ry="4" fill="#145A32"/>
          <ellipse cx="16" cy="26" rx="12" ry="3" fill="#1E8449"/>
          <path d="M16 8 C11 15 8 22 16 26 C24 22 21 15 16 8 Z" fill="#FADBD8"/>
          <path d="M16 11 C12 16 10 21 16 25 C22 21 20 16 16 11 Z" fill="#F1948A"/>
          <path d="M9 16 C6 20 8 24 16 26 C12 24 10 20 9 16 Z" fill="#E8DAEF"/>
          <path d="M23 16 C26 20 24 24 16 26 C20 24 22 20 23 16 Z" fill="#E8DAEF"/>
          <circle cx="16" cy="22" r="3" fill="#F1C40F"/>
        </svg>
      `;
    } else if (p === 'bonsai') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Bonsái Resiliencia';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <path d="M15 28 Q18 22 13 18 Q8 14 16 10 Q14 14 17 18 Q19 23 17 28 Z" fill="#5D4037"/>
          <ellipse cx="11" cy="14" rx="7" ry="4" fill="#1E4A25"/>
          <ellipse cx="11" cy="13" rx="5" ry="3" fill="#2E7D32"/>
          <ellipse cx="21" cy="10" rx="8" ry="4" fill="#1E4A25"/>
          <ellipse cx="21" cy="9" rx="6" ry="3" fill="#388E3C"/>
          <ellipse cx="16" cy="7" rx="6" ry="3" fill="#4CAF50"/>
        </svg>
      `;
    } else if (p === 'sunflower') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Girasol Gratitud';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="15" y="16" width="2" height="12" fill="#2E7D32"/>
          <path d="M15 22 Q10 20 8 23 Q12 25 15 23 Z" fill="#4CAF50"/>
          <path d="M17 20 Q22 18 24 21 Q20 23 17 21 Z" fill="#4CAF50"/>
          <circle cx="16" cy="12" r="10" fill="#F39C12"/>
          <circle cx="16" cy="12" r="8.5" fill="#F1C40F"/>
          <circle cx="16" cy="12" r="5" fill="#5D4037"/>
          <circle cx="16" cy="12" r="4" fill="#3E2723"/>
        </svg>
      `;
    } else if (p === 'cactus') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Cactus Fortaleza';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="13" y="8" width="6" height="21" rx="3" fill="#27AE60"/>
          <rect x="14" y="9" width="4" height="19" rx="2" fill="#2ECC71"/>
          <path d="M13 18 H8 V12 H10 V16 H13 Z" fill="#27AE60"/>
          <path d="M19 16 H24 V10 H22 V14 H19 Z" fill="#27AE60"/>
          <circle cx="16" cy="6" r="3.5" fill="#E91E63"/>
          <circle cx="16" cy="6" r="1.8" fill="#F1C40F"/>
          <circle cx="16" cy="12" r="0.9" fill="#FFF59D"/>
          <circle cx="16" cy="18" r="0.9" fill="#FFF59D"/>
          <circle cx="16" cy="24" r="0.9" fill="#FFF59D"/>
          <circle cx="9" cy="13" r="0.7" fill="#FFF59D"/>
          <circle cx="23" cy="11" r="0.7" fill="#FFF59D"/>
        </svg>
      `;
    } else if (p === 'bamboo') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Bambú de Paz';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="8" y="8" width="3.5" height="21" rx="1.5" fill="#2E7D32"/>
          <rect x="7" y="14" width="5.5" height="1.2" fill="#1B5E20"/>
          <rect x="7" y="21" width="5.5" height="1.2" fill="#1B5E20"/>
          <rect x="14.5" y="4" width="4" height="25" rx="2" fill="#43A047"/>
          <rect x="13.5" y="10" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="13.5" y="17" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="13.5" y="24" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="21" y="9" width="3.5" height="20" rx="1.5" fill="#388E3C"/>
          <rect x="20" y="16" width="5.5" height="1.2" fill="#1B5E20"/>
          <path d="M18.5 10 Q25 8 28 11 Q23 12.5 18.5 11.5 Z" fill="#81C784"/>
          <path d="M11.5 14 Q5 12 4 15 Q8.5 16.5 11.5 15.5 Z" fill="#81C784"/>
          <path d="M18.5 5 Q24 3 26 6 Q22 7.5 18.5 6.5 Z" fill="#A5D6A7"/>
        </svg>
      `;
    } else if (p === 'lavender') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Lavanda Calma';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <line x1="16" y1="12" x2="16" y2="28" stroke="#388E3C" stroke-width="2"/>
          <line x1="10" y1="14" x2="15" y2="28" stroke="#2E7D32" stroke-width="1.8"/>
          <line x1="22" y1="14" x2="17" y2="28" stroke="#2E7D32" stroke-width="1.8"/>
          <ellipse cx="16" cy="6" rx="3.5" ry="4.5" fill="#8E24AA"/>
          <ellipse cx="16" cy="10" rx="4" ry="3.5" fill="#BA68C8"/>
          <ellipse cx="16" cy="14" rx="3.5" ry="3" fill="#CE93D8"/>
          <ellipse cx="10" cy="10" rx="3" ry="4" fill="#7B1FA2"/>
          <ellipse cx="11" cy="14" rx="3.2" ry="3" fill="#AB47BC"/>
          <ellipse cx="22" cy="10" rx="3" ry="4" fill="#7B1FA2"/>
          <ellipse cx="21" cy="14" rx="3.2" ry="3" fill="#AB47BC"/>
        </svg>
      `;
    } else if (p === 'orchid') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Orquídea Armonía';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <path d="M12 28 Q15 16 22 6" stroke="#2E7D32" stroke-width="2.5" fill="none"/>
          <ellipse cx="9" cy="27" rx="7" ry="2.5" fill="#388E3C" transform="rotate(-20 9 27)"/>
          <ellipse cx="17" cy="27" rx="7" ry="2.5" fill="#388E3C" transform="rotate(20 17 27)"/>
          <circle cx="16" cy="14" r="5" fill="#F3E5F5"/>
          <ellipse cx="12.5" cy="13" rx="3.5" ry="2.5" fill="#CE93D8"/>
          <ellipse cx="19.5" cy="13" rx="3.5" ry="2.5" fill="#CE93D8"/>
          <circle cx="16" cy="15.5" r="2.5" fill="#E91E63"/>
          <circle cx="16" cy="15.5" r="1" fill="#FDD835"/>
          <circle cx="21" cy="6.5" r="3.8" fill="#F8BBD0"/>
          <ellipse cx="18.5" cy="6" rx="2.5" ry="1.8" fill="#EC407A"/>
          <ellipse cx="23.5" cy="6" rx="2.5" ry="1.8" fill="#EC407A"/>
          <circle cx="21" cy="7.5" r="1.8" fill="#C2185B"/>
        </svg>
      `;
    } else {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Árbol Sabiduría';
      svgHtml = `
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
      `;
    }

    container.innerHTML = svgHtml;
  }

  function updateShopUI() {
    const shopBal = document.getElementById('shopCalmBalance');
    if (shopBal) shopBal.innerText = totalCalmPoints;

    // Shield
    const btnShield = document.getElementById('btnShopShield');
    const cardShield = document.getElementById('shopCardShield');
    const priceBadgeShield = document.getElementById('priceBadgeShield');
    if (btnShield && cardShield) {
      if (zenInventory.shield) {
        btnShield.className = 'pvz-shop-action-btn active-equipped';
        btnShield.innerHTML = '<i class="fa-solid fa-check"></i> <span>Escudo Activo</span>';
        btnShield.disabled = true;
        cardShield.classList.add('owned');
        if (priceBadgeShield) priceBadgeShield.style.display = 'none';
      } else {
        btnShield.className = 'pvz-shop-action-btn';
        btnShield.innerHTML = '<i class="fa-solid fa-hand-holding-heart"></i> <span>Canjear Escudo</span>';
        btnShield.disabled = false;
        cardShield.classList.remove('owned');
        if (priceBadgeShield) priceBadgeShield.style.display = 'inline-flex';
      }
    }

    // 8 Plants - Progress & Level display
    ALL_SPECIES.forEach(p => {
      const btn = document.getElementById('btnPlant' + p.id);
      const card = document.getElementById('cardPlant' + p.id);
      const priceBadge = document.getElementById('priceBadge' + p.id);
      const levelLabel = document.getElementById('shopLevel' + p.id);
      const percentLabel = document.getElementById('shopPercent' + p.id);
      const levelFill = document.getElementById('shopLevelFill' + p.id);

      const pData = plantLevels[p.key] || { level: 1, xp: 0 };
      const req = getXpRequired(pData.level);
      const pct = pData.level >= 100 ? 100 : (req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0);

      if (levelLabel) {
        levelLabel.innerText = pData.level >= 100 ? 'Nv. 100 MAX' : `Nv. ${pData.level}`;
      }
      if (percentLabel) {
        percentLabel.innerText = `${pct}%`;
      }
      if (levelFill) {
        levelFill.style.width = `${pct}%`;
      }

      if (!btn || !card) return;

      const isUnlocked = zenInventory.unlockedPlants.includes(p.key);
      const isActive = zenInventory.activePlant === p.key;

      if (isActive) {
        btn.className = 'pvz-shop-action-btn active-equipped';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>En el Invernadero</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else if (isUnlocked) {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = '<i class="fa-solid fa-seedling"></i> <span>Colocar en Jardín</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = `<i class="fa-solid fa-lock"></i> <span>Desbloquear</span>`;
        card.classList.remove('owned');
        if (priceBadge) priceBadge.style.display = 'inline-flex';
      }
    });

    // 6 Pots
    const pots = [
      { id: 'Terracotta', key: 'terracotta', cost: 0 },
      { id: 'Jade', key: 'jade', cost: 80 },
      { id: 'Kintsugi', key: 'kintsugi', cost: 160 },
      { id: 'Marble', key: 'marble', cost: 240 },
      { id: 'Obsidian', key: 'obsidian', cost: 360 },
      { id: 'Wood', key: 'wood', cost: 480 }
    ];

    pots.forEach(pot => {
      const btn = document.getElementById('btnPot' + pot.id);
      const card = document.getElementById('cardPot' + pot.id);
      const priceBadge = document.getElementById('priceBadge' + pot.id);
      if (!btn || !card) return;

      const isUnlocked = zenInventory.unlockedPots.includes(pot.key);
      const isActive = zenInventory.activePot === pot.key;

      if (isActive) {
        btn.className = 'pvz-shop-action-btn active-equipped';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Equipada</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else if (isUnlocked) {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = '<i class="fa-solid fa-palette"></i> <span>Equipar</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = `<i class="fa-solid fa-lock"></i> <span>Desbloquear</span>`;
        card.classList.remove('owned');
        if (priceBadge) priceBadge.style.display = 'inline-flex';
      }
    });
  }

  function updateCalmDisplays() {
    const totalElem = document.getElementById('pvzTotalCalmPoints');
    const shopBal = document.getElementById('shopCalmBalance');
    const dailyCompletedElem = document.getElementById('pvzDailyTasksCompleted');

    if (totalElem) totalElem.innerText = totalCalmPoints;
    if (shopBal) shopBal.innerText = totalCalmPoints;
    if (dailyCompletedElem) dailyCompletedElem.innerText = dailyTasksCompleted;
  }

  // FX CREATORS
  function createWaterFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 10; i++) {
      const drop = document.createElement('div');
      drop.style.position = 'absolute';
      drop.style.left = (42 + Math.random() * 16) + '%';
      drop.style.top = (25 + Math.random() * 15) + '%';
      drop.style.color = '#5DADE2';
      drop.style.fontSize = '0.95rem';
      drop.style.pointerEvents = 'none';
      drop.style.transition = 'all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1)';
      drop.innerHTML = '<i class="fa-solid fa-droplet"></i>';

      fxLayer.appendChild(drop);

      setTimeout(() => {
        drop.style.transform = `translateY(${90 + Math.random() * 30}px) scale(0.6)`;
        drop.style.opacity = '0';
      }, 25);

      setTimeout(() => drop.remove(), 650);
    }
  }

  function createFertilizerFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 8; i++) {
      const sparkle = document.createElement('div');
      sparkle.style.position = 'absolute';
      sparkle.style.left = (38 + Math.random() * 24) + '%';
      sparkle.style.top = (30 + Math.random() * 30) + '%';
      sparkle.style.color = '#58D68D';
      sparkle.style.fontSize = '1.05rem';
      sparkle.style.pointerEvents = 'none';
      sparkle.style.transition = 'all 0.8s ease-out';
      sparkle.innerHTML = '<i class="fa-solid fa-star"></i>';

      fxLayer.appendChild(sparkle);

      setTimeout(() => {
        sparkle.style.transform = `translateY(-25px) rotate(${Math.random() * 180}deg) scale(1.3)`;
        sparkle.style.opacity = '0';
      }, 35);

      setTimeout(() => sparkle.remove(), 850);
    }
  }

  function createMusicFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    const notes = ['fa-music', 'fa-compact-disc', 'fa-music'];
    for (let i = 0; i < 5; i++) {
      const note = document.createElement('div');
      note.style.position = 'absolute';
      note.style.left = (38 + Math.random() * 24) + '%';
      note.style.top = (40 + Math.random() * 20) + '%';
      note.style.color = '#AF7AC5';
      note.style.fontSize = '1.15rem';
      note.style.pointerEvents = 'none';
      note.style.transition = 'all 1.1s ease-out';
      note.innerHTML = `<i class="fa-solid ${notes[i % notes.length]}"></i>`;

      fxLayer.appendChild(note);

      setTimeout(() => {
        note.style.transform = `translate(${Math.random() * 50 - 25}px, -75px) scale(1.2)`;
        note.style.opacity = '0';
      }, 40);

      setTimeout(() => note.remove(), 1200);
    }
  }

  function createSprayFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 7; i++) {
      const mist = document.createElement('div');
      mist.style.position = 'absolute';
      mist.style.left = (40 + Math.random() * 20) + '%';
      mist.style.top = (35 + Math.random() * 25) + '%';
      mist.style.color = '#48C9B0';
      mist.style.fontSize = '1.25rem';
      mist.style.pointerEvents = 'none';
      mist.style.transition = 'all 0.85s ease-out';
      mist.innerHTML = '<i class="fa-solid fa-wind"></i>';

      fxLayer.appendChild(mist);

      setTimeout(() => {
        mist.style.transform = `scale(1.6) translateY(-35px)`;
        mist.style.opacity = '0';
      }, 35);

      setTimeout(() => mist.remove(), 950);
    }
  }

  function createSunFx() {
    const glow = document.getElementById('sunGlow');
    if (!glow) return;
    glow.style.transform = 'translateX(-50%) scale(1.6)';
    setTimeout(() => { glow.style.transform = 'translateX(-50%) scale(1)'; }, 750);
  }

  // Dropping Collectibles (Tree-spawned Suns / Gems)
  function spawnInitialSuns() {
    setTimeout(() => spawnDroppingSun(true), 400);
  }

  function spawnDroppingSun(isBonus = false) {
    const container = document.getElementById('pvzCollectiblesLayer');
    if (!container) return;

    const count = isBonus ? 2 : 1;
    for (let c = 0; c < count; c++) {
      setTimeout(() => {
        const item = document.createElement('div');
        item.className = 'pvz-collectible-item';

        const isGem = Math.random() > 0.6;
        item.innerHTML = `<i class="fa-solid ${isGem ? 'fa-gem' : 'fa-sun'}"></i>`;

        const posX = 28 + Math.random() * 44;
        const posY = 52 + Math.random() * 22;
        item.style.left = posX + '%';
        item.style.top = posY + '%';

        item.onclick = function(e) {
          e.stopPropagation();
          collectSunItem(this, isGem ? 3 : 2);
        };

        container.appendChild(item);
      }, c * 250);
    }
  }

  // Sky Falling Suns (PvZ Daytime Mechanic)
  function startSkySuns() {
    if (skySunInterval) clearInterval(skySunInterval);
    skySunInterval = setInterval(() => {
      spawnSkySun();
    }, 6500);
  }

  function spawnSkySun() {
    const container = document.getElementById('pvzCollectiblesLayer');
    if (!container) return;

    const skySun = document.createElement('div');
    skySun.className = 'pvz-sky-sun';
    skySun.innerHTML = '<i class="fa-solid fa-sun"></i>';
    skySun.style.left = (15 + Math.random() * 70) + '%';

    skySun.onclick = function(e) {
      e.stopPropagation();
      collectSunItem(this, 2);
    };

    container.appendChild(skySun);

    setTimeout(() => {
      if (skySun.parentElement) skySun.remove();
    }, 9000);
  }

  function collectSunItem(elem, points = 2) {
    playCollectChime();

    spawnScorePopup(`<i class="fa-solid fa-plus"></i>${points} Soles (Jardín)`, elem.style.left, elem.style.top);

    totalCalmPoints += points;
    saveZenEconomy();
    updateCalmDisplays();

    // Award XP on collection
    const activeKey = zenInventory.activePlant || 'tree';
    addPlantXp(activeKey, 8, false);

    const bankWrap = document.getElementById('calmBankDisplay');
    if (bankWrap) {
      bankWrap.style.transform = 'scale(1.15)';
      setTimeout(() => { bankWrap.style.transform = 'scale(1)'; }, 200);
    }

    elem.style.transition = 'all 0.45s cubic-bezier(0.2, 0.8, 0.2, 1)';
    elem.style.transform = 'scale(1.3) translateY(-120px)';
    elem.style.opacity = '0';

    setTimeout(() => elem.remove(), 500);
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
