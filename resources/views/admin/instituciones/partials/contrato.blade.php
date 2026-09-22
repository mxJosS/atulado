{{--
  Plan, padrón estimado y renovación. $i: la institución que se edita, o null en el alta.
--}}
@php
  $planes = config('atulado.planes', []);
  $planActual = old('plan', $i?->plan ?? ($planes[0] ?? ''));
  if ($planActual && !in_array($planActual, $planes, true)) {
      array_unshift($planes, $planActual);
  }
@endphp

<div class="form-fila c3">
  <div>
    <label class="form-group-label" for="{{ $prefijo }}-plan">Plan Institucional *</label>
    <select id="{{ $prefijo }}-plan" name="plan" class="form-input-styled @error('plan') con-error @enderror" required>
      @foreach($planes as $plan)
        <option value="{{ $plan }}" @selected($planActual === $plan)>{{ $plan }}</option>
      @endforeach
    </select>
    @error('plan') <span class="form-error">{{ $message }}</span> @enderror
  </div>
  <div>
    <label class="form-group-label" for="{{ $prefijo }}-padron_estimado">Padrón Estimado</label>
    <input type="number" id="{{ $prefijo }}-padron_estimado" name="padron_estimado" class="form-input-styled @error('padron_estimado') con-error @enderror" min="0"
           value="{{ old('padron_estimado', $i ? $i->padron_estimado : 100) }}" placeholder="Número de personas">
    @error('padron_estimado') <span class="form-error">{{ $message }}</span> @enderror
  </div>
  <div>
    <label class="form-group-label" for="{{ $prefijo }}-vigencia_fin">Fecha de Renovación</label>
    <input type="date" id="{{ $prefijo }}-vigencia_fin" name="vigencia_fin" class="form-input-styled @error('vigencia_fin') con-error @enderror"
           value="{{ old('vigencia_fin', $i ? $i->vigencia_fin?->format('Y-m-d') : now()->addYear()->format('Y-m-d')) }}">
    @error('vigencia_fin') <span class="form-error">{{ $message }}</span> @enderror
  </div>
</div>

<div class="form-fila c3" style="margin-top: 0.85rem;">
  <div>
    <label class="form-group-label" for="{{ $prefijo }}-umbral_anonimato">Mínimo de personas por reporte</label>
    <input type="number" id="{{ $prefijo }}-umbral_anonimato" name="umbral_anonimato" class="form-input-styled @error('umbral_anonimato') con-error @enderror" min="1" max="1000"
           value="{{ old('umbral_anonimato', $i ? $i->umbral_anonimato : 1) }}">
    @error('umbral_anonimato') <span class="form-error">{{ $message }}</span> @enderror
  </div>
  <div>
    <label class="form-group-label" for="{{ $prefijo }}-meta_adopcion">Meta de cuentas activadas (%)</label>
    <input type="number" id="{{ $prefijo }}-meta_adopcion" name="meta_adopcion" class="form-input-styled @error('meta_adopcion') con-error @enderror" min="1" max="100"
           value="{{ old('meta_adopcion', $i ? $i->meta_adopcion : 75) }}">
    @error('meta_adopcion') <span class="form-error">{{ $message }}</span> @enderror
  </div>
  <div style="align-self: end;">
    <span class="form-hint-styled">
      Mínimo por reporte: un reporte agregado sólo se genera si el corte tiene al menos esas personas (1 = desde la primera; 15 es lo recomendado para anonimato).
      Meta: el porcentaje del padrón que el contrato espera con cuenta activada.
    </span>
  </div>
</div>
