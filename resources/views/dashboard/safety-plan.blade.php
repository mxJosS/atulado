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
              <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar señal">
                <i class="fa-solid fa-trash"></i>
              </button>
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
              <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar estrategia">
                <i class="fa-solid fa-trash"></i>
              </button>
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
            <label class="form-label" style="font-size: 0.82rem; font-weight: 600; margin-bottom: 0.5rem; display: block;">Personas que te distraen:</label>
            <div id="socialDistractionsContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
              @php
                $distractions = old('social_distractions', $safetyPlan->social_distractions ?? ['Llamar a mi hermano(a)', 'Platicar de series con mi amigo(a)']);
              @endphp
              @foreach($distractions as $ds)
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="text" name="social_distractions[]" value="{{ $ds }}" class="form-control" placeholder="Nombre de persona...">
                  <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              @endforeach
            </div>
            <button type="button" onclick="addSocialDistraction()" class="btn btn-sm btn-secondary" style="margin-top: 0.65rem; font-size: 0.75rem; gap: 4px;">
              <i class="fa-solid fa-plus"></i>
              <span>Agregar persona</span>
            </button>
          </div>

          <div>
            <label class="form-label" style="font-size: 0.82rem; font-weight: 600; margin-bottom: 0.5rem; display: block;">Lugares seguros:</label>
            <div id="safePlacesContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
              @php
                $places = old('safe_places', $safetyPlan->safe_places ?? ['El parque cerca de casa', 'La cafetería de la esquina', 'Mi recámara con música']);
              @endphp
              @foreach($places as $pl)
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="text" name="safe_places[]" value="{{ $pl }}" class="form-control" placeholder="Lugar donde te sientes en paz...">
                  <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              @endforeach
            </div>
            <button type="button" onclick="addSafePlace()" class="btn btn-sm btn-secondary" style="margin-top: 0.65rem; font-size: 0.75rem; gap: 4px;">
              <i class="fa-solid fa-plus"></i>
              <span>Agregar lugar</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PASO 4: RED DE APOYO DE CONFIANZA (ITEM 6: ACCIONES LLAMADA & WHATSAPP) -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid #8A7332;">
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
          <span class="badge badge-gold" style="font-size: 0.78rem;">Paso 4</span>
          <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-near-black);">Red de Apoyo Personal (A quién pedir auxilio)</h2>
        </div>
        <p style="font-size: 0.86rem; color: var(--text-medium-gray); margin-bottom: 1rem;">
          Personas a las que puedes decirles explícitamente: <em>"Estoy pasando por un momento muy difícil y necesito que me acompañes"</em>.
        </p>

        <div id="trustedContactsContainer" style="display: flex; flex-direction: column; gap: 0.85rem;">
          @php
            $contacts = old('trusted_contacts', $safetyPlan->trusted_contacts ?? [
              ['name' => 'Carlos (Amigo)', 'phone' => '55 1234 5678', 'relationship' => 'Amigo de confianza'],
              ['name' => 'Mamá', 'phone' => '55 9876 5432', 'relationship' => 'Familia directa']
            ]);
          @endphp
          @foreach($contacts as $idx => $tc)
            @php
              $phoneClean = preg_replace('/[^0-9]/', '', $tc['phone'] ?? '');
              $waMessage = urlencode("Necesito ayuda, ¿podemos hablar?");
            @endphp
            <div class="trusted-contact-card" data-index="{{ $idx }}">
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.65rem; margin-bottom: 0.75rem;">
                <div>
                  <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Nombre:</label>
                  <input type="text" name="trusted_contacts[{{ $idx }}][name]" value="{{ $tc['name'] ?? '' }}" class="form-control" placeholder="Nombre completo" style="font-size: 0.86rem;">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Teléfono:</label>
                  <input type="text" name="trusted_contacts[{{ $idx }}][phone]" value="{{ $tc['phone'] ?? '' }}" class="form-control contact-phone-input" placeholder="Ej. 5512345678" style="font-size: 0.86rem;" oninput="updateContactActions(this)">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Vínculo / Relación:</label>
                  <input type="text" name="trusted_contacts[{{ $idx }}][relationship]" value="{{ $tc['relationship'] ?? '' }}" class="form-control" placeholder="Amigo, Pareja, Familiar..." style="font-size: 0.86rem;">
                </div>
              </div>

              <!-- ════ CAMPO ACCIONES (ITEM 6 ESPECÍFICO) ════ -->
              <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 1px dashed #DCE8E0; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-family: var(--font-mono); font-size: 0.72rem; color: #556860; font-weight: 700; text-transform: uppercase;">Acciones:</span>
                  
                  <!-- 1. LLAMADA DIRECTA -->
                  <a href="tel:{{ $phoneClean ?: '8002900024' }}" class="btn-call action-call-btn" title="Llamar directamente a este contacto">
                    <i class="fa-solid fa-phone"></i>
                    <span>Llamar</span>
                  </a>

                  <!-- 2. WHATSAPP CON MENSAJE PRELLENADO "Necesito ayuda, ¿podemos hablar?" -->
                  <a href="https://wa.me/{{ $phoneClean ? (str_starts_with($phoneClean, '52') ? $phoneClean : '52'.$phoneClean) : '' }}?text={{ $waMessage }}" target="_blank" class="btn-whatsapp action-wa-btn" title="Enviar WhatsApp de auxilio">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1rem;"></i>
                    <span class="wa-btn-label">WhatsApp</span>
                  </a>
                </div>

                <!-- 3. BOTÓN ELIMINAR CONTACTO -->
                <button type="button" onclick="this.closest('.trusted-contact-card').remove()" class="btn-delete-item" title="Eliminar este contacto">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          @endforeach
        </div>

        <button type="button" onclick="addTrustedContact()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem; gap: 4px;">
          <i class="fa-solid fa-user-plus"></i>
          <span>Agregar otro contacto de confianza</span>
        </button>
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
              <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar razón de vida">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          @endforeach
        </div>
        <button type="button" onclick="addReasonToLive()" class="btn btn-sm btn-secondary" style="margin-top: 0.85rem; font-size: 0.78rem; gap: 4px;">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar otra razón de vida</span>
        </button>
      </div>
    </div>

    <!-- SAVE SUBMIT (ITEM 6: ETIQUETA "Guardar cambios") -->
    <div style="text-align: right; margin-bottom: 3rem;">
      <button type="submit" class="btn btn-primary btn-lg" style="gap: 8px; font-size: 1rem; padding: 0.85rem 1.75rem;">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Guardar cambios</span>
      </button>
    </div>
  </form>

</div>
@endsection

@push('scripts')
<script>
  let contactCounter = {{ count($contacts ?? []) }};

  function addWarningSign() {
    const c = document.getElementById('warningSignsContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = `
      <i class="fa-solid fa-triangle-exclamation" style="color: var(--clinical-red); font-size: 0.9rem;"></i>
      <input type="text" name="warning_signs[]" class="form-control" placeholder="Nueva señal de alerta...">
      <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar señal">
        <i class="fa-solid fa-trash"></i>
      </button>
    `;
    c.appendChild(div);
  }

  function addInternalCoping() {
    const c = document.getElementById('internalCopingContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = `
      <i class="fa-solid fa-shield" style="color: var(--sage-base); font-size: 0.9rem;"></i>
      <input type="text" name="internal_coping[]" class="form-control" placeholder="Nueva estrategia de afrontamiento...">
      <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar estrategia">
        <i class="fa-solid fa-trash"></i>
      </button>
    `;
    c.appendChild(div);
  }

  function addSocialDistraction() {
    const c = document.getElementById('socialDistractionsContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.5rem; align-items: center;';
    div.innerHTML = `
      <input type="text" name="social_distractions[]" class="form-control" placeholder="Nombre de persona...">
      <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar">
        <i class="fa-solid fa-trash"></i>
      </button>
    `;
    c.appendChild(div);
  }

  function addSafePlace() {
    const c = document.getElementById('safePlacesContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.5rem; align-items: center;';
    div.innerHTML = `
      <input type="text" name="safe_places[]" class="form-control" placeholder="Lugar seguro...">
      <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar">
        <i class="fa-solid fa-trash"></i>
      </button>
    `;
    c.appendChild(div);
  }

  function addReasonToLive() {
    const c = document.getElementById('reasonsToLiveContainer');
    const div = document.createElement('div');
    div.style = 'display: flex; gap: 0.6rem; align-items: center;';
    div.innerHTML = `
      <i class="fa-solid fa-heart" style="color: var(--sage-base); font-size: 0.9rem;"></i>
      <input type="text" name="reasons_to_live[]" class="form-control" style="background: #ffffff;" placeholder="Nueva razón para seguir...">
      <button type="button" onclick="this.parentElement.remove()" class="btn-delete-item" title="Eliminar razón de vida">
        <i class="fa-solid fa-trash"></i>
      </button>
    `;
    c.appendChild(div);
  }

  function addTrustedContact() {
    const c = document.getElementById('trustedContactsContainer');
    const card = document.createElement('div');
    card.className = 'trusted-contact-card';
    card.setAttribute('data-index', contactCounter);
    
    const waMessage = encodeURIComponent("Necesito ayuda, ¿podemos hablar?");

    card.innerHTML = `
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.65rem; margin-bottom: 0.75rem;">
        <div>
          <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Nombre:</label>
          <input type="text" name="trusted_contacts[${contactCounter}][name]" class="form-control" placeholder="Nombre completo" style="font-size: 0.86rem;">
        </div>
        <div>
          <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Teléfono:</label>
          <input type="text" name="trusted_contacts[${contactCounter}][phone]" class="form-control contact-phone-input" placeholder="Ej. 5512345678" style="font-size: 0.86rem;" oninput="updateContactActions(this)">
        </div>
        <div>
          <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.2rem;">Vínculo / Relación:</label>
          <input type="text" name="trusted_contacts[${contactCounter}][relationship]" class="form-control" placeholder="Amigo, Pareja, Familiar..." style="font-size: 0.86rem;">
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 1px dashed #DCE8E0; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <span style="font-family: var(--font-mono); font-size: 0.72rem; color: #556860; font-weight: 700; text-transform: uppercase;">Acciones:</span>
          <a href="tel:" class="btn-call action-call-btn" title="Llamar directamente">
            <i class="fa-solid fa-phone"></i>
            <span>Llamar</span>
          </a>
          <a href="https://wa.me/?text=${waMessage}" target="_blank" class="btn-whatsapp action-wa-btn" title="Enviar WhatsApp de auxilio">
            <i class="fa-brands fa-whatsapp" style="font-size: 1rem;"></i>
            <span class="wa-btn-label">WhatsApp</span>
          </a>
        </div>
        <button type="button" onclick="this.closest('.trusted-contact-card').remove()" class="btn-delete-item" title="Eliminar este contacto">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    `;

    c.appendChild(card);
    contactCounter++;
  }

  function updateContactActions(phoneInput) {
    const card = phoneInput.closest('.trusted-contact-card');
    if (!card) return;

    const rawPhone = phoneInput.value.replace(/[^0-9]/g, '');
    const callBtn = card.querySelector('.action-call-btn');
    const waBtn = card.querySelector('.action-wa-btn');
    const waMessage = encodeURIComponent("Necesito ayuda, ¿podemos hablar?");

    if (callBtn) {
      callBtn.href = rawPhone ? `tel:${rawPhone}` : 'tel:8002900024';
    }

    if (waBtn) {
      const waNumber = rawPhone.startsWith('52') ? rawPhone : (rawPhone ? '52' + rawPhone : '');
      waBtn.href = waNumber ? `https://wa.me/${waNumber}?text=${waMessage}` : `https://wa.me/?text=${waMessage}`;
    }
  }
</script>
@endpush
