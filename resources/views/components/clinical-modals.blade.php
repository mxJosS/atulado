<!-- ════ COMPONENTE DE MODALES DEL MOTOR CLÍNICO (CAPAS 1, 2, 3 Y CONTENCIÓN) ════ -->

<!-- 1. MODAL CAPA 1: WHO-5 (ÍNDICE DE BIENESTAR DE LA OMS) -->
<div id="who5ModalOverlay" class="clinical-modal-overlay" style="display: none;">
  <div class="clinical-modal-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
      <div>
        <span class="mono-tag" style="color: #2E5D4B; font-size: 0.72rem;">— EVALUACIÓN DE BIENESTAR (OMS)</span>
        <h2 style="font-size: 1.35rem; color: #1A2620; margin-top: 0.25rem; font-family: var(--font-display);">Índice de Bienestar WHO-5</h2>
      </div>
      <button type="button" onclick="closeClinicalModals()" class="btn btn-sm btn-secondary" style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;" aria-label="Cerrar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Encabezado Obligatorio de la OMS -->
    <div style="background: #FAF8F2; border-left: 4px solid #C8B87A; padding: 0.85rem 1rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem; font-size: 0.92rem; color: #4A3B18; font-weight: 500;">
      <i class="fa-regular fa-clock" style="margin-right: 6px;"></i> <strong>Durante las últimas dos semanas...</strong>
    </div>

    <form id="who5Form">
      @csrf
      <input type="hidden" name="origen" id="who5OrigenInput" value="programada">

      @php
        $who5Questions = [
          1 => 'Me he sentido alegre y de buen humor',
          2 => 'Me he sentido tranquilo y relajado',
          3 => 'Me he sentido activo y enérgico',
          4 => 'Me he despertado fresco y descansado',
          5 => 'Mi vida cotidiana ha estado llena de cosas que me interesan',
        ];
        $who5Scale = [
          5 => 'Todo el tiempo',
          4 => 'La mayor parte',
          3 => 'Más de la mitad',
          2 => 'Menos de la mitad',
          1 => 'De vez en cuando',
          0 => 'Nunca',
        ];
      @endphp

      @foreach($who5Questions as $num => $text)
        <div class="clinical-question-item">
          <div style="font-weight: 600; font-size: 0.92rem; color: #1A2620; margin-bottom: 0.5rem;">
            {{ $num }}. {{ $text }}
          </div>
          <div class="clinical-options-grid">
            @foreach($who5Scale as $val => $label)
              <label class="clinical-radio-btn" onclick="selectClinicalRadio(this)">
                <input type="radio" name="i{{ $num }}" value="{{ $val }}" required style="display: none;">
                <span style="font-weight: 700; font-size: 0.95rem;">{{ $val }}</span>
                <span style="font-size: 0.72rem; line-height: 1.2; margin-top: 2px;">{{ $label }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @endforeach

      <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; justify-content: flex-end;">
        <button type="button" onclick="closeClinicalModals()" class="btn btn-secondary">Cancelar</button>
        <button type="submit" id="who5SubmitBtn" class="btn btn-primary" style="gap: 8px;">
          <span>Continuar</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 2. MODAL CAPA 2: MDI (MAJOR DEPRESSION INVENTORY) -->
<div id="mdiModalOverlay" class="clinical-modal-overlay" style="display: none;">
  <div class="clinical-modal-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
      <div>
        <span class="mono-tag" style="color: #2E5D4B; font-size: 0.72rem;">— CUESTIONARIO COMPLEMENTARIO</span>
        <h2 style="font-size: 1.35rem; color: #1A2620; margin-top: 0.25rem; font-family: var(--font-display);">Evaluación de Estado de Ánimo (MDI)</h2>
      </div>
      <button type="button" onclick="closeClinicalModals()" class="btn btn-sm btn-secondary" style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;" aria-label="Cerrar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="background: #F4F9F6; border-left: 4px solid #2E5D4B; padding: 0.85rem 1rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem; font-size: 0.9rem; color: #1E4A25;">
      Durante las últimas dos semanas, ¿con qué frecuencia has experimentado lo siguiente?
    </div>

    <form id="mdiForm">
      @csrf
      @php
        $mdiQuestions = [
          'i1' => '1. ¿Se ha sentido triste o con el ánimo bajo?',
          'i2' => '2. ¿Ha perdido interés en sus actividades cotidianas?',
          'i3' => '3. ¿Se ha sentido falto de energía y fuerzas?',
          'i4' => '4. ¿Se ha sentido con menos confianza en sí mismo(a)?',
          'i5' => '5. ¿Ha tenido mala conciencia o sentimientos de culpa?',
          'i6' => '6. ¿Ha sentido que la vida no valía la pena vivirla?',
          'i7' => '7. ¿Ha tenido dificultad para concentrarse?',
          'i8a' => '8a. ¿Se ha sentido inquieto(a)?',
          'i8b' => '8b. ¿Se ha sentido lento(a) o inhibido(a)?',
          'i9' => '9. ¿Ha tenido dificultades para dormir?',
          'i10a' => '10a. ¿Ha tenido menos apetito?',
          'i10b' => '10b. ¿Ha tenido más apetito?',
        ];
        $mdiScale = [
          0 => 'Nunca',
          1 => 'De vez en cuando',
          2 => 'Menos de mitad',
          3 => 'Más de mitad',
          4 => 'Casi siempre',
          5 => 'Todo el tiempo',
        ];
      @endphp

      @foreach($mdiQuestions as $key => $title)
        <div class="clinical-question-item">
          <div style="font-weight: 600; font-size: 0.9rem; color: #1A2620; margin-bottom: 0.45rem;">
            {{ $title }}
          </div>
          <div class="clinical-options-grid">
            @foreach($mdiScale as $val => $label)
              <label class="clinical-radio-btn" onclick="selectClinicalRadio(this)">
                <input type="radio" name="{{ $key }}" value="{{ $val }}" required style="display: none;">
                <span style="font-weight: 700; font-size: 0.9rem;">{{ $val }}</span>
                <span style="font-size: 0.7rem; line-height: 1.15; margin-top: 2px;">{{ $label }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @endforeach

      <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; justify-content: flex-end;">
        <button type="button" onclick="closeClinicalModals()" class="btn btn-secondary">Cancelar</button>
        <button type="submit" id="mdiSubmitBtn" class="btn btn-primary" style="gap: 8px;">
          <span>Continuar</span>
          <i class="fa-solid fa-arrow-right"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 3. MODAL CAPA 3: ASQ (NIMH - ASK SUICIDE-SCREENING QUESTIONS) -->
<div id="asqModalOverlay" class="clinical-modal-overlay" style="display: none;">
  <div class="clinical-modal-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
      <div>
        <span class="mono-tag" style="color: #922B21; font-size: 0.72rem;">— PROTOCOLO DE SEGURIDAD</span>
        <h2 style="font-size: 1.35rem; color: #1A2620; margin-top: 0.25rem; font-family: var(--font-display);">Preguntas de Acompañamiento Personal</h2>
      </div>
    </div>

    <div style="background: #FDF2E9; border-left: 4px solid #E67E22; padding: 0.85rem 1rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem; font-size: 0.88rem; color: #783E10; line-height: 1.5;">
      Tu seguridad y tranquilidad son nuestra prioridad absoluta. Responde con total confianza y libertad.
    </div>

    <form id="asqForm">
      @csrf
      @php
        $asqQuestions = [
          'p1' => ['q' => '1. ¿Ha deseado estar muerto(a)?', 'period' => 'En las últimas semanas'],
          'p2' => ['q' => '2. ¿Ha sentido que usted o su familia estarían mejor si estuviera muerto(a)?', 'period' => 'En las últimas semanas'],
          'p3' => ['q' => '3. ¿Ha estado pensando en suicidarse?', 'period' => 'En la última semana'],
          'p4' => ['q' => '4. ¿Alguna vez ha intentado suicidarse?', 'period' => 'A lo largo de la vida'],
        ];
      @endphp

      @foreach($asqQuestions as $pKey => $pData)
        <div class="clinical-question-item">
          <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 4px;">
            <span style="font-weight: 600; font-size: 0.92rem; color: #1A2620;">{{ $pData['q'] }}</span>
            <span class="badge badge-sage" style="font-size: 0.72rem;">{{ $pData['period'] }}</span>
          </div>
          <div class="clinical-options-grid" style="grid-template-columns: repeat(3, 1fr);">
            <label class="clinical-radio-btn" onclick="selectClinicalRadio(this); checkAsqP5Visibility();">
              <input type="radio" name="{{ $pKey }}" value="si" required style="display: none;">
              <span style="font-weight: 600;">Sí</span>
            </label>
            <label class="clinical-radio-btn" onclick="selectClinicalRadio(this); checkAsqP5Visibility();">
              <input type="radio" name="{{ $pKey }}" value="no" required style="display: none;">
              <span style="font-weight: 600;">No</span>
            </label>
            <label class="clinical-radio-btn" onclick="selectClinicalRadio(this); checkAsqP5Visibility();">
              <input type="radio" name="{{ $pKey }}" value="prefiero_no_contestar" required style="display: none;">
              <span style="font-weight: 500; font-size: 0.75rem;">Prefiero no contestar</span>
            </label>
          </div>
        </div>
      @endforeach

      <!-- Pregunta Condicional P5 (Agudeza) -->
      <div id="asqP5Container" class="clinical-question-item" style="display: none; background: #FFF5F5; border-color: #FFA59C;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 4px;">
          <span style="font-weight: 700; font-size: 0.94rem; color: #922B21;">5. ¿Está pensando en suicidarse en este momento?</span>
          <span class="badge" style="background: #C0392B; color: #FFFFFF; font-size: 0.72rem;">En este momento</span>
        </div>
        <div class="clinical-options-grid" style="grid-template-columns: repeat(3, 1fr);">
          <label class="clinical-radio-btn" onclick="selectClinicalRadio(this)">
            <input type="radio" name="p5" value="si" style="display: none;">
            <span style="font-weight: 600; color: #C0392B;">Sí</span>
          </label>
          <label class="clinical-radio-btn" onclick="selectClinicalRadio(this)">
            <input type="radio" name="p5" value="no" style="display: none;">
            <span style="font-weight: 600;">No</span>
          </label>
          <label class="clinical-radio-btn" onclick="selectClinicalRadio(this)">
            <input type="radio" name="p5" value="prefiero_no_contestar" style="display: none;">
            <span style="font-weight: 500; font-size: 0.75rem;">Prefiero no contestar</span>
          </label>
        </div>
      </div>

      <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
        <button type="submit" id="asqSubmitBtn" class="btn btn-primary" style="background: #2E5D4B; gap: 8px; width: 100%; justify-content: center; padding: 0.85rem;">
          <span>Finalizar evaluación y conectar</span>
          <i class="fa-solid fa-heart-pulse"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 4. PANTALLA DE CONTENCIÓN SEGURA (SAFE CONTAINMENT PARA ROJO / ROJO AGUDO) -->
<div id="containmentModalOverlay" class="clinical-modal-overlay" style="display: none;">
  <div class="containment-screen-box">
    
    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(192, 57, 43, 0.2); border: 2px solid #FFA59C; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
      <i class="fa-solid fa-hand-holding-heart" style="font-size: 1.6rem; color: #FFA59C;"></i>
    </div>

    <h2 style="font-family: var(--font-display); font-size: clamp(1.4rem, 3.5vw, 1.9rem); margin-bottom: 0.5rem; color: #FFFFFF;">
      Estamos contigo y no estás solo(a)
    </h2>
    <p style="color: #C8DDD1; font-size: 0.95rem; line-height: 1.6; max-width: 520px; margin: 0 auto;">
      Sentir dolor o saturación emocional puede ser abrumador. En este momento, dar el siguiente paso en compañía hace toda la diferencia.
    </p>

    <!-- 4 ACCIONES INMEDIATAS REQUERIDAS POR EL PROTOCOLO -->
    <div class="containment-actions-list">
      
      <!-- Acción 1: Línea de crisis 24/7 -->
      <a href="tel:{{ config('clinical.crisis_numbers.linea_vida', '8002900024') }}" onclick="registrarCrisisAccion('llamar_linea')" class="containment-action-item containment-action-primary">
        <i class="fa-solid fa-phone-volume" style="font-size: 1.25rem;"></i>
        <div style="flex: 1;">
          <div style="font-size: 0.95rem;">Hablar con Línea de la Vida (24/7 Gratuita)</div>
          <div style="font-size: 0.78rem; opacity: 0.9; font-weight: 400;">{{ config('clinical.crisis_numbers.linea_vida', '800 290 0024') }} · Confidencial</div>
        </div>
        <i class="fa-solid fa-arrow-right" style="font-size: 0.85rem;"></i>
      </a>

      <!-- Acción 2: Contacto de Emergencia Registrado -->
      @php
        $emergencyContact = auth()->user()?->contactosEmergencia()->first();
        $contactPhone = $emergencyContact?->telefono ?? auth()->user()?->crisis_contact_phone ?? '8002900024';
        $contactName = $emergencyContact?->nombre ?? auth()->user()?->crisis_contact_name ?? 'Contacto de Apoyo';
      @endphp
      <a href="tel:{{ preg_replace('/[^0-9]/', '', $contactPhone) }}" onclick="registrarCrisisAccion('llamar_contacto')" class="containment-action-item containment-action-secondary">
        <i class="fa-solid fa-user-shield" style="font-size: 1.25rem; color: #A8E6C0;"></i>
        <div style="flex: 1;">
          <div style="font-size: 0.95rem;">Llamar a mi contacto: {{ $contactName }}</div>
          <div style="font-size: 0.78rem; opacity: 0.8; font-weight: 400;">{{ $contactPhone }}</div>
        </div>
        <i class="fa-solid fa-phone" style="font-size: 0.85rem; color: #A8E6C0;"></i>
      </a>

      <!-- Acción 3: Plan de Seguridad y Recursos Inmediatos -->
      <a href="{{ route('safety-plan.show') }}" onclick="registrarCrisisAccion('ver_recursos')" class="containment-action-item containment-action-secondary">
        <i class="fa-solid fa-shield-heart" style="font-size: 1.25rem; color: #C8B87A;"></i>
        <div style="flex: 1;">
          <div style="font-size: 0.95rem;">Abrir mi Plan de Seguridad</div>
          <div style="font-size: 0.78rem; opacity: 0.8; font-weight: 400;">Estrategias y lugares seguros guardados por ti</div>
        </div>
        <i class="fa-solid fa-chevron-right" style="font-size: 0.85rem;"></i>
      </a>

      <!-- Acción 4: Ya estoy con alguien (Salida acompañada) -->
      <button type="button" onclick="registrarCrisisAccion('estoy_con_alguien'); closeClinicalModals();" class="containment-action-item containment-action-secondary" style="border-color: rgba(90, 181, 110, 0.4); cursor: pointer; text-align: left; width: 100%;">
        <i class="fa-solid fa-user-group" style="font-size: 1.25rem; color: #5AB56E;"></i>
        <div style="flex: 1;">
          <div style="font-size: 0.95rem;">Ya estoy acompañado(a) con alguien de confianza</div>
          <div style="font-size: 0.78rem; opacity: 0.8; font-weight: 400;">Registrar evento de acompañamiento seguro</div>
        </div>
        <i class="fa-solid fa-circle-check" style="font-size: 0.95rem; color: #5AB56E;"></i>
      </button>

    </div>

    <!-- Salida discreta sin ayuda (registra salida_sin_contacto en auditoría) -->
    <div>
      <button type="button" onclick="registrarCrisisAccion('salida_sin_contacto'); closeClinicalModals();" class="containment-exit-btn">
        Continuar a mi espacio de bienestar →
      </button>
    </div>

  </div>
</div>
