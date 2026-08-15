@extends('layouts.guest')

@section('title', 'Respiración Diafragmática Guiada (4-7-8) — A tu lado')

@section('content')
<div style="background: var(--dark-900); color: #ffffff; min-height: 88vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1.5rem; position: relative; overflow: hidden;">
  <!-- Ambient glow halo -->
  <div class="hero-halo" style="top: 50%; left: 50%;"></div>

  <div style="position: relative; z-index: 2; text-align: center; max-width: 600px;">
    <div class="mono-tag" style="color: var(--sage-300); margin-bottom: 0.5rem;">Herramienta de Biorregulación</div>
    <h1 style="color: #ffffff; font-size: clamp(2rem, 5vw, 3rem); margin-bottom: 0.5rem;">
      Respira con nosotros
    </h1>
    <p style="color: rgba(255, 255, 255, 0.65); font-size: 0.95rem; margin-bottom: 2rem;">
      Sigue el ritmo del círculo. Al inhalar se expande, sostén con calma y exhala lentamente para activar tu sistema parasimpático.
    </p>

    <!-- MODE PICKER PILLS -->
    <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
      <button class="btn btn-sm mode-pill active" data-mode="478" style="background: var(--sage-500); color: #fff; border-radius: var(--radius-full);">
        Técnica 4-7-8 (Calma Profunda)
      </button>
      <button class="btn btn-sm mode-pill" data-mode="box" style="background: rgba(255,255,255,0.1); color: #fff; border-radius: var(--radius-full);">
        Box 4-4-4-4 (Enfoque)
      </button>
      <button class="btn btn-sm mode-pill" data-mode="calm" style="background: rgba(255,255,255,0.1); color: #fff; border-radius: var(--radius-full);">
        Suave 4-4 (Relajación)
      </button>
    </div>

    <!-- BREATHING CIRCLE -->
    <div class="breathing-box">
      <div class="breathing-circle-outer"></div>
      <div class="breathing-circle-core" id="breathingCore">
        <div id="breathStateText" style="font-family: var(--font-display); font-size: 1.4rem; font-weight: 700;">
          Comenzar
        </div>
        <div id="breathSecondsCounter" style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 700; margin-top: 0.2rem;">
          0s
        </div>
      </div>
    </div>

    <!-- CYCLES COUNTER & CONTROLS -->
    <div style="margin-top: 2rem; display: flex; align-items: center; justify-content: center; gap: 1.5rem;">
      <button id="toggleBreathBtn" class="btn btn-primary btn-lg" style="min-width: 180px;">
        ▶ Iniciar Ejercicio
      </button>
      <button id="soundToggleBtn" class="btn btn-outline-white btn-sm" title="Activar/desactivar campanas sonoras">
        🔔 Sonido: Activado
      </button>
    </div>

    <div style="margin-top: 1.5rem; font-family: var(--font-mono); font-size: 0.85rem; color: rgba(255,255,255,0.5);">
      Ciclos completados: <span id="cycleCount" style="color: #ffd166; font-weight: 700;">0</span>
    </div>

    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; color: rgba(255,255,255,0.5);">
      <a href="{{ route('sientes') }}" style="color: var(--sage-300); text-decoration: underline;">← Volver al Test Emocional</a>
      &nbsp;·&nbsp;
      <a href="{{ route('recursos.index') }}" style="color: var(--sage-300); text-decoration: underline;">Más herramientas</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let isRunning = false;
  let currentMode = '478';
  let soundEnabled = true;
  let intervalId = null;
  let cycleCount = 0;

  const core = document.getElementById('breathingCore');
  const stateText = document.getElementById('breathStateText');
  const counterText = document.getElementById('breathSecondsCounter');
  const toggleBtn = document.getElementById('toggleBreathBtn');
  const soundBtn = document.getElementById('soundToggleBtn');
  const cycleDisplay = document.getElementById('cycleCount');

  // Mode intervals (inhale, hold, exhale, hold2)
  const modes = {
    '478':  { inhale: 4, hold: 7, exhale: 8, hold2: 0 },
    'box':  { inhale: 4, hold: 4, exhale: 4, hold2: 4 },
    'calm': { inhale: 4, hold: 0, exhale: 4, hold2: 0 },
  };

  soundBtn.addEventListener('click', () => {
    soundEnabled = !soundEnabled;
    window.zenAudio.enabled = soundEnabled;
    soundBtn.textContent = soundEnabled ? '🔔 Sonido: Activado' : '🔕 Sonido: Desactivado';
  });

  document.querySelectorAll('.mode-pill').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mode-pill').forEach(b => {
        b.style.background = 'rgba(255,255,255,0.1)';
      });
      btn.style.background = 'var(--sage-500)';
      currentMode = btn.getAttribute('data-mode');
      if (isRunning) stopExercise();
    });
  });

  toggleBtn.addEventListener('click', () => {
    if (isRunning) {
      stopExercise();
    } else {
      startExercise();
    }
  });

  function startExercise() {
    isRunning = true;
    toggleBtn.innerHTML = '⏸ Pausar Ejercicio';
    toggleBtn.classList.remove('btn-primary');
    toggleBtn.classList.add('btn-secondary');
    runBreathingCycle();
  }

  function stopExercise() {
    isRunning = false;
    clearTimeout(intervalId);
    toggleBtn.innerHTML = '▶ Iniciar Ejercicio';
    toggleBtn.classList.remove('btn-secondary');
    toggleBtn.classList.add('btn-primary');
    core.className = 'breathing-circle-core';
    stateText.textContent = 'En pausa';
    counterText.textContent = '0s';
  }

  async function runBreathingCycle() {
    if (!isRunning) return;
    const cfg = modes[currentMode];

    // INHALE
    core.className = 'breathing-circle-core inhale';
    stateText.textContent = 'Inhala';
    window.zenAudio.playChime(440, 'sine', 2.0); // A4
    await countdown(cfg.inhale);
    if (!isRunning) return;

    // HOLD
    if (cfg.hold > 0) {
      core.className = 'breathing-circle-core hold';
      stateText.textContent = 'Sostén';
      window.zenAudio.playChime(523.25, 'sine', 1.5); // C5
      await countdown(cfg.hold);
      if (!isRunning) return;
    }

    // EXHALE
    core.className = 'breathing-circle-core exhale';
    stateText.textContent = 'Exhala suave';
    window.zenAudio.playChime(349.23, 'sine', 2.2); // F4
    await countdown(cfg.exhale);
    if (!isRunning) return;

    // HOLD 2 (For Box)
    if (cfg.hold2 > 0) {
      core.className = 'breathing-circle-core';
      stateText.textContent = 'Sostén vacío';
      await countdown(cfg.hold2);
      if (!isRunning) return;
    }

    cycleCount++;
    cycleDisplay.textContent = cycleCount;
    runBreathingCycle();
  }

  function countdown(seconds) {
    return new Promise(resolve => {
      let remaining = seconds;
      counterText.textContent = `${remaining}s`;
      
      const timer = setInterval(() => {
        remaining--;
        if (!isRunning) {
          clearInterval(timer);
          resolve();
          return;
        }
        if (remaining > 0) {
          counterText.textContent = `${remaining}s`;
        } else {
          clearInterval(timer);
          resolve();
        }
      }, 1000);
    });
  }
</script>
@endpush
