@extends('layouts.guest')

@section('title', '¿Cómo te sientes hoy? — A tu lado')

@section('content')
<!-- ════ HERO SECTION OFICIAL (ITEM 3) ════ -->
<section class="hero-forest-green" style="background: #1E2A22 !important; color: #FFFFFF !important; padding: clamp(4rem, 6.5vw, 5.5rem) 1.5rem; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.2) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;"></div>

  <div style="position: relative; z-index: 2; max-width: 820px; margin: 0 auto;">
    <h1 style="color: #FFFFFF !important; font-size: clamp(2.4rem, 5.2vw, 3.8rem); line-height: 1.1; margin-bottom: 1rem; font-weight: 700;">
      ¿Cómo te sientes <em class="editorial-italic" style="color: #A8E6C0 !important; font-weight: 400;">hoy?</em>
    </h1>
    <p style="color: #C8DDD1 !important; font-size: clamp(1rem, 2vw, 1.2rem); line-height: 1.7; font-weight: 300; max-width: 660px; margin: 0 auto;">
      Sin juicios. Sin análisis. Solo un momento para reconocerte y encontrar lo que necesitas ahora.
    </p>
  </div>
</section>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">

  <!-- ════ SECCIÓN 1: ESTADO EMOCIONAL (8 CARDS EN GRID 4x2) ════ -->
  <div style="margin-bottom: 4.5rem;">
    <div style="text-align: center; max-width: 680px; margin: 0 auto 2.5rem;">
      <span class="mono-tag" style="color: #2E5D4B; display: block; margin-bottom: 0.35rem;">— ESTADO EMOCIONAL</span>
      <h2 style="font-size: clamp(1.5rem, 2.8vw, 2.1rem); margin-bottom: 0.4rem; color: #1A2620;">
        Elige lo que más se acerca a lo que sientes
      </h2>
      <p style="color: #556860; font-size: 0.94rem;">
        Puedes cambiar cuando quieras. No hay respuesta incorrecta.
      </p>
    </div>

    @php
      $emotions8 = [
        [
          'id' => 'tranquilo',
          'name' => 'Tranquilo',
          'color' => '#2E5D4B',
          'icon' => 'fa-solid fa-face-smile',
          'desc' => 'En serenidad, paz interior y ritmo pausado.',
          'tool' => 'Diario de Gratitud',
          'url' => route('login'),
          'tip' => 'Aprovecha este estado para guardar un pensamiento positivo en tu diario y anclar esta sensación.'
        ],
        [
          'id' => 'ansioso',
          'name' => 'Ansioso',
          'color' => '#5B4A8A',
          'icon' => 'fa-solid fa-water',
          'desc' => 'Pulso acelerado, mente inquieta, pensamientos rápidos.',
          'tool' => 'Respiración 4-7-8',
          'url' => route('tools.respiracion'),
          'tip' => 'Inhala en 4s, sostén 7s y exhala en 8s para estimular el nervio vago y calmar tu sistema nervioso.'
        ],
        [
          'id' => 'triste',
          'name' => 'Triste',
          'color' => '#9B8EC4',
          'icon' => 'fa-solid fa-cloud-rain',
          'desc' => 'Desánimo, pesadez en el cuerpo, ganas de parar.',
          'tool' => 'Registro y Autocuidado',
          'url' => route('login'),
          'tip' => 'Permítete sentir sin juzgarte. La tristeza pide descanso, cobijo y comprensión.'
        ],
        [
          'id' => 'enojado',
          'name' => 'Enojado',
          'color' => '#C0392B',
          'icon' => 'fa-solid fa-fire',
          'desc' => 'Tensión muscular, frustración, ganas de reaccionar.',
          'tool' => 'Técnica STOP (DBT)',
          'url' => route('tools.stop'),
          'tip' => 'Haz una pausa de 60 segundos. No actúes bajo el impulso caliente. Respira y observa.'
        ],
        [
          'id' => 'agotado',
          'name' => 'Agotado',
          'color' => '#8A7332',
          'icon' => 'fa-solid fa-battery-quarter',
          'desc' => 'Poca energía física y mental, necesidad de recargar.',
          'tool' => 'Pausa Sensorial',
          'url' => route('tools.respiracion'),
          'tip' => 'Apaga pantallas por 5 minutos, toma agua fresca y suelta la tensión de tus hombros.'
        ],
        [
          'id' => 'con-esperanza',
          'name' => 'Con esperanza',
          'color' => '#5AB56E',
          'icon' => 'fa-solid fa-seedling',
          'desc' => 'Sensación de nuevos comienzos, optimismo y fuerza.',
          'tool' => 'Biblioteca de Recursos',
          'url' => route('recursos.index'),
          'tip' => 'Canaliza este impulso para aprender nuevas técnicas y nutrir tu plan de seguridad personal.'
        ],
        [
          'id' => 'confundido',
          'name' => 'Confundido',
          'color' => '#E67E22',
          'icon' => 'fa-solid fa-compass',
          'desc' => 'Dudas, sobrecarga de opciones, falta de claridad.',
          'tool' => 'Grounding 5-4-3-2-1',
          'url' => route('tools.grounding'),
          'tip' => 'Ancla tus 5 sentidos al entorno real. Cuando la mente se nubla, volver al presente aclara el camino.'
        ],
        [
          'id' => 'bien-gracias',
          'name' => 'Bien, gracias',
          'color' => '#3D7A5F',
          'icon' => 'fa-solid fa-sun',
          'desc' => 'Equilibrio, gratitud y armonía con tu día.',
          'tool' => 'Mi Espacio Personal',
          'url' => route('login'),
          'tip' => 'Celebra este bienestar. Tu racha y constancia de autocuidado están dando frutos.'
        ],
      ];
    @endphp

    <!-- 8 Emotions Grid Explicit 4-column layout -->
    <div class="emotion-grid-8" id="feelingsGrid8" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; width: 100%;">
      @foreach($emotions8 as $emo)
        <div class="emotion-card-8" data-id="{{ $emo['id'] }}" data-tool="{{ $emo['tool'] }}" data-url="{{ $emo['url'] }}" data-tip="{{ $emo['tip'] }}" data-name="{{ $emo['name'] }}" style="background: #FFFFFF !important; border: 1.5px solid #DCE8E0; border-radius: 16px; padding: 1.6rem 1.15rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.65rem; cursor: pointer; box-shadow: 0 4px 14px rgba(10, 21, 15, 0.04);">
          <div class="emo-icon-ring" style="width: 56px; height: 56px; border-radius: 50%; background: rgba({{ hexdec(substr($emo['color'],1,2)) }}, {{ hexdec(substr($emo['color'],3,2)) }}, {{ hexdec(substr($emo['color'],5,2)) }}, 0.14); color: {{ $emo['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="{{ $emo['icon'] }}"></i>
          </div>
          <div class="emo-name" style="font-size: 1.05rem; font-weight: 700; color: #1A2620;">{{ $emo['name'] }}</div>
          <div class="emo-desc" style="font-size: 0.82rem; color: #556860; line-height: 1.45;">{{ $emo['desc'] }}</div>
        </div>
      @endforeach
    </div>

    <!-- DYNAMIC RECOMMENDATION BOX (EXPANDS ON CLICK) -->
    <div id="feelingRecommendationBox" class="card" style="display: none; background: #D4EDE2; border: 2px solid #5AB56E; margin-top: 2rem; animation: slideUpFade 0.4s ease;">
      <div class="card-body" style="padding: 1.75rem 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
          <div>
            <span class="mono-tag" style="color: #2E5D4B;">Recomendación para cuando te sientes <strong id="recEmotionName"></strong></span>
            <h3 id="recTitle" style="font-size: 1.4rem; margin-top: 0.25rem; color: #1A2620;"></h3>
            <p id="recTip" style="font-size: 0.95rem; color: #1A2620; max-width: 680px; line-height: 1.65; margin-top: 0.35rem;"></p>
          </div>
          <div>
            <a id="recActionBtn" href="#" class="btn btn-primary btn-lg" style="gap: 8px;">
              <span>Abrir herramienta</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ════ SECCIÓN 2: MINIJUEGO "RESPIRA CONMIGO" (ITEM 3 OBLIGATORIO) ════ -->
  <div class="breathing-module-container" id="respiraConmigoSection" style="background: #0D1410 !important; color: #FFFFFF !important; border-radius: 24px; padding: 3.5rem 2rem; position: relative; overflow: hidden; text-align: center; border: 1px solid rgba(255, 255, 255, 0.08);">
    
    <div style="max-width: 640px; margin: 0 auto; position: relative; z-index: 2;">
      <h2 style="color: #FFFFFF !important; font-size: clamp(2rem, 4vw, 2.8rem); margin-bottom: 0.5rem; font-weight: 700;">
        Respira conmigo
      </h2>
      <p style="color: #C8DDD1 !important; font-size: 1rem; line-height: 1.6; margin-bottom: 1.75rem;">
        Haz clic para iniciar una guía de respiración. Solo un minuto.
      </p>

      <!-- Mode Selector Pills -->
      <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 1.75rem; flex-wrap: wrap;">
        <button class="btn btn-sm breath-mode-btn active" data-mode="478" style="background: #2E5D4B !important; color: #FFFFFF !important; border-radius: 9999px; border: 1px solid #3D7A5F;">
          Técnica 4-7-8 (Calma)
        </button>
        <button class="btn btn-sm breath-mode-btn" data-mode="box" style="background: rgba(255,255,255,0.08) !important; color: #E8F0EA !important; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.2);">
          Box 4-4-4-4 (Enfoque)
        </button>
        <button class="btn btn-sm breath-mode-btn" data-mode="calm" style="background: rgba(255,255,255,0.08) !important; color: #E8F0EA !important; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.2);">
          Suave 4-4 (Relajación)
        </button>
      </div>

      <!-- ZEN BREATHING CIRCLE -->
      <div class="breathing-zen-circle" id="zenBreathCircle" title="Toca para iniciar o pausar" style="width: 220px; height: 220px; border-radius: 50%; margin: 2rem auto; background: radial-gradient(circle, #2E5D4B 0%, #1E4A25 75%, #0D1410 100%); border: 3px solid #5AB56E; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 0 35px rgba(90, 181, 110, 0.35); position: relative; user-select: none;">
        <div class="circle-action-text" id="zenBreathAction" style="font-family: var(--font-editorial); font-style: italic; font-size: 1.55rem; color: #FFFFFF; line-height: 1.1; padding: 0 1rem; text-align: center;">
          Toca para comenzar
        </div>
        <div class="circle-counter-text" id="zenBreathCounter" style="display: none; font-family: var(--font-mono); font-size: 1.4rem; font-weight: 700; color: #A8E6C0; margin-top: 4px;">
          4s
        </div>
      </div>

      <!-- CONTROLS -->
      <div style="margin-top: 2rem; display: flex; align-items: center; justify-content: center; gap: 0.85rem; flex-wrap: wrap;">
        <button id="toggleZenBreathBtn" class="btn btn-primary btn-lg" style="min-width: 240px; text-transform: uppercase; font-family: var(--font-mono); font-size: 0.88rem; letter-spacing: 0.06em; gap: 8px; background: #2E5D4B !important; color: #FFFFFF !important;">
          <i id="zenPlayIcon" class="fa-solid fa-play"></i>
          <span id="zenPlayText">INICIAR EJERCICIO DE RESPIRACIÓN</span>
        </button>
      </div>

      <div style="margin-top: 1.5rem; font-family: var(--font-mono); font-size: 0.85rem; color: #8EADA4;">
        Ciclos completados: <span id="zenCycleCount" style="color: #C8B87A; font-weight: 700;">0</span>
      </div>
    </div>

  </div>

  <!-- ════ SECCIÓN 3: EXPERIENCIAS SENSORIALES COMPLEMENTARIAS ════ -->
  <div style="margin-top: 4.5rem;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 2.5rem;">
      <span class="mono-tag" style="color: #2E5D4B;">Otras Experiencias Sensoriales</span>
      <h2 style="font-size: clamp(1.4rem, 2.5vw, 1.9rem); margin-top: 0.25rem; color: #1A2620;">
        ¿Prefieres otro ejercicio relajante?
      </h2>
      <p style="color: #556860; font-size: 0.92rem;">
        Herramientas interactivas diseñadas para aliviar la tensión y restablecer tu equilibrio.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
      
      <!-- Grounding -->
      <div class="card" style="border-top: 4px solid #2E5D4B;">
        <div class="card-body">
          <span class="badge badge-sage">Anclaje</span>
          <h3 style="font-size: 1.2rem; margin-top: 0.6rem; margin-bottom: 0.35rem; color: #1A2620;">Grounding 5-4-3-2-1</h3>
          <p style="font-size: 0.85rem; color: #556860; line-height: 1.6; margin-bottom: 1.15rem;">
            Asistente paso a paso para reconectar con tus 5 sentidos cuando la mente se siente abrumada.
          </p>
          <a href="{{ route('tools.grounding') }}" class="btn btn-secondary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-hand-holding-heart" style="color: #3D7A5F;"></i>
            <span>Iniciar Anclaje Sensorial</span>
          </a>
        </div>
      </div>

      <!-- DBT STOP -->
      <div class="card" style="border-top: 4px solid #C0392B;">
        <div class="card-body">
          <span class="badge badge-crisis">Pausa Consciente</span>
          <h3 style="font-size: 1.2rem; margin-top: 0.6rem; margin-bottom: 0.35rem; color: #1A2620;">Técnica STOP (DBT)</h3>
          <p style="font-size: 0.85rem; color: #556860; line-height: 1.6; margin-bottom: 1.15rem;">
            Protocolo de 4 pasos para congelar el impulso destructivo y actuar desde tu mente sabia.
          </p>
          <a href="{{ route('tools.stop') }}" class="btn btn-secondary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-circle-pause" style="color: #C0392B;"></i>
            <span>Aprender Técnica STOP</span>
          </a>
        </div>
      </div>

      <!-- Directorio Crisis -->
      <div class="card" style="border-top: 4px solid #8A7332;">
        <div class="card-body">
          <span class="badge badge-gold">Acompañamiento 24/7</span>
          <h3 style="font-size: 1.2rem; margin-top: 0.6rem; margin-bottom: 0.35rem; color: #1A2620;">Líneas de Escucha</h3>
          <p style="font-size: 0.85rem; color: #556860; line-height: 1.6; margin-bottom: 1.15rem;">
            Directorio gratuito y confidencial de atención psicológica telefónica para México y LATAM.
          </p>
          <a href="{{ route('crisis') }}" class="btn btn-secondary btn-sm" style="width: 100%; gap: 6px;">
            <i class="fa-solid fa-phone" style="color: #8A7332;"></i>
            <span>Ver Líneas Telefónicas</span>
          </a>
        </div>
      </div>

    </div>
  </div>

</div>

@include('components.clinical-modals')
@endsection
