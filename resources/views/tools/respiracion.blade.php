@extends('layouts.guest')

@section('title', 'Respira Conmigo — Biorregulación Guiada 4-7-8')

@section('content')
<div id="mainRespiracionContainer" style="background: #080C0A !important; color: #FFFFFF !important; min-height: 88vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 1.5rem; position: relative; overflow: hidden;">
  
  <div style="position: absolute; width: 650px; height: 650px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; text-align: center; max-width: 650px;">
    <span class="mono-tag" style="color: #A8E6C0; margin-bottom: 0.5rem; display: block;">Herramienta de Biorregulación</span>
    <h1 style="color: #FFFFFF !important; font-size: clamp(2.2rem, 5vw, 3.4rem); margin-bottom: 0.5rem; font-weight: 700;">
      Respira conmigo
    </h1>
    <p style="color: #C8DDD1 !important; font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">
      Sigue el ritmo del círculo. Al inhalar se expande suavemente, sostén en calma y exhala despacio para estimular tu nervio vago.
    </p>

    <!-- MODE PICKER PILLS -->
    <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
      <button class="btn btn-sm page-breath-mode active" data-mode="478" style="background: #2E5D4B !important; color: #FFFFFF !important; border-radius: 9999px; border: 1px solid #3D7A5F;">
        Técnica 4-7-8 (Calma Profunda)
      </button>
      <button class="btn btn-sm page-breath-mode" data-mode="box" style="background: rgba(255,255,255,0.08) !important; color: #E8F0EA !important; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.2);">
        Box 4-4-4-4 (Enfoque)
      </button>
      <button class="btn btn-sm page-breath-mode" data-mode="calm" style="background: rgba(255,255,255,0.08) !important; color: #E8F0EA !important; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.2);">
        Suave 4-4 (Relajación)
      </button>
    </div>

    <!-- BREATHING CIRCLE -->
    <div class="breathing-zen-circle" id="mainBreathCircle" style="width: 240px; height: 240px; border-radius: 50%; margin: 2rem auto; background: radial-gradient(circle, #2E5D4B 0%, #1E4A25 75%, #0D1410 100%); border: 3px solid #5AB56E; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 0 40px rgba(90, 181, 110, 0.35); position: relative; user-select: none;" title="Toca para iniciar o pausar">
      <div class="circle-action-text" id="mainBreathAction" style="font-family: var(--font-editorial); font-style: italic; font-size: 1.65rem; color: #FFFFFF; line-height: 1.1; padding: 0 1rem; text-align: center;">
        Toca para comenzar
      </div>
      <div class="circle-counter-text" id="mainBreathCounter" style="display: none; font-family: var(--font-mono); font-size: 1.6rem; font-weight: 700; color: #A8E6C0; margin-top: 4px;">
        4s
      </div>
    </div>

    <!-- CONTROLS -->
    <div style="margin-top: 2rem; display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
      <button id="mainToggleBreathBtn" class="btn btn-primary btn-lg" style="min-width: 240px; gap: 8px; font-family: var(--font-mono); font-size: 0.9rem; letter-spacing: 0.05em; background: #2E5D4B !important; color: #FFFFFF !important;">
        <i id="mainPlayIcon" class="fa-solid fa-play"></i>
        <span id="mainPlayText">INICIAR EJERCICIO</span>
      </button>
    </div>

    <div style="margin-top: 1.5rem; font-family: var(--font-mono); font-size: 0.88rem; color: #8EADA4;">
      Ciclos completados: <span id="mainCycleCount" style="color: #C8B87A; font-weight: 700;">0</span>
    </div>

    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.88rem; color: #8EADA4;">
      <a href="{{ route('sientes') }}" style="color: #A8E6C0; text-decoration: underline; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Volver a ¿Cómo te sientes?</span>
      </a>
    </div>
  </div>
</div>
@endsection
