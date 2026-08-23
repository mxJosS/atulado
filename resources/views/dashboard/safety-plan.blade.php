@extends('layouts.app')

@section('title', 'Mi Plan de Seguridad Personal')

@section('content')
<div style="max-width: 960px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sage-base);">— PROTOCOLO DE RESPALDO</span>
      <h1 style="font-size: 1.85rem; margin-top: 0.15rem; color: var(--text-near-black);">Mi Plan de Seguridad Digital</h1>
      <p style="color: var(--text-medium-gray); font-size: 0.9rem;">Un documento vivo basado en evidencia para acompañarte y protegerte en momentos de alta vulnerabilidad.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
      <a href="{{ route('safety-plan.print') }}" target="_blank" class="btn btn-secondary btn-sm" style="gap: 6px;">
        <i class="fa-solid fa-print"></i>
        <span>Imprimir / PDF</span>
      </a>
      <a href="tel:8002900024" class="btn btn-crisis btn-sm" style="gap: 6px;">
        <i class="fa-solid fa-phone"></i>
        <span>SOS 800 290 0024</span>
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('safety-plan.update') }}">
    @csrf
    @method('PUT')

    <!-- PASO 1: SEÑALES DE ALERTA -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid var(--clinical-red);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-crisis" style="font-size: 0.78rem;">Paso 1</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Mis Señales de Alerta Tempranas</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-medium-gray); margin-bottom: 1rem;">
          ¿Qué pensamientos, sensaciones físicas, imágenes o conductas te indican que una tormenta emocional está comenzando?
        </p>

        <div id="warningSignsContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $warningSigns = old('warning_signs', $safetyPlan->warning_signs ?? ['Pensamientos de no ser suficiente', 'Aislamiento y no responder mensajes', 'Tensión constante en el cuerpo']);
          @endphp
          @foreach($warningSigns as $ws)
            <div style="display: flex; gap: 0.6rem; align-items: center;">
              <i class="fa-solid fa-triangle-exclamation" style="color: var(--clinical-red); font-size: 0.9rem;"></i>
              <input type="text" name="warning_signs[]" value="{{ $ws }}" class="form-control" placeholder="Ej. Insomnio severo, dolor en el pecho, rumiación mental...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addWarningSign()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem; gap: 4px;">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar otra señal</span>
        </button>
      </div>
    </div>

    <!-- PASO 2: ESTRATEGIAS INTERNAS -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid var(--sage-base);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-sage" style="font-size: 0.78rem;">Paso 2</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Estrategias Internas de Afrontamiento</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-medium-gray); margin-bottom: 1rem;">
          Cosas que puedes hacer por ti mismo(a) para bajar la intensidad sin depender de nadie más en ese instante.
        </p>

        <div id="internalCopingContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $coping = old('internal_coping', $safetyPlan->internal_coping ?? ['Respiración 4-7-8 por 4 minutos', 'Ducha de agua fría/tibia consciente', 'Salir a caminar sin celular']);
          @endphp
          @foreach($coping as $cp)
            <div style="display: flex; gap: 0.6rem; align-items: center;">
              <i class="fa-solid fa-shield" style="color: var(--sage-base); font-size: 0.9rem;"></i>
              <input type="text" name="internal_coping[]" value="{{ $cp }}" class="form-control" placeholder="Ej. Ejercicio de respiración 4-7-8, técnica 5-4-3-2-1, dibujar...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addInternalCoping()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem; gap: 4px;">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar otra estrategia</span>
        </button>
      </div>
    </div>

    <!-- PASO 3: PERSONAS Y LUGARES DE DISTRACCIÓN -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid var(--violet-base);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-violet" style="font-size: 0.78rem;">Paso 3</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Lugares y Contactos de Distracción Social</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-medium-gray); margin-bottom: 1rem;">
          Personas o entornos que te ayudan a desconectar la mente sin necesidad de hablar de la crisis.
        </p>

        <div class="responsive-two-col">
          <div>
            <label class="form-label" style="font-size: 0.82rem;">Personas que te distraen:</label>
            <div id="socialDistractionsContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
              @php
                $distractions = old('social_distractions', $safetyPlan->social_distractions ?? ['Llamar a mi hermano(a)', 'Platicar de series con mi amigo(a)']);
              @endphp
              @foreach($distractions as $ds)
                <input type="text" name="social_distractions[]" value="{{ $ds }}" class="form-control" placeholder="Nombre de persona...">
              @endforeach
            </div>
            <button type="button" onclick="addSocialDistraction()" class="btn btn-sm btn-secondary" style="margin-top: 0.5rem; font-size: 0.75rem;">+ Persona</button>
          </div>

          <div>
            <label class="form-label" style="font-size: 0.82rem;">Lugares seguros:</label>
            <div id="safePlacesContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
              @php
                $places = old('safe_places', $safetyPlan->safe_places ?? ['El parque cerca de casa', 'La cafetería de la esquina', 'Mi recámara con música']);
              @endphp
              @foreach($places as $pl)
                <input type="text" name="safe_places[]" value="{{ $pl }}" class="form-control" placeholder="Lugar donde te sientes en paz...">
              @endforeach
            </div>
            <button type="button" onclick="addSafePlace()" class="btn btn-sm btn-secondary" style="margin-top: 0.5rem; font-size: 0.75rem;">+ Lugar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PASO 4: RED DE APOYO DE CONFIANZA -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid #8A7332;">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-gold" style="font-size: 0.78rem;">Paso 4</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Red de Apoyo Personal (A quién pedir auxilio)</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-medium-gray); margin-bottom: 1rem;">
          Personas a las que puedes decirles explícitamente: <em>"Estoy pasando por un momento muy difícil y necesito que me acompañes"</em>.
        </p>

        <div id="trustedContactsContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
          @php
            $contacts = old('trusted_contacts', $safetyPlan->trusted_contacts ?? [
              ['name' => 'Carlos (Amigo)', 'phone' => '55 1234 5678', 'relationship' => 'Amigo de confianza'],
              ['name' => 'Mamá', 'phone' => '55 9876 5432', 'relationship' => 'Familia directa']
            ]);
          @endphp
          @foreach($contacts as $idx => $tc)
            <div class="trusted-contact-grid" style="background: var(--bg-subtle); padding: 0.75rem; border-radius: var(--radius-sm);">
              <input type="text" name="trusted_contacts[{{ $idx }}][name]" value="{{ $tc['name'] ?? '' }}" class="form-control" placeholder="Nombre completo">
              <input type="text" name="trusted_contacts[{{ $idx }}][phone]" value="{{ $tc['phone'] ?? '' }}" class="form-control" placeholder="Teléfono de contacto">
              <input type="text" name="trusted_contacts[{{ $idx }}][relationship]" value="{{ $tc['relationship'] ?? '' }}" class="form-control" placeholder="Vínculo / Relación">
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- PASO 5: RAZONES PARA VIVIR -->
    <div class="card" style="margin-bottom: 2rem; border-top: 4px solid var(--mint-accent); background: linear-gradient(145deg, #FFFFFF, var(--sage-pale));">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-mint" style="font-size: 0.78rem;">Paso 5</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Mis Razones para Vivir y Seguir Adelante</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-near-black); margin-bottom: 1rem;">
          Tus anclas de sentido: seres queridos, mascotas, proyectos futuros, metas personales o curiosidad por el mañana.
        </p>

        <div id="reasonsToLiveContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $reasons = old('reasons_to_live', $safetyPlan->reasons_to_live ?? ['Mi mascota que depende de mí con amor incondicional', 'Terminar mi carrera y ver mundo', 'Estar presente para las personas que me quieren']);
          @endphp
          @foreach($reasons as $rs)
            <div style="display: flex; gap: 0.6rem; align-items: center;">
              <i class="fa-solid fa-heart" style="color: var(--sage-base); font-size: 0.9rem;"></i>
              <input type="text" name="reasons_to_live[]" value="{{ $rs }}" class="form-control" style="background: #ffffff;" placeholder="Ej. Mi perro, el proyecto que estoy construyendo...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addReasonToLive()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem; gap: 4px;">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar otra razón de vida</span>
        </button>
      </div>
    </div>

    <!-- SAVE SUBMIT -->
    <div style="text-align: right; margin-bottom: 3rem;">
      <button type="submit" class="btn btn-primary btn-lg" style="gap: 8px;">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Guardar cambios del Plan de Seguridad</span>
      </button>
    </div>
  </form>

</div>
@endsection

@push('scripts')
<script>
  function addWarningSign() {
    const c = document.getElementById('warningSignsContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--clinical-red); font-size: 0.9rem;"></i><input type="text" name="warning_signs[]" class="form-control" placeholder="Nueva señal de alerta...">';
    c.appendChild(div);
  }

  function addInternalCoping() {
    const c = document.getElementById('internalCopingContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = '<i class="fa-solid fa-shield" style="color: var(--sage-base); font-size: 0.9rem;"></i><input type="text" name="internal_coping[]" class="form-control" placeholder="Nueva estrategia de afrontamiento...">';
    c.appendChild(div);
  }

  function addSocialDistraction() {
    const c = document.getElementById('socialDistractionsContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'social_distractions[]';
    input.className = 'form-control';
    input.placeholder = 'Nombre de persona...';
    c.appendChild(input);
  }

  function addSafePlace() {
    const c = document.getElementById('safePlacesContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'safe_places[]';
    input.className = 'form-control';
    input.placeholder = 'Lugar seguro...';
    c.appendChild(input);
  }

  function addReasonToLive() {
    const c = document.getElementById('reasonsToLiveContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = '<i class="fa-solid fa-heart" style="color: var(--sage-base); font-size: 0.9rem;"></i><input type="text" name="reasons_to_live[]" class="form-control" style="background: #ffffff;" placeholder="Nueva razón para seguir...">';
    c.appendChild(div);
  }
</script>
@endpush
