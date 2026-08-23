@extends('layouts.guest')

@section('title', 'Técnica Sensorial 5-4-3-2-1 (Grounding) — A tu lado')

@section('content')
<div style="max-width: 780px; margin: 3.5rem auto; padding: 0 1.5rem;">

  <div style="text-align: center; margin-bottom: 2.5rem;">
    <span class="mono-tag" style="color: var(--sage-base);">— ANCLAJE SENSORIAL</span>
    <h1 style="font-size: 2.4rem; margin-top: 0.3rem; color: var(--text-near-black);">La Regla 5-4-3-2-1</h1>
    <p style="color: var(--text-medium-gray); max-width: 540px; margin: 0.5rem auto 0; font-size: 0.95rem;">
      Cuando la ansiedad sobrecarga tu mente, tus 5 sentidos son el ancla más rápida para regresar al aquí y ahora.
    </p>
  </div>

  <!-- PROGRESS BAR -->
  <div style="background: var(--bg-subtle); height: 8px; border-radius: var(--radius-full); margin-bottom: 2rem; overflow: hidden; border: 1px solid var(--border-light);">
    <div id="groundingProgressBar" style="width: 20%; height: 100%; background: var(--sage-base); transition: width 0.4s ease;"></div>
  </div>

  <!-- STEP 1: 5 COSAS QUE VES -->
  <div class="grounding-step card" id="step1" style="border-top: 5px solid var(--sage-base);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.4rem; font-family: var(--font-display); font-weight: 800; color: var(--sage-base);">5</span>
        <div>
          <h2 style="font-size: 1.35rem; display: flex; align-items: center; gap: 8px; color: var(--text-near-black);">
            <span>Cosas que puedes VER</span>
            <i class="fa-solid fa-eye" style="color: var(--sage-base); font-size: 1.1rem;"></i>
          </h2>
          <div style="font-size: 0.82rem; color: var(--text-medium-gray);">Mira a tu alrededor y nombra 5 objetos o detalles específicos.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.6rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El reflejo de la luz en la pared">
        <input type="text" class="form-control" placeholder="2. Ej. El color de la taza sobre la mesa">
        <input type="text" class="form-control" placeholder="3. Ej. Una planta o una sombra">
        <input type="text" class="form-control" placeholder="4. Ej. La textura de la madera o el suelo">
        <input type="text" class="form-control" placeholder="5. Ej. Las letras de un cartel">
      </div>
      <div style="text-align: right;">
        <button class="btn btn-primary" onclick="nextGroundingStep(2)" style="gap: 6px;">
          <span>Continuar al Paso 4</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 2: 4 COSAS QUE TOCAS -->
  <div class="grounding-step card" id="step2" style="display: none; border-top: 5px solid var(--sage-medium);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.4rem; font-family: var(--font-display); font-weight: 800; color: var(--sage-medium);">4</span>
        <div>
          <h2 style="font-size: 1.35rem; display: flex; align-items: center; gap: 8px; color: var(--text-near-black);">
            <span>Cosas que puedes TOCAR</span>
            <i class="fa-solid fa-hand" style="color: var(--sage-medium); font-size: 1.1rem;"></i>
          </h2>
          <div style="font-size: 0.82rem; color: var(--text-medium-gray);">Presta atención a las sensaciones táctiles directas.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.6rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. La suavidad de mi playera o suéter">
        <input type="text" class="form-control" placeholder="2. Ej. La firmeza de la silla donde estoy">
        <input type="text" class="form-control" placeholder="3. Ej. La temperatura fresca de la mesa">
        <input type="text" class="form-control" placeholder="4. Ej. La textura de la pantalla del teléfono">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(1)" style="gap: 6px;">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Anterior</span>
        </button>
        <button class="btn btn-primary" onclick="nextGroundingStep(3)" style="gap: 6px;">
          <span>Continuar al Paso 3</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 3: 3 COSAS QUE ESCUCHAS -->
  <div class="grounding-step card" id="step3" style="display: none; border-top: 5px solid var(--violet-base);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.4rem; font-family: var(--font-display); font-weight: 800; color: var(--violet-base);">3</span>
        <div>
          <h2 style="font-size: 1.35rem; display: flex; align-items: center; gap: 8px; color: var(--text-near-black);">
            <span>Cosas que puedes ESCUCHAR</span>
            <i class="fa-solid fa-ear-listen" style="color: var(--violet-base); font-size: 1.1rem;"></i>
          </h2>
          <div style="font-size: 0.82rem; color: var(--text-medium-gray);">Cierra los ojos un segundo y detecta 3 sonidos a la distancia.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.6rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El zumbido lejano de un auto">
        <input type="text" class="form-control" placeholder="2. Ej. El sonido de mi propia respiración">
        <input type="text" class="form-control" placeholder="3. Ej. El viento o pájaros afuera">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(2)" style="gap: 6px;">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Anterior</span>
        </button>
        <button class="btn btn-primary" onclick="nextGroundingStep(4)" style="gap: 6px;">
          <span>Continuar al Paso 2</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 4: 2 COSAS QUE HUELEN -->
  <div class="grounding-step card" id="step4" style="display: none; border-top: 5px solid #8A7332;">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.4rem; font-family: var(--font-display); font-weight: 800; color: #8A7332;">2</span>
        <div>
          <h2 style="font-size: 1.35rem; display: flex; align-items: center; gap: 8px; color: var(--text-near-black);">
            <span>Cosas que puedes OLER</span>
            <i class="fa-solid fa-wind" style="color: #8A7332; font-size: 1.1rem;"></i>
          </h2>
          <div style="font-size: 0.82rem; color: var(--text-medium-gray);">Inhala suavemente e identifica olores a tu alrededor.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.6rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. El aroma de mi ropa o perfume">
        <input type="text" class="form-control" placeholder="2. Ej. El café, té o el aire fresco">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(3)" style="gap: 6px;">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Anterior</span>
        </button>
        <button class="btn btn-primary" onclick="nextGroundingStep(5)" style="gap: 6px;">
          <span>Continuar al Paso 1</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 5: 1 COSA QUE SABOREAS -->
  <div class="grounding-step card" id="step5" style="display: none; border-top: 5px solid var(--clinical-red);">
    <div class="card-body">
      <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span style="font-size: 2.4rem; font-family: var(--font-display); font-weight: 800; color: var(--clinical-red);">1</span>
        <div>
          <h2 style="font-size: 1.35rem; display: flex; align-items: center; gap: 8px; color: var(--text-near-black);">
            <span>Cosa que puedes SABOREAR</span>
            <i class="fa-solid fa-cookie-bite" style="color: var(--clinical-red); font-size: 1.1rem;"></i>
          </h2>
          <div style="font-size: 0.82rem; color: var(--text-medium-gray);">Un sorbo de agua, una menta o el sabor actual en tu boca.</div>
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.6rem; margin: 1.25rem 0;">
        <input type="text" class="form-control" placeholder="1. Ej. Un trago de agua fresca">
      </div>
      <div style="display: flex; justify-content: space-between;">
        <button class="btn btn-secondary" onclick="nextGroundingStep(4)" style="gap: 6px;">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Anterior</span>
        </button>
        <button class="btn btn-primary" onclick="finishGrounding()" style="gap: 6px;">
          <i class="fa-solid fa-circle-check"></i>
          <span>Finalizar Anclaje</span>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP COMPLETE -->
  <div class="grounding-step card" id="stepComplete" style="display: none; text-align: center; padding: 3rem 1.5rem; border: 2px solid var(--sage-base); background: var(--sage-pale);">
    <i class="fa-solid fa-spa" style="font-size: 3rem; color: var(--sage-base);"></i>
    <h2 style="font-size: 1.8rem; margin-top: 1rem; margin-bottom: 0.5rem; color: var(--text-near-black);">¡Excelente trabajo!</h2>
    <p style="color: var(--text-near-black); max-width: 500px; margin: 0 auto 1.75rem; line-height: 1.7;">
      Has reconectado con tu cuerpo y con la realidad presente. Tómate unos segundos para sentir el peso de tus pies en el suelo.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
      <button class="btn btn-secondary" onclick="nextGroundingStep(1)" style="gap: 6px;">
        <i class="fa-solid fa-rotate-right"></i>
        <span>Repetir ejercicio</span>
      </button>
      <a href="{{ route('home') }}" class="btn btn-primary" style="gap: 6px;">
        <i class="fa-solid fa-house"></i>
        <span>Volver al inicio</span>
      </a>
    </div>
  </div>

</div>
@endsection
