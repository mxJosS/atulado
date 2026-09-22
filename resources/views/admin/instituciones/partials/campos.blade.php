{{--
  Bloques 1 a 3 del formulario de institución, compartidos por el alta y la edición.
  $i: la institución que se edita, o null en el alta.
--}}
@php
  $sectores = config('atulado.sectores', []);
  $sectorActual = old('sector', $i?->sector ?? ($sectores[0] ?? ''));
  if ($sectorActual && !in_array($sectorActual, $sectores, true)) {
      array_unshift($sectores, $sectorActual);
  }
@endphp

<div class="form-bloque" id="{{ $prefijo }}-sec-organizacion">
  <div class="form-bloque-titulo">1. Datos de la Organización (Empresa o Colegio)</div>

  <div class="form-fila c2-1">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-razon_social">Razón Social o Nombre Oficial *</label>
      <input type="text" id="{{ $prefijo }}-razon_social" name="razon_social" class="form-input-styled @error('razon_social') con-error @enderror" required maxlength="255"
             value="{{ old('razon_social', $i?->razon_social) }}" placeholder="Ej. Organización Ejemplo S.A. de C.V.">
      @error('razon_social') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-nombre_corto">Nombre Corto *</label>
      <input type="text" id="{{ $prefijo }}-nombre_corto" name="nombre_corto" class="form-input-styled @error('nombre_corto') con-error @enderror" required maxlength="80"
             value="{{ old('nombre_corto', $i?->nombre_corto) }}" placeholder="Ej. Organización Ejemplo">
      @error('nombre_corto') <span class="form-error">{{ $message }}</span> @enderror
    </div>
  </div>

  <div class="form-fila c3">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-sector">Sector / Giro *</label>
      <select id="{{ $prefijo }}-sector" name="sector" class="form-input-styled @error('sector') con-error @enderror" required>
        @foreach($sectores as $sector)
          <option value="{{ $sector }}" @selected($sectorActual === $sector)>{{ $sector }}</option>
        @endforeach
      </select>
      @error('sector') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-rfc">RFC (Opcional)</label>
      <input type="text" id="{{ $prefijo }}-rfc" name="rfc" class="form-input-styled @error('rfc') con-error @enderror" maxlength="15"
             value="{{ old('rfc', $i?->rfc) }}" placeholder="CMA210415H21" style="text-transform: uppercase;">
      @error('rfc') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-ciudad">Ciudad / Ubicación</label>
      <input type="text" id="{{ $prefijo }}-ciudad" name="ciudad" class="form-input-styled @error('ciudad') con-error @enderror" maxlength="150"
             value="{{ old('ciudad', $i ? $i->ciudad : 'Mérida, Yucatán') }}" placeholder="Ej. Mérida, Yucatán">
      @error('ciudad') <span class="form-error">{{ $message }}</span> @enderror
    </div>
  </div>
</div>

<div class="form-bloque" id="{{ $prefijo }}-sec-contacto">
  <div class="form-bloque-titulo">2. Contacto Administrativo / Recursos Humanos</div>

  <div class="form-fila c1-1">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-contacto_nombre">Nombre del Contacto *</label>
      <input type="text" id="{{ $prefijo }}-contacto_nombre" name="contacto_nombre" class="form-input-styled @error('contacto_nombre') con-error @enderror" required maxlength="255"
             value="{{ old('contacto_nombre', $i?->contacto_nombre) }}" placeholder="Ej. Lic. Roberto Pech">
      @error('contacto_nombre') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-contacto_puesto">Cargo o Puesto</label>
      <input type="text" id="{{ $prefijo }}-contacto_puesto" name="contacto_puesto" class="form-input-styled @error('contacto_puesto') con-error @enderror" maxlength="150"
             value="{{ old('contacto_puesto', $i ? $i->contacto_puesto : 'Gerente de Recursos Humanos') }}" placeholder="Ej. Gerente de Recursos Humanos">
      @error('contacto_puesto') <span class="form-error">{{ $message }}</span> @enderror
    </div>
  </div>

  <div class="form-fila c1-1">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-contacto_email">Correo Electrónico *</label>
      <input type="email" id="{{ $prefijo }}-contacto_email" name="contacto_email" class="form-input-styled @error('contacto_email') con-error @enderror" required maxlength="255"
             value="{{ old('contacto_email', $i?->contacto_email) }}" placeholder="rpech@empresa.com">
      @error('contacto_email') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-contacto_telefono">Teléfono de Enlace</label>
      <input type="text" id="{{ $prefijo }}-contacto_telefono" name="contacto_telefono" class="form-input-styled @error('contacto_telefono') con-error @enderror" maxlength="50"
             value="{{ old('contacto_telefono', $i?->contacto_telefono) }}" placeholder="Ej. 999 412 8830">
      @error('contacto_telefono') <span class="form-error">{{ $message }}</span> @enderror
    </div>
  </div>
</div>

<div class="form-bloque" id="{{ $prefijo }}-sec-profesional">
  <div class="form-bloque-titulo">3. Profesional Clínico Designado (NOM-035 y Crisis)</div>

  <div class="form-fila c2-1" style="margin-bottom: 0.6rem;">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-profesional_nombre">Nombre del Psicólogo / Especialista *</label>
      <input type="text" id="{{ $prefijo }}-profesional_nombre" name="profesional_nombre" class="form-input-styled @error('profesional_nombre') con-error @enderror" required maxlength="255"
             value="{{ old('profesional_nombre', $i?->profesional_nombre) }}" placeholder="Ej. Psic. Rodrigo Ancona Rosado">
      @error('profesional_nombre') <span class="form-error">{{ $message }}</span> @enderror
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-profesional_cedula">Cédula Profesional</label>
      <input type="text" id="{{ $prefijo }}-profesional_cedula" name="profesional_cedula" class="form-input-styled @error('profesional_cedula') con-error @enderror" maxlength="30"
             value="{{ old('profesional_cedula', $i?->profesional_cedula) }}" placeholder="Ej. 9412034">
      @error('profesional_cedula') <span class="form-error">{{ $message }}</span> @enderror
    </div>
  </div>

  <div class="form-fila c2-1" style="margin-bottom: 0.6rem;">
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-profesional_email">Correo del profesional</label>
      <input type="email" id="{{ $prefijo }}-profesional_email" name="profesional_email" class="form-input-styled @error('profesional_email') con-error @enderror" maxlength="255"
             value="{{ old('profesional_email', $i?->profesional_email) }}" placeholder="Ej. rancona@consultorio.mx">
      @error('profesional_email') <span class="form-error">{{ $message }}</span> @enderror
      <span class="form-hint-styled">A este correo llega el resumen clínico cuando se entrega.</span>
    </div>
    <div>
      <label class="form-group-label" for="{{ $prefijo }}-profesional_nda_hasta">Confidencialidad (NDA) vigente hasta</label>
      <input type="date" id="{{ $prefijo }}-profesional_nda_hasta" name="profesional_nda_hasta" class="form-input-styled @error('profesional_nda_hasta') con-error @enderror"
             value="{{ old('profesional_nda_hasta', $i?->profesional_nda_hasta?->format('Y-m-d')) }}">
      @error('profesional_nda_hasta') <span class="form-error">{{ $message }}</span> @enderror
      <span class="form-hint-styled">Sin NDA vigente no se le puede entregar un resumen.</span>
    </div>
  </div>

  <div class="nota-protocolo">
    <i class="fa-solid fa-user-shield"></i>
    <span>Este profesional es el único facultado legalmente para recibir el expediente clínico nominativo en situaciones de crisis activa, protegiendo la confidencialidad ante la dirección.</span>
  </div>
</div>
