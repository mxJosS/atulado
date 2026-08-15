@extends('layouts.guest')

@section('title', '¿Cómo te sientes hoy? — A tu lado')

@section('content')
<!-- HERO SECTION -->
<section class="hero-wrapper" style="padding: 4rem 1.5rem 5rem;">
  <div class="hero-halo" style="top: 35%; left: 50%;"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">
      <span>🌱</span> Brújula de Autoconocimiento
    </div>
    <h1 class="hero-title">
      ¿Cómo te sientes <em>hoy</em>?
    </h1>
    <p class="hero-subtitle">
      No hay emociones "buenas" ni "malas". Todas traen un mensaje biológico. Selecciona cómo te sientes y te guiaremos con herramientas precisas.
    </p>
  </div>
</section>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 4.5rem;">

  <!-- EMOTION SELECTOR GRID -->
  <div style="margin-bottom: 3rem;">
    <h2 style="font-size: 1.6rem; text-align: center; margin-bottom: 2rem;">Elige lo más cercano a tu estado actual:</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;" id="feelingGrid">
      @php
        $feelings = [
          ['id' => 'ansiedad', 'name' => 'Ansiedad o Pánico', 'color' => 'sky', 'icon' => '🌊', 'desc' => 'Ritmo acelerado, pensamientos rápidos, opresión.', 'tool' => 'Respiración 4-7-8', 'url' => route('tools.respiracion'), 'tip' => 'Inhala en 4s, sostén 7s y exhala en 8s para desactivar la alarma biológica.'],
          ['id' => 'abrumado', 'name' => 'Sobrecarga / Abrumado', 'color' => 'lav', 'icon' => '🌪️', 'desc' => 'Demasiadas cosas a la vez, sensación de colapso.', 'tool' => 'Grounding 5-4-3-2-1', 'url' => route('tools.grounding'), 'tip' => 'Ancla tus 5 sentidos al entorno inmediato para recuperar el control.'],
          ['id' => 'tristeza', 'name' => 'Tristeza o Vacío', 'color' => 'terra', 'icon' => '🌧️', 'desc' => 'Poca energía, desánimo, necesidad de recogimiento.', 'tool' => 'Registro Emocional', 'url' => route('login'), 'tip' => 'No te fuerces a estar feliz. Valida lo que sientes y date permiso de descansar.'],
          ['id' => 'enojo', 'name' => 'Enojo o Frustración', 'color' => 'dark', 'icon' => '🛑', 'desc' => 'Tensión muscular, sensación de injusticia, rabia.', 'tool' => 'Técnica STOP (DBT)', 'url' => route('tools.stop'), 'tip' => 'Haz una pausa de 60 segundos antes de responder. Respira y observa.'],
          ['id' => 'soledad', 'name' => 'Soledad o Incomprensión', 'color' => 'sage', 'icon' => '🌿', 'desc' => 'Sensación de aislamiento, falta de conexión.', 'tool' => 'Línea de Escucha', 'url' => route('crisis'), 'tip' => 'Recordatorio: no estás solo(a). Hay personas dispuestas a escucharte sin juzgarte.'],
          ['id' => 'insomnio', 'name' => 'Insomnio o Rumiación', 'color' => 'amber', 'icon' => '🌙', 'desc' => 'La mente no se apaga, cansancio físico pero insomnio.', 'tool' => 'Respiración Calma', 'url' => route('tools.respiracion'), 'tip' => 'Coloca una mano en tu abdomen y haz 6 respiraciones lentas exhalando por la boca.'],
          ['id' => 'calma', 'name' => 'En Equilibrio o Calma', 'color' => 'sage', 'icon' => '✨', 'desc' => 'Paz interior, claridad mental, estabilidad.', 'tool' => 'Diario de Gratitud', 'url' => route('login'), 'tip' => 'Guarda este momento en tu bitácora para recordarlo cuando lleguen días nublados.'],
          ['id' => 'esperanza', 'name' => 'Esperanza o Motivación', 'color' => 'amber', 'icon' => '🌱', 'desc' => 'Ganas de crecer, nuevos proyectos, optimismo.', 'tool' => 'Explorar Recursos', 'url' => route('recursos.index'), 'tip' => 'Aprovecha este impulso para nutrir tus hábitos y tu plan de seguridad personal.'],
        ];
      @endphp

      @foreach($feelings as $f)
        <div class="card feeling-card theme-{{ $f['color'] }}" data-id="{{ $f['id'] }}" style="cursor: pointer; transition: all 0.3s ease; padding: 1.5rem; text-align: center;">
          <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">{{ $f['icon'] }}</div>
          <h3 style="font-size: 1.15rem; margin-bottom: 0.35rem; color: var(--ink-900);">{{ $f['name'] }}</h3>
          <p style="font-size: 0.82rem; color: var(--ink-600); line-height: 1.5;">{{ $f['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>

  <!-- DYNAMIC RECOMMENDATION BOX (EXPANDS ON CLICK) -->
  <div id="feelingRecommendationBox" class="card" style="display: none; background: linear-gradient(145deg, #ffffff, var(--sage-50)); border: 2px solid var(--sage-300); margin-bottom: 3.5rem; animation: cardEntrance 0.4s ease;">
    <div class="card-body" style="padding: 2.25rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
          <span class="mono-tag" style="color: var(--sage-700);">Recomendación Personalizada</span>
          <h3 id="recTitle" style="font-size: 1.6rem; margin-top: 0.3rem; color: var(--ink-900);"></h3>
          <p id="recTip" style="font-size: 1rem; color: var(--ink-700); max-width: 650px; line-height: 1.7; margin-top: 0.5rem;"></p>
        </div>
        <div style="align-self: center;">
          <a id="recActionBtn" href="#" class="btn btn-primary btn-lg"></a>
        </div>
      </div>
    </div>
  </div>

  <!-- FEATURED TOOLS PREVIEW -->
  <div>
    <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Herramientas destacadas de autorregulación</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
      <div class="card">
        <div class="card-body">
          <span class="badge badge-sage">Bio-Feedback</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.75rem; margin-bottom: 0.4rem;">Respiración 4-7-8</h3>
          <p style="font-size: 0.88rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
            Ejercicio interactivo con campanas sonoras para reducir la frecuencia cardíaca y la presión arterial.
          </p>
          <a href="{{ route('tools.respiracion') }}" class="btn btn-secondary btn-sm">Abrir herramienta →</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <span class="badge badge-sky">Sensorial</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.75rem; margin-bottom: 0.4rem;">Grounding 5-4-3-2-1</h3>
          <p style="font-size: 0.88rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
            Asistente paso a paso para reconectar con tus 5 sentidos cuando sientas saturación mental.
          </p>
          <a href="{{ route('tools.grounding') }}" class="btn btn-secondary btn-sm">Abrir asistente →</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <span class="badge badge-crisis">Emergencia 24h</span>
          <h3 style="font-size: 1.25rem; margin-top: 0.75rem; margin-bottom: 0.4rem;">Líneas de Crisis</h3>
          <p style="font-size: 0.88rem; color: var(--ink-600); line-height: 1.6; margin-bottom: 1.25rem;">
            Directorio telefónico gratuito y confidencial para México y Latinoamérica con un solo toque.
          </p>
          <a href="{{ route('crisis') }}" class="btn btn-crisis btn-sm">Ver números de ayuda →</a>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  const feelingsData = @json($feelings);

  document.querySelectorAll('.feeling-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.feeling-card').forEach(c => {
        c.style.borderColor = 'rgba(28, 40, 32, 0.07)';
        c.style.transform = 'none';
      });
      card.style.borderColor = 'var(--sage-500)';
      card.style.transform = 'translateY(-4px)';

      const fId = card.getAttribute('data-id');
      const item = feelingsData.find(x => x.id === fId);

      if (item) {
        const box = document.getElementById('feelingRecommendationBox');
        document.getElementById('recTitle').textContent = `Para ${item.name}: ${item.tool}`;
        document.getElementById('recTip').textContent = item.tip;
        const btn = document.getElementById('recActionBtn');
        btn.textContent = `Ir a ${item.tool} →`;
        btn.href = item.url;

        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.zenAudio.playChime(523.25);
      }
    });
  });
</script>
@endpush
