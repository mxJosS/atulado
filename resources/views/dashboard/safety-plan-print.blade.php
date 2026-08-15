<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plan de Seguridad — {{ $user->name }}</title>
  <style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1a201c; line-height: 1.5; padding: 2rem; max-width: 800px; margin: 0 auto; background: #fff; }
    h1 { font-size: 1.8rem; margin-bottom: 0.2rem; color: #2e4f3a; }
    h2 { font-size: 1.15rem; margin-top: 1.25rem; margin-bottom: 0.4rem; border-bottom: 2px solid #e1ede5; padding-bottom: 0.25rem; color: #3c634b; }
    .box { border: 1px solid #c5dcce; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    .crisis-box { background: #fff5f5; border: 2px solid #fca5a5; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; text-align: center; }
    .btn-print { background: #4d7c5f; color: #fff; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-bottom: 1.5rem; }
    @media print { .no-print { display: none !important; } body { padding: 0; } }
  </style>
</head>
<body>

  <div class="no-print" style="display: flex; justify-content: space-between; align-items: center;">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Guardar como PDF</button>
    <a href="{{ route('safety-plan.show') }}" style="color: #4d7c5f; font-size: 0.9rem;">← Volver al editor</a>
  </div>

  <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2e4f3a; padding-bottom: 0.75rem; margin-bottom: 1rem;">
    <div>
      <h1>Plan de Seguridad Personal</h1>
      <div style="font-size: 0.95rem; color: #555;">Titular: <strong>{{ $user->name }}</strong> · Documento de Respaldo y Prevención</div>
    </div>
    <div style="text-align: right; font-size: 0.85rem; color: #777;">
      Generado: {{ date('d/m/Y') }}<br>A tu lado
    </div>
  </div>

  <h2>1. Mis Señales de Alerta Tempranas</h2>
  <div class="box">
    <ul>
      @forelse($safetyPlan->warning_signs ?? [] as $ws)
        <li>{{ $ws }}</li>
      @empty
        <li>Sin señales registradas aún.</li>
      @endforelse
    </ul>
  </div>

  <h2>2. Mis Estrategias Internas de Afrontamiento</h2>
  <div class="box">
    <ul>
      @forelse($safetyPlan->internal_coping ?? [] as $ic)
        <li>{{ $ic }}</li>
      @empty
        <li>Sin estrategias registradas aún.</li>
      @endforelse
    </ul>
  </div>

  <h2>3. Contactos de Confianza a Quienes Llamar</h2>
  <div class="box">
    @forelse($safetyPlan->trusted_contacts ?? [] as $tc)
      <div><strong>{{ $tc['name'] ?? '' }}</strong> ({{ $tc['relationship'] ?? 'Apoyo' }}): {{ $tc['phone'] ?? '' }}</div>
    @empty
      <div>Sin contactos registrados aún.</div>
    @endforelse
  </div>

  @if($safetyPlan->reasons_for_living)
    <h2>4. Mis Razones para Vivir</h2>
    <div class="box" style="background: #fdfaf3;">
      <p style="white-space: pre-line;">{{ $safetyPlan->reasons_for_living }}</p>
    </div>
  @endif

  <div class="crisis-box">
    <strong style="color: #c0392b; font-size: 1.1rem;">LÍNEAS DE CRISIS 24 HORAS</strong><br>
    México (Línea de la Vida): <strong>800 290 0024</strong> · Gratuito y confidencial<br>
    Cruz Roja (SAPTEL): <strong>55 5259 8121</strong> · Emergencias: <strong>911</strong>
  </div>

</body>
</html>
