@extends('layouts.admin')

@section('title', $institucion->nombre_corto)

@push('styles')
  @include('admin.instituciones.partials.estilos')
@endpush

@php
  $dias = $institucion->diasParaRenovar();
  $vacio = fn ($valor) => $valor === null || $valor === '';
@endphp

@section('content')
<div class="admin-content-canvas">

  {{-- Encabezado --}}
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div class="crumbs-atl">
        Consola A Tu Lado &rsaquo; <a href="{{ route('admin.instituciones.index') }}">Instituciones</a> &rsaquo; <b>{{ $institucion->nombre_corto }}</b>
      </div>
      <div style="display: flex; align-items: center; gap: 0.9rem;">
        <span class="logo-inst grande" style="background: {{ $institucion->color ?: '#2E5D4B' }};">{{ $institucion->iniciales }}</span>
        <div>
          <h1 class="titulo-atl">{{ $institucion->razon_social }}</h1>
          <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 0.4rem;">
            <span class="chip-atl {{ $institucion->estado === 'activa' ? '' : ($institucion->estado === 'onboarding' ? 'gris' : 'ambar') }}">{{ $institucion->estado_legible }}</span>
            <span class="chip-atl gris">{{ $institucion->sector ?: 'Sin sector' }}</span>
            <span class="chip-atl gris">{{ $institucion->plan }}</span>
            @if($dias !== null)
              @if($dias < 0)
                <span class="chip-atl rojo">Contrato vencido el {{ $institucion->vigencia_fin->format('d/m/Y') }}</span>
              @elseif($dias <= 30)
                <span class="chip-atl ambar">Renueva en {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}</span>
              @else
                <span class="chip-atl gris">Renueva {{ $institucion->vigencia_fin->format('d/m/Y') }}</span>
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
      @if($otras->count() > 1)
        <select class="form-input-styled" style="width: auto; font-weight: 600;" aria-label="Cambiar de institución"
                data-url="{{ route('admin.instituciones.show', '__SLUG__') }}"
                onchange="window.location.href = this.dataset.url.replace('__SLUG__', this.value)">
          @foreach($otras as $otra)
            <option value="{{ $otra->slug }}" @selected($otra->id === $institucion->id)>{{ $otra->nombre_corto }}</option>
          @endforeach
        </select>
      @endif
      @if($esAdmin)
      <button type="button" class="btn-atl primario" data-open="m-editar-institucion">
        <i class="fa-solid fa-pen-to-square"></i> Editar empresa
      </button>
      @endif
    </div>
  </div>

  {{-- Resumen --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-users"></i> Personas en padrón</div>
      <div class="kpi-value">{{ $resumen['padron'] }}</div>
      <div class="kpi-foot">Estimado en contrato: {{ $institucion->padron_estimado }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-user-check"></i> Cuentas activadas</div>
      <div class="kpi-value">
        @if($resumen['adopcion'] !== null)
          {{ $resumen['adopcion'] }} <span class="unit">%</span>
        @else
          --
        @endif
      </div>
      <div class="kpi-foot">{{ $resumen['activas'] }} activas · {{ $resumen['invitadas'] }} sin entrar aún</div>
    </div>
    <div class="kpi">
      <div class="kpi-label"><i class="fa-solid fa-sitemap"></i> Estructura</div>
      <div class="kpi-value">{{ $resumen['macro_areas'] }} <span class="unit">{{ $resumen['macro_areas'] === 1 ? 'macro-área' : 'macro-áreas' }}</span></div>
      <div class="kpi-foot">{{ $resumen['areas'] }} {{ $resumen['areas'] === 1 ? 'área interna' : 'áreas internas' }}</div>
    </div>
    <div class="kpi {{ $resumen['casos_abiertos'] > 0 ? 'accent-red' : '' }}">
      <div class="kpi-label"><i class="fa-solid fa-life-ring"></i> Casos de crisis abiertos</div>
      <div class="kpi-value">{{ $resumen['casos_abiertos'] }}</div>
      <div class="kpi-foot">Se cierran sólo con contacto humano verificado</div>
    </div>
  </div>

  {{-- Fichas de datos --}}
  <div class="grid-fichas">
    <div class="card-atl">
      <div class="card-atl-head">
        <div><h3>Datos de la organización</h3></div>
        @if($esAdmin)<button type="button" class="btn-atl linea sm" data-open="m-editar-institucion" data-seccion="organizacion"><i class="fa-solid fa-pen"></i> Editar</button>@endif
      </div>
      <dl class="kv-atl">
        <dt>Razón social</dt><dd>{{ $institucion->razon_social }}</dd>
        <dt>Nombre corto</dt><dd>{{ $institucion->nombre_corto }}</dd>
        <dt>Sector / giro</dt><dd @class(['vacio' => $vacio($institucion->sector)])>{{ $institucion->sector ?: 'Sin registrar' }}</dd>
        <dt>RFC</dt><dd @class(['vacio' => $vacio($institucion->rfc)])>{{ $institucion->rfc ?: 'Sin registrar' }}</dd>
        <dt>Ciudad</dt><dd @class(['vacio' => $vacio($institucion->ciudad)])>{{ $institucion->ciudad ?: 'Sin registrar' }}</dd>
      </dl>
    </div>

    <div class="card-atl">
      <div class="card-atl-head">
        <div><h3>Contacto administrativo / RR. HH.</h3></div>
        @if($esAdmin)<button type="button" class="btn-atl linea sm" data-open="m-editar-institucion" data-seccion="contacto"><i class="fa-solid fa-pen"></i> Editar</button>@endif
      </div>
      <dl class="kv-atl">
        <dt>Nombre</dt><dd>{{ $institucion->contacto_nombre }}</dd>
        <dt>Cargo</dt><dd @class(['vacio' => $vacio($institucion->contacto_puesto)])>{{ $institucion->contacto_puesto ?: 'Sin registrar' }}</dd>
        <dt>Correo</dt>
        <dd>
          @if($institucion->contacto_email)
            <a href="mailto:{{ $institucion->contacto_email }}" style="color: #2E5D4B;">{{ $institucion->contacto_email }}</a>
          @endif
        </dd>
        <dt>Teléfono</dt><dd @class(['vacio' => $vacio($institucion->contacto_telefono)])>{{ $institucion->contacto_telefono ?: 'Sin registrar' }}</dd>
      </dl>
    </div>

    <div class="card-atl">
      <div class="card-atl-head">
        <div><h3>Profesional clínico designado</h3></div>
        @if($esAdmin)<button type="button" class="btn-atl linea sm" data-open="m-editar-institucion" data-seccion="profesional"><i class="fa-solid fa-pen"></i> Editar</button>@endif
      </div>
      <dl class="kv-atl" style="margin-bottom: 0.85rem;">
        <dt>Nombre</dt><dd @class(['vacio' => $vacio($institucion->profesional_nombre)])>{{ $institucion->profesional_nombre ?: 'Falta designar' }}</dd>
        <dt>Cédula</dt><dd @class(['vacio' => $vacio($institucion->profesional_cedula)])>{{ $institucion->profesional_cedula ?: 'Sin registrar' }}</dd>
        <dt>Correo</dt><dd @class(['vacio' => $vacio($institucion->profesional_email)])>{{ $institucion->profesional_email ?: 'Sin registrar' }}</dd>
        <dt>NDA</dt>
        <dd>
          @if(!$institucion->profesional_nda_hasta)
            <span class="chip-atl ambar">Sin registrar</span>
          @elseif($institucion->profesional_nda_hasta->isPast())
            <span class="chip-atl rojo">Venció el {{ $institucion->profesional_nda_hasta->format('d/m/Y') }}</span>
          @else
            <span class="chip-atl">Vigente hasta {{ $institucion->profesional_nda_hasta->format('d/m/Y') }}</span>
          @endif
        </dd>
      </dl>
      <div class="nota-protocolo">
        <i class="fa-solid fa-user-shield"></i>
        <span>Único facultado para recibir el expediente clínico nominativo cuando hay una crisis activa.</span>
      </div>
    </div>

    <div class="card-atl">
      <div class="card-atl-head">
        <div><h3>Contrato</h3></div>
        @if($esAdmin)<button type="button" class="btn-atl linea sm" data-open="m-editar-institucion" data-seccion="contrato"><i class="fa-solid fa-pen"></i> Editar</button>@endif
      </div>
      <dl class="kv-atl">
        <dt>Plan</dt><dd>{{ $institucion->plan }}</dd>
        <dt>Padrón estimado</dt><dd>{{ $institucion->padron_estimado }} personas</dd>
        <dt>Inicio</dt><dd @class(['vacio' => $institucion->vigencia_inicio === null])>{{ $institucion->vigencia_inicio?->format('d/m/Y') ?? 'Sin registrar' }}</dd>
        <dt>Renovación</dt><dd @class(['vacio' => $institucion->vigencia_fin === null])>{{ $institucion->vigencia_fin?->format('d/m/Y') ?? 'Sin registrar' }}</dd>
      </dl>
    </div>
  </div>

  {{-- ══════════ PESTAÑAS ══════════ --}}
  <div class="tabs" data-tabs="inst" id="tabs-inst" style="margin-bottom: 1.25rem;">
    <button class="tab on" type="button" data-pane="estructura"><i class="fa-solid fa-sitemap"></i> Áreas y Macro-Grupos</button>
    @if($esAdmin)
      <button class="tab" type="button" data-pane="padron"><i class="fa-solid fa-users"></i> Padrón de Colaboradores ({{ $personas->where('estado', '!=', 'baja')->count() }})</button>
      <button class="tab" type="button" data-pane="invitaciones"><i class="fa-solid fa-paper-plane"></i> Invitaciones</button>
    @endif
    <button class="tab" type="button" data-pane="clinico"><i class="fa-solid fa-user-doctor"></i> Plano clínico</button>
  </div>

  <div class="tabpane on" data-tabpane="inst" data-pane="estructura" id="tab-estructura">
  {{-- Estructura --}}
  <div class="card-atl" style="margin-bottom: 1.5rem;">
    <div class="card-atl-head">
      <div>
        <h3>Estructura organizacional</h3>
        <p>Macro-áreas y las cuadrillas, salones o áreas dentro de cada una.</p>
      </div>
      @if($esAdmin)<button type="button" class="btn-atl linea sm" data-open="m-editar-institucion" data-seccion="estructura"><i class="fa-solid fa-pen"></i> Editar estructura</button>@endif
    </div>

    @forelse($arbol as $macro)
      <div class="arbol-macro">
        <div class="arbol-linea macro">
          <span><i class="fa-solid fa-layer-group" style="color: #2E5D4B; margin-right: 6px;"></i>{{ $macro['modelo']->nombre }}</span>
          <span class="arbol-conteo">{{ $macro['personas'] }} {{ $macro['personas'] === 1 ? 'persona' : 'personas' }}</span>
        </div>
        @foreach($macro['hijos'] as $hijo)
          <div class="arbol-linea hija">
            <span><i class="fa-solid fa-angle-right" style="color: #A5B8B0; margin-right: 6px;"></i>{{ $hijo['modelo']->nombre }}</span>
            <span class="arbol-conteo">{{ $hijo['personas'] }}</span>
          </div>
        @endforeach
      </div>
    @empty
      <p style="color: #6E887E; font-size: 0.88rem; margin: 0;">Esta institución aún no tiene áreas. Agrégalas desde «Editar estructura».</p>
    @endforelse
  </div>

  </div>

  @if($esAdmin)
  <div class="tabpane" data-tabpane="inst" data-pane="padron" id="tab-padron">
  {{-- Padrón por Excel --}}
  <div class="card-atl" id="padron" style="margin-bottom: 1.5rem;">
    <div class="card-atl-head">
      <div>
        <h3>Padrón de personas</h3>
        <p>Alta masiva con Excel, sin límite de personas. Si alguien ya estaba en el padrón, se actualizan sus datos.</p>
      </div>
    </div>

    <div class="padron-pasos">
      <div class="padron-paso">
        <div class="padron-num">1</div>
        <div style="flex: 1;">
          <div class="padron-titulo">Descarga la plantilla</div>
          <p class="padron-texto">Un Excel con las columnas ya definidas y listas desplegables con las áreas de {{ $institucion->nombre_corto }}. Las columnas con * son obligatorias.</p>
          <a href="{{ route('admin.instituciones.padron.plantilla', $institucion) }}" class="btn-atl suave sm">
            <i class="fa-solid fa-file-excel"></i> Descargar plantilla Excel
          </a>
        </div>
      </div>

      <div class="padron-paso">
        <div class="padron-num">2</div>
        <div style="flex: 1;">
          <div class="padron-titulo">Súbela llena</div>
          <form method="POST" action="{{ route('admin.instituciones.padron.importar', $institucion) }}" enctype="multipart/form-data"
                onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> Procesando…';">
            @csrf
            <input type="file" name="archivo" required accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                   class="form-input-styled @error('archivo') con-error @enderror" style="margin-bottom: 0.5rem; border-style: dashed;">
            @error('archivo') <span class="form-error" style="margin: -0.2rem 0 0.5rem;">{{ $message }}</span> @enderror
            <button type="submit" class="btn-atl primario sm"><i class="fa-solid fa-cloud-arrow-up"></i> Subir padrón</button>
          </form>
        </div>
      </div>
    </div>

    @if($resumen['macro_areas'] === 0)
      <div class="nota-protocolo" style="margin-top: 0.9rem;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Esta institución aún no tiene áreas. Agrégalas en «Editar estructura» antes de subir el padrón: cada persona se asigna a un área.</span>
      </div>
    @endif

    @if($ultimaCarga)
      <div class="padron-resultado">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.6rem;">
          <div style="font-size: 0.82rem; color: #556860;">
            <b style="color: #1A2620;">Última carga:</b> {{ $ultimaCarga->nombre_original }}
            · {{ $ultimaCarga->created_at->format('d/m/Y H:i') }}
            @if($ultimaCarga->autor) · {{ $ultimaCarga->autor->name }} @endif
          </div>
          <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <span class="chip-atl gris">{{ $ultimaCarga->filas_total }} filas</span>
            <span class="chip-atl">{{ $ultimaCarga->filas_lista + $ultimaCarga->filas_advertencia }} altas</span>
            @if($ultimaCarga->filas_duplicado > 0)<span class="chip-atl gris">{{ $ultimaCarga->filas_duplicado }} actualizadas</span>@endif
            @if($ultimaCarga->filas_advertencia > 0)<span class="chip-atl ambar">{{ $ultimaCarga->filas_advertencia }} con aviso</span>@endif
            @if($ultimaCarga->filas_error > 0)<span class="chip-atl rojo">{{ $ultimaCarga->filas_error }} con error</span>@endif
          </div>
        </div>

        @if(!empty($ultimaCarga->mapeo['columnas_ignoradas']))
          <p style="font-size: 0.76rem; color: #6E887E; margin: 0 0 0.6rem;">
            <i class="fa-solid fa-eye-slash"></i> Columnas ignoradas (no se guardaron): {{ implode(', ', $ultimaCarga->mapeo['columnas_ignoradas']) }}
          </p>
        @endif

        @if($filasConProblema->isNotEmpty())
          <div style="overflow-x: auto; max-height: 360px; overflow-y: auto; border: 1px solid #EEF4F0; border-radius: 10px;">
            <table class="padron-tabla">
              <thead><tr><th>Fila</th><th>Nombre</th><th>Correo</th><th>Área</th><th>Qué pasó</th></tr></thead>
              <tbody>
                @foreach($filasConProblema as $fila)
                  <tr>
                    <td class="mono">{{ $fila->numero_fila }}</td>
                    <td>{{ $fila->dato('nombre_completo') ?? '—' }}</td>
                    <td class="mono">{{ $fila->dato('correo') ?? '—' }}</td>
                    <td>{{ $fila->dato('departamento') ?? '—' }}</td>
                    <td>
                      <span class="chip-atl {{ $fila->tieneError() ? 'rojo' : 'ambar' }}" style="margin-right: 4px;">{{ $fila->tieneError() ? 'No se dio de alta' : 'Alta con aviso' }}</span>
                      <span style="font-size: 0.78rem; color: #556860;">{{ implode(' ', $fila->mensajes ?? []) }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @if($ultimaCarga->filas_error > 0)
            <a href="{{ route('admin.instituciones.padron.problemas', [$institucion, $ultimaCarga]) }}" class="btn-atl linea sm" style="margin-top: 0.7rem;">
              <i class="fa-solid fa-file-arrow-down"></i> Descargar filas con error para corregirlas
            </a>
          @endif
        @endif
      </div>
    @endif
  </div>

  @include('admin.instituciones.partials.padron-personas')
  </div>

  <div class="tabpane" data-tabpane="inst" data-pane="invitaciones" id="tab-invitaciones">
  @include('admin.instituciones.partials.invitaciones')
  </div>
  @endif

  <div class="tabpane" data-tabpane="inst" data-pane="clinico" id="tab-clinico">
  {{-- ══════════ COLABORADORES (plano clínico) ══════════ --}}
  <div class="section-head" id="colaboradores">
    <h2 class="section-title">Colaboradores</h2>
    <span class="section-note">Ordenado por prioridad clínica, no alfabéticamente</span>
    <div class="spacer"></div>
    <span class="locked"><i class="fa-solid fa-lock"></i> Vista exclusiva del plano clínico acreditado</span>
  </div>

  @if(!$esClinico)
    <div class="card-atl" style="margin-bottom: 2rem;">
      <div class="nota-protocolo">
        <i class="fa-solid fa-user-lock"></i>
        <span>Tu cuenta administra la plataforma pero no tiene <b>acreditación clínica</b>, así que no ve identidades, puntajes ni fichas individuales.
          La acreditación se otorga con <code>php artisan atulado:superadmin correo --clinico</code>.</span>
      </div>
    </div>
  @else
    <form method="GET" action="{{ route('admin.instituciones.show', $institucion) }}#colaboradores" class="toolbar" id="filtros-colab">
      <div class="input-icon" style="min-width: 230px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input class="input" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre, correo, número de empleado o folio…">
      </div>
      <select class="select" name="departamento" style="width: auto;" onchange="this.form.submit()">
        <option value="">Todos los departamentos</option>
        @foreach($opcionesArea as [$id, $nombre])
          <option value="{{ $id }}" @selected((int) ($filtros['departamento'] ?? 0) === $id)>{{ $nombre }}</option>
        @endforeach
      </select>
      <select class="select" name="turno" style="width: auto;" onchange="this.form.submit()">
        <option value="">Todos los turnos</option>
        @foreach(['matutino' => 'Matutino', 'vespertino' => 'Vespertino', 'nocturno' => 'Nocturno', 'mixto' => 'Mixto'] as $valor => $texto)
          <option value="{{ $valor }}" @selected(($filtros['turno'] ?? '') === $valor)>{{ $texto }}</option>
        @endforeach
      </select>
      <input type="hidden" name="semaforo" value="{{ $filtros['semaforo'] ?? '' }}">
      <div class="segmented">
        @foreach(['' => 'Todos', 'ROJO' => 'Rojo', 'NARANJA' => 'Naranja', 'AMARILLO' => 'Amarillo', 'VERDE' => 'Verde', 'SILENCIO' => 'Silencio'] as $valor => $texto)
          <button type="submit" class="{{ strtoupper($filtros['semaforo'] ?? '') === $valor ? 'on' : '' }}"
                  onclick="this.form.semaforo.value = '{{ $valor }}'">{{ $texto }}</button>
        @endforeach
      </div>
      <div class="spacer"></div>
      <label class="row small muted" style="gap: .35rem;">
        <input type="checkbox" name="bandera" value="1" @checked(!empty($filtros['bandera'])) onchange="this.form.submit()"> Sólo con bandera léxica
      </label>
    </form>

    <div class="card" style="margin-bottom: 2rem;">
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th style="min-width: 210px;">Colaborador</th>
              <th>Puesto</th><th>Departamento</th><th>Turno / horario</th>
              <th>Semáforo</th><th class="num">WHO-5</th><th class="num">MDI</th><th>ASQ</th>
              <th class="num">Último registro</th><th class="num">Racha</th><th>Señales</th>
              <th>Contacto de emergencia</th><th class="actions">Ficha</th>
            </tr>
          </thead>
          <tbody>
            @forelse($colaboradores as $fila)
              @php
                $m = $fila['membresia'];
                $nivel = $fila['semaforo']['nivel'];
                $grave = in_array($nivel, ['ROJO', 'ROJO_AGUDO'], true);
                $iniciales = \App\Models\Institucion::calcularIniciales($m->user?->name ?? '?');
                $ultimo = $fila['ultimo_registro'];
              @endphp
              <tr class="{{ $nivel === 'ROJO_AGUDO' ? 'row-critical' : ($nivel === 'ROJO' ? 'row-red' : '') }}">
                <td>
                  <div class="who-cell">
                    <div class="av" style="background: {{ \App\Services\ExpedienteClinicoService::COLOR_NIVEL[$nivel] }};{{ $nivel === 'AMARILLO' ? 'color:#201900;' : '' }}">{{ $iniciales }}</div>
                    <div>
                      <div class="nm">{{ $m->user?->name }}</div>
                      <div class="sub mono">{{ $m->folio }}@if($m->numero_empleado) · Emp. {{ $m->numero_empleado }}@endif</div>
                    </div>
                  </div>
                </td>
                <td>{{ $m->puesto ?: '—' }}</td>
                <td>{{ $m->departamento?->nombre ?: '—' }}</td>
                <td class="small">
                  {{ $m->turno ? ucfirst($m->turno) : '—' }}
                  @if($m->horario)<br><span class="muted mono xsmall">{{ $m->horario }}</span>@endif
                </td>
                <td>@include('admin.instituciones.partials.semaforo', ['s' => $fila['semaforo']])</td>
                <td class="num">{{ $fila['who5'] ?? '—' }}</td>
                <td class="num">{{ $fila['mdi'] ?? '—' }}</td>
                <td><span class="chip {{ $fila['asq']['tono'] }}">{{ $fila['asq']['texto'] }}</span></td>
                <td class="num">
                  @if(!$ultimo) —
                  @elseif($ultimo->isToday()) hoy {{ $ultimo->format('H:i') }}
                  @elseif($ultimo->isYesterday()) ayer {{ $ultimo->format('H:i') }}
                  @else {{ $ultimo->format('d/m H:i') }}
                  @endif
                </td>
                <td class="num">{{ $fila['racha'] }} d</td>
                <td>
                  @forelse($fila['senales'] as $senal)
                    <span class="chip {{ $senal['tono'] }}">{{ $senal['texto'] }}</span>
                  @empty
                    <span class="chip">—</span>
                  @endforelse
                </td>
                <td class="small">
                  @if($c = $fila['contacto'])
                    {{ $c->nombre }}@if($c->relacion) · {{ \Illuminate\Support\Str::lower($c->relacion) }}@endif
                    <br><span class="mono xsmall muted">{{ $c->telefono }}</span>
                  @else
                    — <span class="chip amber xsmall">sin registrar</span>
                  @endif
                </td>
                <td class="actions">
                  <button type="button" class="btn btn-sm {{ $grave ? 'btn-danger' : '' }}"
                          data-ficha="{{ route('admin.instituciones.ficha', [$institucion, $m]) }}"
                          data-nombre="{{ $m->user?->name }}" data-folio="{{ $m->folio }}">
                    <i class="fa-solid fa-folder-open"></i> Abrir
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="13" style="text-align: center; padding: 2rem 1rem; color: #6E887E;">
                  @if(array_filter($filtros))
                    Nadie coincide con los filtros. <a href="{{ route('admin.instituciones.show', $institucion) }}#colaboradores" style="color: #2E5D4B;">Quitar filtros</a>
                  @else
                    Aún no hay personas en el padrón. Súbelo en Excel desde «Padrón de personas».
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-foot row">
        <span>
          Mostrando {{ $colaboradores->count() }} de {{ $colaboradores->total() }} {{ $colaboradores->total() === 1 ? 'persona' : 'personas' }}
          · <b>Racha</b> = días consecutivos con registro diario · <b>Señales</b> = reglas de vigilancia R1–R4 y bandera del filtro léxico (últimos 14 días).
        </span>
        <div class="spacer"></div>
        @if($colaboradores->lastPage() > 1)
          <div class="row" style="gap: .3rem;">
            <a class="btn btn-sm {{ $colaboradores->onFirstPage() ? 'disabled' : '' }}" href="{{ $colaboradores->previousPageUrl() ?? '#' }}" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></a>
            @foreach($colaboradores->getUrlRange(max(1, $colaboradores->currentPage() - 2), min($colaboradores->lastPage(), $colaboradores->currentPage() + 2)) as $num => $url)
              <a class="btn btn-sm {{ $num === $colaboradores->currentPage() ? 'btn-primary' : '' }}" href="{{ $url }}">{{ $num }}</a>
            @endforeach
            <a class="btn btn-sm {{ $colaboradores->hasMorePages() ? '' : 'disabled' }}" href="{{ $colaboradores->nextPageUrl() ?? '#' }}" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></a>
          </div>
        @endif
      </div>
    </div>
  @endif
  </div>
</div>

@if($esClinico)
  {{-- Motivo de consulta: se pide antes de abrir cualquier ficha y queda en la bitácora --}}
  <div class="modal-backdrop" id="m-motivo">
    <div class="modal narrow">
      <div class="modal-head">
        <div><h2>Abrir ficha individual</h2><div class="sub" id="motivo-persona"></div></div>
        <button class="x" type="button" data-close="m-motivo" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="form-motivo">
        <div class="modal-body">
          <div class="field mb-2">
            <label for="motivo-preset">Motivo de la consulta</label>
            <select class="select" id="motivo-preset">
              <option>Revisión de caso rojo abierto</option>
              <option>Seguimiento de caso en atención</option>
              <option>Revisión por señal de vigilancia</option>
              <option>Revisión por bandera léxica</option>
              <option>Seguimiento clínico programado</option>
              <option value="">Otro (escríbelo)</option>
            </select>
          </div>
          <div class="field mb-2">
            <label for="motivo-texto">Detalle (opcional si elegiste un motivo)</label>
            <textarea class="input" id="motivo-texto" rows="2" maxlength="400" placeholder="Ej. llamada de la guardia de las 08:52"></textarea>
            <span class="form-error" id="motivo-error" style="display: none;"></span>
          </div>
          <div class="alert warn">
            <div class="ico"><i class="fa-solid fa-fingerprint"></i></div>
            <div class="txt"><b>La apertura queda en la bitácora</b><span>Se registra tu nombre, la hora y el motivo. El registro no se puede editar ni borrar.</span></div>
          </div>
        </div>
        <div class="modal-foot">
          <div class="spacer"></div>
          <button type="button" class="btn" data-close="m-motivo">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="motivo-enviar"><i class="fa-solid fa-folder-open"></i> Abrir ficha</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-backdrop" id="m-ficha">
    <div class="modal wide" id="ficha-contenido"></div>
  </div>
@endif

@if($esAdmin)
{{-- ══════════ MODAL DE EDICIÓN ══════════ --}}
<div class="modal-backdrop" id="m-editar-institucion">
  <div class="modal modal-atl">
    <div class="modal-head">
      <div>
        <h2>Editar {{ $institucion->nombre_corto }}</h2>
        <div class="sub">Datos de la empresa, enlace de RRHH, profesional designado, contrato y estructura.</div>
      </div>
      <button class="x" type="button" data-close="m-editar-institucion" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form method="POST" action="{{ route('admin.instituciones.update', $institucion) }}">
      @csrf
      @method('PUT')
      <input type="hidden" name="_form" value="editar">

      <div class="modal-body">
        @include('admin.instituciones.partials.campos', ['i' => $institucion, 'prefijo' => 'editar'])

        <div class="form-bloque" id="editar-sec-contrato">
          <div class="form-bloque-titulo">4. Contrato</div>
          @include('admin.instituciones.partials.contrato', ['i' => $institucion, 'prefijo' => 'editar'])
        </div>

        <div class="form-bloque" id="editar-sec-estructura">
          <div class="form-bloque-titulo">5. Estructura de Macro-Grupos</div>
          @error('areas') <div class="form-error" style="margin-bottom: 0.6rem;">{{ $message }}</div> @enderror

          @foreach($arbol as $macro)
            @php $m = $macro['modelo']; @endphp
            <div class="area-macro">
              <div class="area-fila">
                <i class="fa-solid fa-layer-group icono"></i>
                <input type="text" name="areas[{{ $m->id }}][nombre]" class="form-input-styled @error('areas.'.$m->id.'.nombre') con-error @enderror" maxlength="150"
                       value="{{ old('areas.'.$m->id.'.nombre', $m->nombre) }}" aria-label="Nombre de la macro-área">
                <span class="area-personas">{{ $macro['personas'] }} {{ $macro['personas'] === 1 ? 'persona' : 'personas' }}</span>
                <label class="area-quitar {{ $macro['personas'] > 0 ? 'bloqueado' : '' }}"
                       title="{{ $macro['personas'] > 0 ? 'Tiene personas asignadas: reasígnalas antes de quitarla.' : 'Quitar esta macro-área y sus áreas internas' }}">
                  <input type="checkbox" name="areas[{{ $m->id }}][quitar]" value="1" @disabled($macro['personas'] > 0) @checked(old('areas.'.$m->id.'.quitar'))> Quitar
                </label>
              </div>
              @error('areas.'.$m->id.'.nombre') <span class="form-error">{{ $message }}</span> @enderror

              @foreach($macro['hijos'] as $hijo)
                @php $h = $hijo['modelo']; @endphp
                <div class="area-fila hija">
                  <input type="text" name="areas[{{ $h->id }}][nombre]" class="form-input-styled @error('areas.'.$h->id.'.nombre') con-error @enderror" maxlength="150"
                         value="{{ old('areas.'.$h->id.'.nombre', $h->nombre) }}" aria-label="Nombre del área">
                  <span class="area-personas">{{ $hijo['personas'] }} {{ $hijo['personas'] === 1 ? 'persona' : 'personas' }}</span>
                  <label class="area-quitar {{ $hijo['personas'] > 0 ? 'bloqueado' : '' }}"
                         title="{{ $hijo['personas'] > 0 ? 'Tiene personas asignadas: reasígnalas antes de quitarla.' : 'Quitar esta área' }}">
                    <input type="checkbox" name="areas[{{ $h->id }}][quitar]" value="1" @disabled($hijo['personas'] > 0) @checked(old('areas.'.$h->id.'.quitar'))> Quitar
                  </label>
                </div>
                @error('areas.'.$h->id.'.nombre') <span class="form-error" style="padding-left: 1.4rem;">{{ $message }}</span> @enderror
              @endforeach

              <div class="area-agregar">
                <input type="text" name="areas[{{ $m->id }}][nuevas]" class="form-input-styled" maxlength="2000"
                       value="{{ old('areas.'.$m->id.'.nuevas') }}"
                       placeholder="+ Agregar cuadrillas o salones dentro de «{{ $m->nombre }}» (separados por comas)">
              </div>
            </div>
          @endforeach

          <div style="margin-top: 0.85rem;">
            <label class="form-group-label" for="editar-nuevas_macro">Agregar macro-áreas nuevas (separadas por comas)</label>
            <input type="text" id="editar-nuevas_macro" name="nuevas_macro" class="form-input-styled @error('nuevas_macro') con-error @enderror" maxlength="2000"
                   value="{{ old('nuevas_macro') }}" placeholder="Ej. Logística, Mantenimiento">
            @error('nuevas_macro') <span class="form-error">{{ $message }}</span> @enderror
            <span class="form-hint-styled">
              Cada área necesita un nombre distinto: el padrón en Excel asigna a cada persona su área por nombre.
              Un área con personas asignadas no se puede quitar.
            </span>
          </div>
        </div>
      </div>

      <div class="modal-foot">
        <span style="font-size: 0.76rem; color: #6E887E;"><i class="fa-solid fa-clock-rotate-left"></i> Los cambios se aplican al guardar</span>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn-atl linea" data-close="m-editar-institucion">Cancelar</button>
          <button type="submit" class="btn-atl primario"><i class="fa-solid fa-check"></i> Guardar cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('vendor/paneles/panel.js') }}?v={{ filemtime(public_path('vendor/paneles/panel.js')) }}"></script>
<script>
  // ── Pestañas: el ancla de la URL decide cuál abrir (#tab-padron, #colaboradores…) ──
  function abrirPestana(nombre) {
    const boton = document.querySelector(`#tabs-inst .tab[data-pane="${nombre}"]`);
    if (boton) boton.click();
  }
  function pestanaDesdeAncla() {
    const ancla = window.location.hash.replace('#', '');
    if (ancla === 'colaboradores') return abrirPestana('clinico');
    if (ancla.startsWith('tab-')) abrirPestana(ancla.slice(4));
  }
  document.addEventListener('DOMContentLoaded', () => {
    pestanaDesdeAncla();
    document.querySelectorAll('#tabs-inst .tab').forEach(t => t.addEventListener('click', () =>
      history.replaceState(null, '', '#tab-' + t.dataset.pane)));
  });
  window.addEventListener('hashchange', pestanaDesdeAncla);

  // Buscador de las tablas de padrón e invitaciones
  function filtrarTabla(input, idTabla) {
    const texto = input.value.trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    document.querySelectorAll(`#${idTabla} tbody tr`).forEach(fila => {
      const contenido = fila.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
      fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
  }

  function contarSeleccion() {
    const n = document.querySelectorAll('.chk-invitar:checked').length;
    const contador = document.getElementById('n-seleccion');
    if (contador) contador.textContent = n;
    const boton = document.getElementById('btn-invitar-seleccion');
    if (boton) boton.disabled = n === 0;
  }

  // Editar una persona del padrón: el mismo modal para todas
  document.addEventListener('click', (e) => {
    const boton = e.target.closest('[data-editar-persona]');
    if (!boton) return;
    const form = document.getElementById('form-persona-editar');
    form.action = boton.dataset.url;
    form.querySelector('[name=_url]').value = boton.dataset.url;
    form.nombre.value = boton.dataset.nombre || '';
    form.numero_empleado.value = boton.dataset.numero || '';
    form.departamento_id.value = boton.dataset.area || '';
    form.puesto.value = boton.dataset.puesto || '';
    form.turno.value = boton.dataset.turno || '';
    form.horario.value = boton.dataset.horario || '';
    const activa = boton.dataset.activa === '1';
    form.nombre.readOnly = activa;
    form.querySelector('[data-aviso-nombre]').style.display = activa ? 'block' : 'none';
    openModal('m-persona-editar');
  });

  @if($errors->any() && in_array(old('_form'), ['persona-nueva', 'persona-editar'], true))
    document.addEventListener('DOMContentLoaded', () => { abrirPestana('padron'); openModal('m-{{ old('_form') === 'persona-nueva' ? 'persona-nueva' : 'persona-editar' }}'); });
  @endif

  // Los botones "Editar" de cada ficha abren el modal ya desplazado a su sección.
  document.addEventListener('click', (e) => {
    const disparador = e.target.closest('[data-seccion]');
    if (!disparador) return;
    const seccion = document.getElementById('editar-sec-' + disparador.dataset.seccion);
    if (seccion) setTimeout(() => seccion.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
  });

  @if($esClinico)
  // ── Ficha individual: motivo → bitácora → HTML de la ficha ──
  (() => {
    let urlFicha = null;
    const form = document.getElementById('form-motivo');
    const error = document.getElementById('motivo-error');
    const enviar = document.getElementById('motivo-enviar');
    const contenedor = document.getElementById('ficha-contenido');

    document.addEventListener('click', (e) => {
      const boton = e.target.closest('[data-ficha]');
      if (!boton) return;
      urlFicha = boton.dataset.ficha;
      document.getElementById('motivo-persona').textContent = boton.dataset.nombre + ' · ' + boton.dataset.folio;
      error.style.display = 'none';
      openModal('m-motivo');
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const preset = document.getElementById('motivo-preset').value;
      const detalle = document.getElementById('motivo-texto').value.trim();
      const motivo = [preset, detalle].filter(Boolean).join(' — ');

      enviar.disabled = true;
      try {
        const respuesta = await fetch(urlFicha, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'text/html',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ motivo }),
        });

        if (respuesta.status === 422) {
          const datos = await respuesta.json();
          error.textContent = (datos.errors?.motivo || ['Revisa el motivo.'])[0];
          error.style.display = 'block';
          return;
        }
        if (!respuesta.ok) throw new Error(respuesta.status);

        contenedor.innerHTML = await respuesta.text();
        closeModal('m-motivo');
        document.getElementById('motivo-texto').value = '';
        prepararFicha(contenedor);
        openModal('m-ficha');
      } catch (err) {
        error.textContent = 'No se pudo abrir la ficha. Recarga la página e intenta de nuevo.';
        error.style.display = 'block';
      } finally {
        enviar.disabled = false;
      }
    });

    // Lo inyectado no pasó por el arranque de panel.js: pestañas, modales y gráficas se conectan aquí.
    function prepararFicha(raiz) {
      // Los modales de acción (escalar, resumen, cierre) viven en <body>, no dentro del modal de la ficha.
      document.querySelectorAll('body > [data-ficha-modal]').forEach(m => m.remove());
      raiz.querySelectorAll('[data-ficha-modal]').forEach(m => document.body.appendChild(m));
      const modales = [...document.querySelectorAll('body > [data-ficha-modal]')];

      wireTabs(raiz);
      [raiz, ...modales].forEach(r => {
        r.querySelectorAll('[data-open]').forEach(b => b.addEventListener('click', ev => { ev.preventDefault(); openModal(b.dataset.open); }));
        r.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', ev => { ev.preventDefault(); closeModal(b.dataset.close); }));
      });
      modales.forEach(bd => bd.addEventListener('click', ev => { if (ev.target === bd) closeModal(bd.id); }));

      const datos = JSON.parse(raiz.querySelector('#ficha-datos')?.textContent || '{}');
      const animo = raiz.querySelector('#ch-animo');
      if (animo && datos.animo?.data.length >= 2) {
        lineChart(animo, {
          height: 200, labels: datos.animo.labels, min: 0, max: 4, ticks: 4,
          ...(datos.animo.base30 !== null ? { refLine: datos.animo.base30, refLabel: 'línea base 30 d' } : {}),
          series: [{ name: 'Valor invertido', color: '#2E5D4B', data: datos.animo.data }],
        });
      }
      const who5 = raiz.querySelector('#ch-who5');
      if (who5 && datos.who5?.data.length >= 2) {
        lineChart(who5, {
          height: 190, labels: datos.who5.labels, min: 0, max: 100, ticks: 4, refLine: 50, refLabel: 'corte de riesgo',
          series: [{ name: 'WHO-5', color: '#2A78D6', data: datos.who5.data }],
        });
      }
    }
  })();
  @endif

  @if(($errors->any() && old('_form') === 'editar') || request()->boolean('editar'))
    document.addEventListener('DOMContentLoaded', () => openModal('m-editar-institucion'));
  @endif
</script>
@endpush
