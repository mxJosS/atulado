@extends('layouts.guest')

@section('title', 'Técnica Sensorial 5-4-3-2-1 (Grounding) — A tu lado')

@section('content')
<div style="max-width: 780px; margin: 3rem auto; padding: 0 1.5rem;">

  <div style="text-align: center; margin-bottom: 2.5rem;">
    <span class="mono-tag" style="color: var(--sky-600);">Herramienta de Anclaje Sensorial</span>
    <h1 style="font-size: 2.3rem; margin-top: 0.3rem;">La Regla 5-4-3-2-1</h1>
    <p style="color: var(--ink-600); max-width: 520px; margin: 0.5rem auto 0; font-size: 0.95rem;">
      Cuando la ansiedad sobrecarga tu mente, tus 5 sentidos son el ancla más rápida para regresar al aquí y ahora.
    </p>
  </div>

  <!-- PROGRESS BAR -->
  <div style="background: var(--bg-subtle); height: 8px; border-radius: var(--radius-full); margin-bottom: 2rem; overflow: hidden;">
    <div id="groundingProgressBar" style="width: 20%; height: 100%; background: var(--sky-500); transition: width 0.4s ease;"></div>
  </div>

  <!-- STEP 1: 5 COSAS QUE VES -->
  <div class="grounding-step card" id="step1" style="border-top: 5px solid var(--sky-500);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--sky-600);">5</span>
        <div>
          <h2 style="font-size: 1.35rem;">Cosas que puedes VER 👀</h2>
          <div style="font-size: 0.82rem; color: var(--ink-600);">Mira a tu alrededor y nombra 5 objetos o detalles específicos.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El reflejo de la luz en la pared">
        <input type="text" class="form-control" placeholder="2. Ej. El color de la taza sobre la mesa">
        <input type="text" class="form-control" placeholder="3. Ej. Una planta o una sombra">
        <input type="text" class="form-control" placeholder="4. Ej. La textura de la madera o el suelo">
        <input type="text" class="form-control" placeholder="5. Ej. Las letras de un cartel">
      </div>
      <div style="text-align: right;">
        <button class="btn btn-primary" onclick="nextGroundingStep(2)">Continuar al Paso 4 →</button>
      </div>
    </div>
  </div>

  <!-- STEP 2: 4 COSAS QUE TOCAS -->
  <div class="grounding-step card" id="step2" style="display: none; border-top: 5px solid var(--sage-500);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--sage-600);">4</span>
        <div>
          <h2 style="font-size: 1.35rem;">Cosas que puedes TOCAR ✋</h2>
          <div style="font-size: 0.82rem; color: var(--ink-600);">Presta atención a las sensaciones táctiles directas.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. La suavidad de mi playera o suéter">
        <input type="text" class="form-control" placeholder="2. Ej. La firmeza de la silla donde estoy">
        <input type="text" class="form-control" placeholder="3. Ej. La temperatura fresca de la mesa">
        <input type="text" class="form-control" placeholder="4. Ej. La textura de la pantalla del teléfono">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(1)">← Anterior</button>
        <button class="btn btn-primary" onclick="nextGroundingStep(3)">Continuar al Paso 3 →</button>
      </div>
    </div>
  </div>

  <!-- STEP 3: 3 COSAS QUE ESCUCHAS -->
  <div class="grounding-step card" id="step3" style="display: none; border-top: 5px solid var(--lav-500);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--lav-600);">3</span>
        <div>
          <h2 style="font-size: 1.35rem;">Cosas que puedes ESCUCHAR 👂</h2>
          <div style="font-size: 0.82rem; color: var(--ink-600);">Cierra los ojos un segundo y detecta 3 sonidos a la distancia.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El zumbido lejano de un auto">
        <input type="text" class="form-control" placeholder="2. Ej. El sonido de mi propia respiración">
        <input type="text" class="form-control" placeholder="3. Ej. El viento o pájaros afuera">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(2)">← Anterior</button>
        <button class="btn btn-primary" onclick="nextGroundingStep(4)">Continuar al Paso 2 →</button>
      </div>
    </div>
  </div>

  <!-- STEP 4: 2 COSAS QUE HUELEN -->
  <div class="grounding-step card" id="step4" style="display: none; border-top: 5px solid var(--terra-500);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--terra-600);">2</span>
        <div>
          <h2 style="font-size: 1.35rem;">Cosas que puedes OLER 👃</h2>
          <div style="font-size: 0.82rem; color: var(--ink-600);">Inhala profundo por la nariz y busca aromas en tu entorno.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El aroma del café o del té">
        <input type="text" class="form-control" placeholder="2. Ej. El jabón en mis manos o el aire limpio">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(3)">← Anterior</button>
        <button class="btn btn-primary" onclick="nextGroundingStep(5)">Continuar al Paso 1 →</button>
      </div>
    </div>
  </div>

  <!-- STEP 5: 1 COSA QUE SABOREAS -->
  <div class="grounding-step card" id="step5" style="display: none; border-top: 5px solid var(--amber-500);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--amber-600);">1</span>
        <div>
          <h2 style="font-size: 1.35rem;">Cosa que puedes SABOREAR 👅</h2>
          <div style="font-size: 0.82rem; color: var(--ink-600);">Nota el sabor residual en tu boca o toma un sorbo de agua fresca.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El sabor a menta, agua fresca o un caramelo">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(4)">← Anterior</button>
        <button class="btn btn-primary" onclick="finishGrounding()">¡Completar Anclaje! ✨</button>
      </div>
    </div>
  </div>

  <!-- STEP FINAL: COMPLETADO -->
  <div class="grounding-step card" id="stepFinal" style="display: none; text-align: center; padding: 2rem;">
    <span style="font-size: 3.5rem;">🌸</span>
    <h2 style="font-size: 1.75rem; margin-top: 1rem;">Estás anclado(a) en el presente</h2>
    <p style="color: var(--ink-600); max-width: 480px; margin: 0.5rem auto 1.5rem; line-height: 1.7;">
      Respira profundamente. La tormenta en tu mente es solo un momento pasajero; tu cuerpo y el mundo real están aquí, seguros.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('tools.respiracion') }}" class="btn btn-primary">Hacer Respiración 4-7-8</a>
      <a href="{{ route('dashboard') }}" class="btn btn-secondary">Ir a Mi Espacio</a>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  function nextGroundingStep(stepNum) {
    document.querySelectorAll('.grounding-step').forEach(el => el.style.display = 'none');
    const target = document.getElementById(`step${stepNum}`);
    if (target) target.style.display = 'block';
    
    const progress = (stepNum / 5) * 100;
    document.getElementById('groundingProgressBar').style.width = `${progress}%`;
    window.zenAudio.playChime(440 + (stepNum * 40));
  }

  function finishGrounding() {
    document.querySelectorAll('.grounding-step').forEach(el => el.style.display = 'none');
    document.getElementById('stepFinal').style.display = 'block';
    document.getElementById('groundingProgressBar').style.width = '100%';
    window.zenAudio.playChime(659.25, 'sine', 3.0); // E5 note
  }
</script>
@endpush
