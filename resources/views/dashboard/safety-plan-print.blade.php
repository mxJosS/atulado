<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plan de Seguridad — {{ $user->name }} — A tu lado</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=IBM+Plex+Mono:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --sage-dark: #1E4A25;
      --sage-main: #2E5D4B;
      --sage-light: #5AB56E;
      --sage-bg: #F4F8F5;
      --crisis-red: #C0392B;
      --border-gray: #D1DFD6;
      --text-dark: #1A2620;
      --text-muted: #556860;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: var(--text-dark);
      background: #F8FAF9;
      line-height: 1.5;
      padding: 2.5rem 1.5rem;
    }

    .print-sheet {
      max-width: 820px;
      margin: 0 auto;
      background: #FFFFFF;
      border: 1.5px solid var(--border-gray);
      border-radius: 20px;
      padding: 2.5rem 2.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }

    /* Print Controls */
    .no-print {
      max-width: 820px;
      margin: 0 auto 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .btn-print-action {
      background: var(--sage-main);
      color: #FFFFFF;
      border: none;
      padding: 0.75rem 1.4rem;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.92rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(46, 93, 75, 0.25);
      transition: all 0.2s;
    }

    .btn-print-action:hover {
      background: var(--sage-dark);
    }

    .back-link {
      color: var(--sage-main);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.88rem;
    }

    /* Document Header */
    .doc-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2.5px solid var(--sage-main);
      padding-bottom: 1.25rem;
      margin-bottom: 1.75rem;
    }

    .brand-title {
      font-family: 'Fraunces', serif;
      font-size: 1.85rem;
      font-weight: 700;
      color: var(--sage-main);
      line-height: 1.1;
    }

    .meta-tag {
      font-family: 'IBM Plex Mono', monospace;
      font-size: 0.74rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
    }

    /* Step Boxes */
    .step-box {
      border: 1px solid var(--border-gray);
      border-radius: 12px;
      padding: 1.1rem 1.25rem;
      margin-bottom: 1.25rem;
      background: #FFFFFF;
      page-break-inside: avoid;
    }

    .step-header {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      margin-bottom: 0.6rem;
    }

    .step-num {
      background: var(--sage-bg);
      color: var(--sage-main);
      font-family: 'IBM Plex Mono', monospace;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.2rem 0.55rem;
      border-radius: 6px;
      border: 1px solid var(--border-gray);
      text-transform: uppercase;
    }

    .step-title {
      font-family: 'Fraunces', serif;
      font-size: 1.08rem;
      font-weight: 700;
      color: var(--text-dark);
    }

    ul.clean-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    ul.clean-list li {
      position: relative;
      padding-left: 1.3rem;
      font-size: 0.9rem;
      color: var(--text-dark);
      line-height: 1.45;
    }

    ul.clean-list li::before {
      content: "•";
      position: absolute;
      left: 0.35rem;
      color: var(--sage-light);
      font-weight: bold;
      font-size: 1.1rem;
    }

    .contacts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 0.75rem;
    }

    .contact-card-print {
      background: var(--sage-bg);
      border: 1px solid var(--border-gray);
      border-radius: 8px;
      padding: 0.75rem 0.9rem;
      font-size: 0.86rem;
    }

    /* Crisis Hotline Ribbon */
    .crisis-ribbon {
      background: #FDEDEC;
      border: 2px solid #F5B7B1;
      border-radius: 12px;
      padding: 1rem 1.25rem;
      text-align: center;
      margin-top: 1.5rem;
      page-break-inside: avoid;
    }

    .crisis-title {
      font-family: 'IBM Plex Mono', monospace;
      font-weight: 700;
      color: var(--crisis-red);
      font-size: 0.88rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 0.35rem;
    }

    .crisis-numbers {
      font-size: 0.92rem;
      color: #78281F;
      line-height: 1.6;
    }

    /* Pocket Safety Card */
    .pocket-card-section {
      margin-top: 2rem;
      border-top: 2px dashed #B8D0C2;
      padding-top: 1.5rem;
      page-break-inside: avoid;
    }

    .pocket-card-header {
      font-family: 'IBM Plex Mono', monospace;
      font-size: 0.74rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .pocket-card {
      border: 2px dashed var(--sage-main);
      border-radius: 14px;
      padding: 1.25rem 1.4rem;
      background: #FAFDFB;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    @media (max-width: 600px) {
      .pocket-card { grid-template-columns: 1fr; }
    }

    @media print {
      body { background: #FFFFFF !important; padding: 0; }
      .no-print { display: none !important; }
      .print-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
      .step-box, .crisis-ribbon, .pocket-card { page-break-inside: avoid; }
    }
  </style>
</head>
<body>

  <!-- Top Action Bar -->
  <div class="no-print">
    <button onclick="window.print()" class="btn-print-action">
      <i class="fa-solid fa-print"></i>
      <span>Imprimir o Guardar en PDF</span>
    </button>
    <a href="{{ route('safety-plan.show') }}" class="back-link">
      <i class="fa-solid fa-arrow-left"></i> Volver al editor del plan
    </a>
  </div>

  <!-- Printable Document Sheet -->
  <div class="print-sheet">

    <!-- Document Header -->
    <div class="doc-header">
      <div>
        <div class="meta-tag">PROTOCOLO DE RESPALDO & PREVENCIÓN CLÍNICA</div>
        <h1 class="brand-title">Plan de Seguridad Personal</h1>
        <div style="font-size: 0.95rem; color: var(--text-muted); margin-top: 0.2rem;">
          Titular: <strong style="color: var(--text-dark);">{{ $user->name }}</strong>
        </div>
      </div>
      <div style="text-align: right;">
        <!-- Pixel Tree Logo -->
        <svg viewBox="0 0 16 16" width="34" height="34" xmlns="http://www.w3.org/2000/svg" style="margin-left: auto; margin-bottom: 0.35rem;">
          <rect x="5" y="0" width="6" height="2" fill="#2D6B3A"/>
          <rect x="3" y="2" width="10" height="2" fill="#3D8C4F"/>
          <rect x="2" y="4" width="12" height="2" fill="#5AB56E"/>
          <rect x="3" y="6" width="10" height="2" fill="#3D8C4F"/>
          <rect x="5" y="8" width="6" height="2" fill="#2D6B3A"/>
          <rect x="7" y="10" width="2" height="4" fill="#6B3A1F"/>
          <rect x="4" y="1" width="1" height="1" fill="#C0392B"/>
          <rect x="11" y="3" width="1" height="1" fill="#C0392B"/>
          <rect x="9" y="7" width="1" height="1" fill="#C0392B"/>
        </svg>
        <div class="meta-tag">A tu lado · {{ date('d/m/Y') }}</div>
      </div>
    </div>

    <!-- PASO 1: SEÑALES DE ALERTA -->
    <div class="step-box" style="border-left: 4px solid var(--crisis-red);">
      <div class="step-header">
        <span class="step-num" style="color: var(--crisis-red);">Paso 1</span>
        <h2 class="step-title">Mis Señales de Alerta Tempranas</h2>
      </div>
      <ul class="clean-list">
        @forelse($safetyPlan->warning_signs ?? [] as $ws)
          <li>{{ $ws }}</li>
        @empty
          <li style="color: var(--text-muted); font-style: italic;">Sin señales registradas aún.</li>
        @endforelse
      </ul>
    </div>

    <!-- PASO 2: ESTRATEGIAS INTERNAS -->
    <div class="step-box" style="border-left: 4px solid var(--sage-main);">
      <div class="step-header">
        <span class="step-num" style="color: var(--sage-main);">Paso 2</span>
        <h2 class="step-title">Estrategias Internas de Afrontamiento</h2>
      </div>
      <ul class="clean-list">
        @forelse($safetyPlan->internal_coping ?? [] as $ic)
          <li>{{ $ic }}</li>
        @empty
          <li style="color: var(--text-muted); font-style: italic;">Sin estrategias registradas aún.</li>
        @endforelse
      </ul>
    </div>

    <!-- PASO 3: DISTRACCIONES SOCIALES Y LUGARES SEGUROS -->
    <div class="step-box" style="border-left: 4px solid #5B4A8A;">
      <div class="step-header">
        <span class="step-num" style="color: #5B4A8A;">Paso 3</span>
        <h2 class="step-title">Personas y Entornos de Distracción</h2>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
          <div style="font-weight: 700; font-size: 0.84rem; color: #5B4A8A; margin-bottom: 0.35rem;">Personas de distracción:</div>
          <ul class="clean-list">
            @forelse($safetyPlan->social_distractions ?? [] as $sd)
              <li>{{ $sd }}</li>
            @empty
              <li style="color: var(--text-muted); font-style: italic;">Sin personas registradas.</li>
            @endforelse
          </ul>
        </div>
        <div>
          <div style="font-weight: 700; font-size: 0.84rem; color: #5B4A8A; margin-bottom: 0.35rem;">Lugares seguros:</div>
          <ul class="clean-list">
            @forelse($safetyPlan->safe_places ?? [] as $sp)
              <li>{{ $sp }}</li>
            @empty
              <li style="color: var(--text-muted); font-style: italic;">Sin lugares registrados.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <!-- PASO 4: RED DE APOYO DE CONFIANZA -->
    <div class="step-box" style="border-left: 4px solid #8A7332;">
      <div class="step-header">
        <span class="step-num" style="color: #8A7332;">Paso 4</span>
        <h2 class="step-title">Red de Apoyo Personal (A quién pedir auxilio)</h2>
      </div>
      <div class="contacts-grid">
        @forelse($safetyPlan->trusted_contacts ?? [] as $tc)
          <div class="contact-card-print">
            <div style="font-weight: 700; color: var(--text-dark);">{{ $tc['name'] ?? 'Contacto' }}</div>
            <div style="color: var(--text-muted); font-size: 0.78rem;">{{ $tc['relationship'] ?? 'Apoyo' }}</div>
            <div style="font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--sage-main); margin-top: 0.2rem; display: flex; align-items: center; gap: 4px;">
              <i class="fa-solid fa-phone" style="font-size: 0.75rem;"></i>
              <span>{{ $tc['phone'] ?? 'Sin número' }}</span>
            </div>
          </div>
        @empty
          <div style="color: var(--text-muted); font-style: italic;">Sin contactos registrados aún.</div>
        @endforelse
      </div>
    </div>

    <!-- PASO 5: RAZONES PARA VIVIR -->
    <div class="step-box" style="border-left: 4px solid var(--sage-light); background: #FAFDFB;">
      <div class="step-header">
        <span class="step-num" style="color: var(--sage-light);">Paso 5</span>
        <h2 class="step-title">Mis Razones para Vivir y Seguir Adelante</h2>
      </div>
      <ul class="clean-list">
        @forelse($safetyPlan->reasons_to_live ?? [] as $rl)
          <li>{{ $rl }}</li>
        @empty
          @if($safetyPlan->reasons_for_living)
            <li>{{ $safetyPlan->reasons_for_living }}</li>
          @else
            <li style="color: var(--text-muted); font-style: italic;">Sin razones registradas aún.</li>
          @endif
        @endforelse
      </ul>
    </div>

    <!-- CRISIS EMERGENCY BANNER -->
    <div class="crisis-ribbon">
      <div class="crisis-title" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>LÍNEAS DE CRISIS Y AYUDA INMEDIATA (24/7 GRATUITO)</span>
      </div>
      <div class="crisis-numbers">
        <strong>México (Línea de la Vida):</strong> 800 290 0024 &nbsp;·&nbsp;
        <strong>SAPTEL Cruz Roja:</strong> 55 5259 8121 &nbsp;·&nbsp;
        <strong>Emergencias Nacionales:</strong> 911
      </div>
    </div>

    <!-- POCKET EMERGENCY CARD (RECORTABLE / PLEGABLE) -->
    <div class="pocket-card-section">
      <div class="pocket-card-header">
        <i class="fa-solid fa-scissors"></i>
        <span>TARJETA RECORTABLE DE BOLSILLO (Llévala en tu cartera o funda de celular)</span>
      </div>
      <div class="pocket-card">
        <div>
          <div style="font-family: 'Fraunces', serif; font-weight: 700; font-size: 0.98rem; color: var(--sage-main);">
            Mi Respaldo Rápido · {{ $user->name }}
          </div>
          <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem; line-height: 1.4;">
            <strong>Si me siento en peligro:</strong><br>
            1. Respira 4-7-8 (4s inhalar, 7s retener, 8s exhalar).<br>
            2. Contacto de emergencia: 
            <strong>{{ ($safetyPlan->trusted_contacts[0]['name'] ?? 'Apoyo') }}: {{ ($safetyPlan->trusted_contacts[0]['phone'] ?? '800 290 0024') }}</strong>
          </div>
        </div>
        <div style="text-align: right; font-size: 0.78rem; line-height: 1.4;">
          <div style="font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--crisis-red); font-size: 0.82rem;">
            LÍNEA DE LA VIDA 24H:
          </div>
          <div style="font-family: 'IBM Plex Mono', monospace; font-size: 1.05rem; font-weight: 700; color: var(--crisis-red);">
            800 290 0024
          </div>
          <div style="color: var(--text-muted); font-size: 0.72rem; margin-top: 0.2rem;">
            A tu lado · Siempre contigo
          </div>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
