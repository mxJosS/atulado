@extends('layouts.guest')

@section('title', 'Respira Conmigo — Biorregulación Guiada 4-7-8')

@section('content')
<div id="mainRespiracionContainer" class="m-hero" style="min-height: 100svh; display: flex; flex-direction: column; align-items: center; justify-content: center;">
  <div class="m-fondo" aria-hidden="true">
    <span class="m-aurora m-aurora-1"></span>
    <span class="m-aurora m-aurora-2"></span>
    <span class="m-aurora m-aurora-3"></span>
  </div>
  <div data-paralaje style="position: absolute; width: 650px; height: 650px; border-radius: 50%; background: radial-gradient(circle, rgba(90,181,110,0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>
  <img src="{{ asset('images/pixel/destello-1.png') }}" alt="" class="m-hero-deco" data-destello style="left: 14%; top: 30%; width: 20px;">
  <img src="{{ asset('images/pixel/destello-3.png') }}" alt="" class="m-hero-deco" data-destello style="right: 15%; top: 42%; width: 16px;">
  <img src="{{ asset('images/pixel/hojas-cayendo.png') }}" alt="" class="m-hero-deco" data-mecer style="left: 10%; bottom: 20%; width: 28px;">

  <div style="position: relative; z-index: 2; text-align: center; max-width: 650px;">
    <span class="m-eyebrow" data-aparecer><span class="m-punto-vivo" aria-hidden="true"></span> Herramienta de biorregulación</span>
    <h1 class="m-titulo" data-aparecer="palabras" style="margin: 1.3rem 0 0.8rem;">Respira conmigo</h1>
    <p class="m-bajada" data-aparecer style="margin-bottom: 2rem;">
      Sigue el ritmo del círculo. Al inhalar se expande suavemente, sostén en calma y exhala despacio para estimular tu nervio vago.
    </p>

    <!-- MODE PICKER PILLS -->
    <div data-aparecer-grupo style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
      <button class="btn btn-sm page-breath-mode active" data-mode="478" style="background: rgba(168, 230, 192, 0.16) !important; color: #FFFFFF !important; border-radius: 9999px; border: 1px solid rgba(168, 230, 192, 0.55);">
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
    <div class="breathing-zen-circle" id="mainBreathCircle" data-aparecer="suave" style="width: 240px; height: 240px; border-radius: 50%; margin: 2rem auto; background: radial-gradient(circle, #2E5D4B 0%, #1E4A25 75%, #0D1410 100%); border: 3px solid #5AB56E; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 0 40px rgba(90, 181, 110, 0.35); position: relative; user-select: none;" title="Toca para iniciar o pausar">
      <div class="circle-action-text" id="mainBreathAction" style="font-family: var(--font-editorial); font-style: italic; font-size: 1.65rem; color: #FFFFFF; line-height: 1.1; padding: 0 1rem; text-align: center;">
        Toca para comenzar
      </div>
      <div class="circle-counter-text" id="mainBreathCounter" style="display: none; font-family: var(--font-mono); font-size: 1.6rem; font-weight: 700; color: #A8E6C0; margin-top: 4px;">
        4s
      </div>
    </div>

    <!-- CONTROLS -->
    <div data-aparecer style="margin-top: 2rem; display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
      <button id="mainToggleBreathBtn" class="btn btn-primary btn-lg" style="min-width: 240px; gap: 8px; font-family: var(--font-mono); font-size: 0.9rem; letter-spacing: 0.05em; background: #A8E6C0 !important; color: #0A1410 !important; box-shadow: 0 12px 32px -10px rgba(168, 230, 192, 0.55);">
        <i id="mainPlayIcon" class="fa-solid fa-play"></i>
        <span id="mainPlayText">INICIAR EJERCICIO</span>
      </button>
    </div>

    <div data-aparecer style="margin-top: 1.5rem; font-family: var(--font-mono); font-size: 0.88rem; color: #8EADA4;">
      Ciclos completados: <span id="mainCycleCount" style="color: #C8B87A; font-weight: 700;">0</span>
    </div>

    <div data-aparecer style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
      <a href="{{ route('sientes') }}" class="m-btn m-btn-vidrio m-btn-chico">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Volver a ¿Cómo te sientes?</span>
      </a>
    </div>
  </div>
</div>
@endsection
