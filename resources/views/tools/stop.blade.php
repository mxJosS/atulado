@extends('layouts.guest')

@section('title', 'Técnica STOP (DBT) para Desbordamiento — A tu lado')

@section('content')
<div style="max-width: 860px; margin: 3.5rem auto; padding: 0 1.5rem;">

  <div style="text-align: center; margin-bottom: 2.5rem;">
    <span class="mono-tag" style="color: var(--clinical-red);">— TERAPIA DIALÉCTICO CONDUCTUAL (DBT)</span>
    <h1 style="font-size: 2.4rem; margin-top: 0.3rem; color: var(--text-near-black);">La Técnica STOP</h1>
    <p style="color: var(--text-medium-gray); max-width: 560px; margin: 0.5rem auto 0; font-size: 0.95rem;">
      Cuando una emoción intensa te empuja a reaccionar impulsivamente, aplica los 4 pasos para proteger tus vínculos y tu bienestar.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.25rem;">

    <!-- S -->
    <div class="card" style="border-left: 5px solid var(--clinical-red);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(192,57,43,0.12); color: var(--clinical-red); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-hand"></i>
          </div>
          <div>
            <h2 style="font-size: 1.25rem; color: var(--text-near-black);">Stop (Para)</h2>
            <span class="mono-tag" style="color: var(--text-medium-gray);">Pausa Inmediata</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--text-near-black); line-height: 1.6;">
          <strong>No te muevas ni hables.</strong> Congela tus músculos un segundo. No envíes ese mensaje, no tomes una decisión bajo la influencia de la adrenalina. Recuerda: tú estás al mando de tus acciones, no la emoción.
        </p>
      </div>
    </div>

    <!-- T -->
    <div class="card" style="border-left: 5px solid var(--sage-base);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--sage-pale); color: var(--sage-base); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-lungs"></i>
          </div>
          <div>
            <h2 style="font-size: 1.25rem; color: var(--text-near-black);">Take a breath (Respira)</h2>
            <span class="mono-tag" style="color: var(--text-medium-gray);">Oxigena el cerebro</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--text-near-black); line-height: 1.6;">
          Toma una respiración profunda por la nariz. Siente cómo el aire expande tu abdomen y exhala despacio por la boca. Esta pausa biológica envía la señal de que no hay peligro mortal inmediato.
        </p>
      </div>
    </div>

    <!-- O -->
    <div class="card" style="border-left: 5px solid var(--violet-base);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(91,74,138,0.12); color: var(--violet-base); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <div>
            <h2 style="font-size: 1.25rem; color: var(--text-near-black);">Observe (Observa)</h2>
            <span class="mono-tag" style="color: var(--text-medium-gray);">Atención Plena</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--text-near-black); line-height: 1.6;">
          ¿Qué está pasando adentro de ti y a tu alrededor? ¿Qué pensamientos automáticos tienes? Separa los hechos reales de las interpretaciones catastróficas que tu mente está creando.
        </p>
      </div>
    </div>

    <!-- P -->
    <div class="card" style="border-left: 5px solid #8A7332;">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(200,184,122,0.2); color: #8A7332; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-shoe-prints"></i>
          </div>
          <div>
            <h2 style="font-size: 1.25rem; color: var(--text-near-black);">Proceed (Procede)</h2>
            <span class="mono-tag" style="color: var(--text-medium-gray);">Mente Sabia</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--text-near-black); line-height: 1.6;">
          Pregúntate: <em>"¿Qué acción va a mejorar esta situación en lugar de empeorarla?"</em>. Elige una respuesta consciente y efectiva en lugar de un impulso destructivo.
        </p>
      </div>
    </div>

  </div>

  <div style="text-align: center; margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
    <a href="{{ route('tools.respiracion') }}" class="btn btn-primary" style="gap: 6px;">
      <i class="fa-solid fa-lungs"></i>
      <span>Hacer Respiración 4-7-8</span>
    </a>
    <a href="{{ route('home') }}" class="btn btn-secondary" style="gap: 6px;">
      <i class="fa-solid fa-house"></i>
      <span>Volver al inicio</span>
    </a>
  </div>

</div>
@endsection
