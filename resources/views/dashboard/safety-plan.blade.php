@extends('layouts.app')

@section('title', 'Mi Plan de Seguridad Personal')

@section('content')
<div style="max-width: 960px; margin: 0 auto;">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="mono-tag" style="color: var(--sky-600);">Protocolo de Respaldo</span>
      <h1 style="font-size: 2rem; margin-top: 0.2rem;">Mi Plan de Seguridad Digital</h1>
      <p style="color: var(--ink-600); font-size: 0.95rem;">Un documento vivo basado en evidencia para acompañarte y protegerte en momentos de alta vulnerabilidad.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
      <a href="{{ route('safety-plan.print') }}" target="_blank" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.4rem;">
        <span>🖨️</span>
        <span>Imprimir / PDF</span>
      </a>
      <a href="tel:8002900024" class="btn btn-crisis btn-sm">
        <span class="nav-crisis-dot"></span>
        <span>SOS 800 290 0024</span>
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('safety-plan.update') }}">
    @csrf
    @method('PUT')

    <!-- PASO 1: SEÑALES DE ALERTA -->
    <div class="card" style="margin-bottom: 1.75rem; border-top: 4px solid var(--terra-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
          <span class="badge badge-terra" style="font-size: 0.8rem;">Paso 1</span>
          <h2 style="font-size: 1.25rem; margin: 0;">Mis Señales de Alerta Tempranas</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--ink-600); margin-bottom: 1.25rem;">
          ¿Qué pensamientos, sensaciones físicas, imágenes o conductas te indican que una tormenta emocional está comenzando?
        </p>

        <div id="warningSignsContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $warningSigns = old('warning_signs', $safetyPlan->warning_signs ?? ['Pensamientos de no ser suficiente', 'Aislamiento y no responder mensajes', 'Tensión constante en el cuerpo']);
          @endphp
          @foreach($warningSigns as $ws)
            <div style="display: flex; gap: 0.5rem; align-items: center;">
              <span style="color: var(--terra-500); font-weight: 700;">•</span>
              <input type="text" name="warning_signs[]" value="{{ $ws }}" class="form-control" placeholder="Ej. Insomnio severo, dolor en el pecho, rumiación mental...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addWarningSign()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem;">
          + Agregar otra señal
        </button>
      </div>
    </div>

    <!-- PASO 2: ESTRATEGIAS INTERNAS -->
    <div class="card" style="margin-bottom: 1.75rem; border-top: 4px solid var(--sage-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
          <span class="badge badge-sage" style="font-size: 0.8rem;">Paso 2</span>
          <h2 style="font-size: 1.25rem; margin: 0;">Estrategias Internas de Afrontamiento</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--ink-600); margin-bottom: 1.25rem;">
          Cosas que puedes hacer por ti mismo(a) para bajar la intensidad sin necesitar a nadie más en ese instante.
        </p>

        <div id="internalCopingContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $coping = old('internal_coping', $safetyPlan->internal_coping ?? ['Respiración 4-7-8 por 4 minutos', 'Ducha de agua fría/tibia consciente', 'Salir a caminar sin celular']);
          @endphp
          @foreach($coping as $cp)
            <div style="display: flex; gap: 0.5rem; align-items: center;">
              <span style="color: var(--sage-500); font-weight: 700;">•</span>
              <input type="text" name="internal_coping[]" value="{{ $cp }}" class="form-control" placeholder="Ej. Ejercicio de respiración 4-7-8, técnica 5-4-3-2-1, dibujar...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addInternalCoping()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem;">
          + Agregar otra estrategia
        </button>
      </div>
    </div>

    <!-- PASO 3: PERSONAS Y LUGARES DE DISTRACCIÓN -->
    <div class="card" style="margin-bottom: 1.75rem; border-top: 4px solid var(--lav-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
          <span class="badge badge-lav" style="font-size: 0.8rem;">Paso 3</span>
          <h2 style="font-size: 1.25rem; margin: 0;">Lugares y Actividades para Distraer la Mente</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--ink-600); margin-bottom: 1.25rem;">
          Entornos sociales sanos o actividades que te ayuden a pausar el bucle de pensamientos.
        </p>

        <div id="distractionsContainer" style="display: flex; flex-direction: column; gap: 0.6rem;">
          @php
            $distractions = old('distraction_activities', $safetyPlan->distraction_activities ?? ['Ir a una cafetería tranquila', 'Pasear en el parque', 'Escuchar mi playlist acústica']);
          @endphp
          @foreach($distractions as $ds)
            <div style="display: flex; gap: 0.5rem; align-items: center;">
              <span style="color: var(--lav-500); font-weight: 700;">•</span>
              <input type="text" name="distraction_activities[]" value="{{ $ds }}" class="form-control" placeholder="Ej. Ir al parque, ver una película reconfortante...">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addDistraction()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem;">
          + Agregar otra actividad/lugar
        </button>
      </div>
    </div>

    <!-- PASO 4: CONTACTOS DE CONFIANZA -->
    <div class="card" style="margin-bottom: 1.75rem; border-top: 4px solid var(--sky-500);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
          <span class="badge badge-sky" style="font-size: 0.8rem;">Paso 4</span>
          <h2 style="font-size: 1.25rem; margin: 0;">Personas de Apoyo a Quienes Puedo Acudir</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--ink-600); margin-bottom: 1.25rem;">
          Familiares, amigos o mentores con quienes puedes hablar abiertamente y pedir compañía.
        </p>

        <div id="trustedContactsContainer" style="display: flex; flex-direction: column; gap: 0.85rem;">
          @php
            $trusted = old('trusted_contacts', $safetyPlan->trusted_contacts ?? [
              ['name' => 'Sofía López', 'phone' => '55 9876 5432', 'relationship' => 'Hermana'],
              ['name' => 'Carlos M.', 'phone' => '55 1122 3344', 'relationship' => 'Amigo'],
            ]);
          @endphp
          @foreach($trusted as $idx => $tc)
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.6rem; background: var(--bg-subtle); padding: 0.75rem; border-radius: var(--radius-md);">
              <input type="text" name="trusted_contacts[{{ $idx }}][name]" value="{{ $tc['name'] ?? '' }}" class="form-control" placeholder="Nombre">
              <input type="text" name="trusted_contacts[{{ $idx }}][phone]" value="{{ $tc['phone'] ?? '' }}" class="form-control" placeholder="Teléfono">
              <input type="text" name="trusted_contacts[{{ $idx }}][relationship]" value="{{ $tc['relationship'] ?? '' }}" class="form-control" placeholder="Parentesco">
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addTrustedContact()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem;">
          + Agregar otra persona de apoyo
        </button>
      </div>
    </div>

    <!-- PASO 5: RAZONES PARA VIVIR -->
    <div class="card" style="margin-bottom: 1.75rem; border-top: 4px solid var(--amber-500); background: var(--amber-50);">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
          <span class="badge badge-amber" style="font-size: 0.8rem;">Paso 5</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--amber-800);">Mis Razones para Vivir y Mis Anclas de Esperanza</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--amber-800); margin-bottom: 1rem;">
          ¿Qué personas, proyectos, mascotas, metas, recuerdos o esperanzas le dan sentido a tu vida y te recuerdan por qué vale la pena continuar?
        </p>

        <textarea 
          name="reasons_for_living" 
          id="reasons_for_living" 
          rows="4" 
          class="form-control" 
          style="background: #ffffff;" 
          placeholder="Escribe aquí tus razones más profundas... (Mi familia, mi mascota, ver el mar nuevamente, mis sueños por cumplir)"
        >{{ old('reasons_for_living', $safetyPlan->reasons_for_living) }}</textarea>
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
      <button type="submit" class="btn btn-primary btn-lg">
        <span>Guardar mi Plan de Seguridad</span>
        <span>🛡️</span>
      </button>
    </div>
  </form>

</div>
@endsection

@push('scripts')
<script>
  function addWarningSign() {
    const container = document.getElementById('warningSignsContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.5rem; align-items: center;';
    div.innerHTML = '<span style="color: var(--terra-500); font-weight: 700;">•</span><input type="text" name="warning_signs[]" class="form-control" placeholder="Escribe otra señal de alerta...">';
    container.appendChild(div);
  }

  function addInternalCoping() {
    const container = document.getElementById('internalCopingContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.5rem; align-items: center;';
    div.innerHTML = '<span style="color: var(--sage-500); font-weight: 700;">•</span><input type="text" name="internal_coping[]" class="form-control" placeholder="Escribe otra estrategia interna...">';
    container.appendChild(div);
  }

  function addDistraction() {
    const container = document.getElementById('distractionsContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.5rem; align-items: center;';
    div.innerHTML = '<span style="color: var(--lav-500); font-weight: 700;">•</span><input type="text" name="distraction_activities[]" class="form-control" placeholder="Escribe otro lugar o actividad...">';
    container.appendChild(div);
  }

  let trustedIndex = 50;
  function addTrustedContact() {
    const container = document.getElementById('trustedContactsContainer');
    const div = document.createElement('div');
    div.style = 'display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.6rem; background: var(--bg-subtle); padding: 0.75rem; border-radius: var(--radius-md);';
    div.innerHTML = `
      <input type="text" name="trusted_contacts[${trustedIndex}][name]" class="form-control" placeholder="Nombre">
      <input type="text" name="trusted_contacts[${trustedIndex}][phone]" class="form-control" placeholder="Teléfono">
      <input type="text" name="trusted_contacts[${trustedIndex}][relationship]" class="form-control" placeholder="Parentesco">
    `;
    container.appendChild(div);
    trustedIndex++;
  }
</script>
@endpush
