@extends('layouts.guest')

@section('title', 'Técnica STOP (DBT) para Desbordamiento — A tu lado')

@section('content')
<div style="max-width: 860px; margin: 3rem auto; padding: 0 1.5rem;">

  <div style="text-align: center; margin-bottom: 3rem;">
    <span class="mono-tag" style="color: var(--terra-600);">Terapia Dialéctico Conductual (DBT)</span>
    <h1 style="font-size: 2.4rem; margin-top: 0.3rem;">La Técnica STOP</h1>
    <p style="color: var(--ink-600); max-width: 550px; margin: 0.5rem auto 0; font-size: 0.95rem;">
      Cuando una emoción intensa te empuja a reaccionar impulsivamente, aplica los 4 pasos para proteger tus vínculos y tu bienestar.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">

    <!-- S -->
    <div class="card" style="border-left: 6px solid #c0392b;">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: #c0392b;">S</span>
          <div>
            <h2 style="font-size: 1.25rem;">Stop (Para)</h2>
            <span class="mono-tag" style="color: var(--ink-400);">Pausa Inmediata</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--ink-700); line-height: 1.7;">
          <strong>No te muevas ni hables.</strong> Congela tus músculos un segundo. No envíes ese mensaje, no tomes una decisión bajo la influencia de la adrenalina. Recuerda: tú estás al mando de tus acciones, no la emoción.
        </p>
      </div>
    </div>

    <!-- T -->
    <div class="card" style="border-left: 6px solid var(--sky-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--sky-600);">T</span>
          <div>
            <h2 style="font-size: 1.25rem;">Take a breath (Respira)</h2>
            <span class="mono-tag" style="color: var(--ink-400);">Oxigena el cerebro</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--ink-700); line-height: 1.7;">
          Toma una respiración profunda por la nariz. Siente cómo el aire expande tu abdomen y exhala despacio por la boca. Esta pausa biológica envía la señal de que no hay peligro mortal inmediato.
        </p>
      </div>
    </div>

    <!-- O -->
    <div class="card" style="border-left: 6px solid var(--lav-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--lav-600);">O</span>
          <div>
            <h2 style="font-size: 1.25rem;">Observe (Observa)</h2>
            <span class="mono-tag" style="color: var(--ink-400);">Atención Plena</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--ink-700); line-height: 1.7;">
          Observa qué está ocurriendo dentro y fuera de ti. ¿Qué pensamientos surgen? ¿Qué sensaciones físicas sientes en el pecho o la garganta? ¿Qué está diciendo la otra persona? Separa los hechos reales de las suposiciones.
        </p>
      </div>
    </div>

    <!-- P -->
    <div class="card" style="border-left: 6px solid var(--sage-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span style="font-size: 2.2rem; font-family: var(--font-display); font-weight: 900; color: var(--sage-600);">P</span>
          <div>
            <h2 style="font-size: 1.25rem;">Proceed (Procede con Sabiduría)</h2>
            <span class="mono-tag" style="color: var(--ink-400);">Mente Sabia</span>
          </div>
        </div>
        <p style="font-size: 0.9rem; color: var(--ink-700); line-height: 1.7;">
          Pregúntate: <em>"¿Qué acción va a mejorar esta situación en lugar de empeorarla?"</em>. Elige responder con asertividad y calma, o pide un momento a solas para continuar más tarde.
        </p>
      </div>
    </div>

  </div>

  <div style="text-align: center; margin-top: 3rem;">
    <a href="{{ route('tools.respiracion') }}" class="btn btn-primary btn-lg">
      🌸 Practicar Respiración Ahora
    </a>
  </div>

</div>
@endsection
