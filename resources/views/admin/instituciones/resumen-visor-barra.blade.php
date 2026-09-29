{{--
  Barra del visor del resumen clínico. Se inserta al inicio del <body> del
  documento guardado; no se imprime. $emitirOtro sólo existe en el panel.
--}}
<style>
  .barra-visor { position: sticky; top: 0; z-index: 10; background: #0A1410 radial-gradient(circle at 10% 0%, rgba(46, 93, 75, 0.8), rgba(10, 20, 16, 0) 55%); color: #E6ECE8; font: 13px/1.45 'Manrope', system-ui, sans-serif; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
  .barra-visor .fila { max-width: 210mm; margin: 0 auto; padding: 12px 16px; display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
  .barra-visor .titulo { font: 700 11px 'IBM Plex Mono', monospace; letter-spacing: .08em; text-transform: uppercase; color: #A8E6C0; }
  .barra-visor h1 { font: 700 17px 'Fraunces', serif; margin: 2px 0; color: #FFFFFF; }
  .barra-visor .meta { font-size: 12px; color: #B9C9C0; }
  .barra-visor .botones { display: flex; gap: 8px; flex-wrap: wrap; }
  .barra-visor a, .barra-visor button { font: 700 13px 'Manrope', sans-serif; padding: 8px 16px; border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.18); background: rgba(255, 255, 255, 0.06); color: #FFFFFF; cursor: pointer; text-decoration: none; }
  .barra-visor button.primario { background: #A8E6C0; border-color: #A8E6C0; color: #0A1410; }
  .barra-visor .aviso-destino { max-width: 210mm; margin: 0 auto; padding: 0 16px 12px; font-size: 12px; color: #B9C9C0; }
  @media print { .barra-visor { display: none !important; } }
</style>
<div class="barra-visor">
  <div class="fila">
    <div>
      <div class="titulo">a tu lado</div>
      <h1>Resumen clínico individual</h1>
      <div class="meta">
        {{ $institucion }} · {{ $entrega->folio }}@if($periodo) · {{ $periodo }}@endif
        · Confidencial · profesional designado
        @if($entrega->paginas) · {{ $entrega->paginas }} {{ $entrega->paginas === 1 ? 'página' : 'páginas' }}@endif
      </div>
    </div>
    <div class="botones">
      @isset($emitirOtro)
        <a href="{{ $emitirOtro }}">Emitir otro</a>
      @endisset
      <button type="button" class="primario" onclick="window.print()">Descargar PDF</button>
    </div>
  </div>
  <div class="aviso-destino">
    @if(isset($emitirOtro) && session('success'))<b style="color: #A8E6C0;">{{ session('success') }}</b><br>@endif
    Destinatario: {{ $entrega->destinatario_nombre }}. Documento confidencial con marca de agua nominal y folio.
    El enlace expira el {{ $entrega->vence_en->format('d/m/Y H:i') }}; el acceso queda asentado en la bitácora.
    «Descargar PDF» abre la impresión: elige «Guardar como PDF».
  </div>
</div>
