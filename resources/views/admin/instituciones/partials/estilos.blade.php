<link rel="stylesheet" href="{{ asset('vendor/paneles/panel.css') }}?v={{ filemtime(public_path('vendor/paneles/panel.css')) }}">
<style>
  .admin-content-canvas { padding: 1.75rem 2rem; max-width: 1400px; margin: 0 auto; }
  @media (max-width: 768px) { .admin-content-canvas { padding: 1rem; } }

  .crumbs-atl { font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem; }
  .crumbs-atl a { color: inherit; text-decoration: none; }
  .crumbs-atl a:hover { color: #2E5D4B; }
  .titulo-atl { font-family: 'Fraunces', serif; font-size: 1.85rem; font-weight: 700; color: #1A2620; margin: 0; }
  .sub-atl { font-size: 0.88rem; color: #556860; margin: 0.35rem 0 0; }

  .btn-atl { display: inline-flex; align-items: center; gap: 6px; border-radius: 9px; padding: 0.55rem 1.1rem; font-size: 0.84rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1.5px solid transparent; line-height: 1.2; }
  .btn-atl.primario { background: #2E5D4B; color: #FFFFFF; }
  .btn-atl.primario:hover { background: #24493B; }
  .btn-atl.suave { background: #EEF4F0; color: #2E5D4B; }
  .btn-atl.suave:hover { background: #DCE8E0; }
  .btn-atl.linea { background: #FFFFFF; color: #556860; border-color: #DCE8E0; }
  .btn-atl.sm { padding: 5px 10px; font-size: 0.78rem; border-radius: 7px; }

  .card-atl { background: #FFFFFF; border-radius: 16px; border: 1.5px solid #DCE8E0; padding: 1.35rem 1.5rem; }
  .card-atl-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.9rem; }
  .card-atl-head h3 { font-family: 'Fraunces', serif; font-size: 1.12rem; font-weight: 700; color: #1A2620; margin: 0; }
  .card-atl-head p { font-size: 0.8rem; color: #6E887E; margin: 0.15rem 0 0; }

  .kv-atl { display: grid; grid-template-columns: minmax(120px, 38%) 1fr; gap: 0.5rem 1rem; margin: 0; font-size: 0.88rem; }
  .kv-atl dt { color: #6E887E; font-size: 0.8rem; }
  .kv-atl dd { margin: 0; color: #1A2620; font-weight: 500; word-break: break-word; }
  .kv-atl dd.vacio { color: #A5B8B0; font-weight: 400; font-style: italic; }

  .logo-inst { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: #FFFFFF; font-family: 'Fraunces', serif; font-weight: 700; font-size: 1rem; flex-shrink: 0; }
  .logo-inst.grande { width: 58px; height: 58px; font-size: 1.3rem; border-radius: 14px; }

  .chip-atl { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #EEF4F0; color: #2E5D4B; white-space: nowrap; }
  .chip-atl.ambar { background: #FBF2D4; color: #8A6D00; }
  .chip-atl.rojo { background: #F8E2DF; color: #B02418; }
  .chip-atl.gris { background: #F1F3F2; color: #6E7C76; }

  /* Formulario */
  .form-group-label { display: block; font-size: 0.8rem; font-weight: 700; color: #1A2620; margin-bottom: 0.35rem; }
  .form-input-styled { width: 100%; padding: 0.55rem 0.8rem; border: 1.5px solid #DCE8E0; border-radius: 8px; font-size: 0.86rem; color: #1A2620; background: #FFFFFF; box-sizing: border-box; transition: border-color 0.2s; font-family: inherit; }
  .form-input-styled:focus { border-color: #2E5D4B; outline: none; box-shadow: 0 0 0 3px rgba(46, 93, 75, 0.12); }
  .form-input-styled.con-error { border-color: #B02418; }
  .form-hint-styled { display: block; font-size: 0.73rem; color: #6E887E; margin-top: 0.3rem; line-height: 1.35; }
  .form-error { display: block; font-size: 0.74rem; color: #B02418; margin-top: 0.3rem; font-weight: 600; }
  .form-bloque { margin-bottom: 1.35rem; padding-bottom: 1.15rem; border-bottom: 1px solid #EEF4F0; }
  .form-bloque:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
  .form-bloque-titulo { font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.06em; margin-bottom: 0.75rem; }
  .form-fila { display: grid; gap: 1rem; margin-bottom: 0.85rem; }
  .form-fila:last-child { margin-bottom: 0; }
  .form-fila.c2-1 { grid-template-columns: 2fr 1fr; }
  .form-fila.c1-1 { grid-template-columns: 1fr 1fr; }
  .form-fila.c3 { grid-template-columns: 1fr 1fr 1fr; }
  @media (max-width: 640px) { .form-fila.c2-1, .form-fila.c1-1, .form-fila.c3 { grid-template-columns: 1fr; } }
  .nota-protocolo { background: #F8FAF9; border: 1px solid #DCE8E0; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; gap: 8px; align-items: flex-start; font-size: 0.75rem; color: #556860; line-height: 1.35; }
  .nota-protocolo i { color: #2E5D4B; margin-top: 2px; }

  /* Modal */
  .modal-atl { max-width: 720px; width: 100%; }
  .modal-atl .modal-head { background: #2E5D4B; color: #FFFFFF; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; }
  .modal-atl .modal-head h2 { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; margin: 0; color: #FFFFFF; }
  .modal-atl .modal-head .sub { color: #A8E6C0; font-size: 0.8rem; margin-top: 3px; }
  .modal-atl .modal-head .x { background: transparent; border: none; color: #FFFFFF; font-size: 1.2rem; cursor: pointer; }
  .modal-atl .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
  .modal-atl .modal-foot { padding: 1rem 1.5rem; background: #F8FAF9; border-top: 1px solid #EEF4F0; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

  /* Editor de estructura */
  .area-macro { border: 1.5px solid #DCE8E0; border-radius: 10px; padding: 0.7rem 0.8rem; margin-bottom: 0.7rem; background: #FBFDFC; }
  .area-fila { display: flex; align-items: center; gap: 0.6rem; }
  .area-fila + .area-fila { margin-top: 0.45rem; }
  .area-fila.hija { padding-left: 1.4rem; position: relative; }
  .area-fila.hija::before { content: ''; position: absolute; left: 0.55rem; top: -0.45rem; bottom: 50%; width: 0.6rem; border-left: 1.5px solid #C2D6CA; border-bottom: 1.5px solid #C2D6CA; border-bottom-left-radius: 4px; }
  .area-fila .form-input-styled { flex: 1; padding: 0.42rem 0.65rem; font-size: 0.84rem; }
  .area-fila .icono { color: #2E5D4B; width: 16px; text-align: center; }
  .area-personas { font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #6E887E; white-space: nowrap; min-width: 82px; text-align: right; }
  .area-quitar { display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #B02418; cursor: pointer; white-space: nowrap; }
  .area-quitar.bloqueado { color: #A5B8B0; cursor: not-allowed; }
  .area-agregar { margin-top: 0.5rem; padding-left: 1.4rem; }
  .area-agregar .form-input-styled { padding: 0.4rem 0.65rem; font-size: 0.8rem; border-style: dashed; }

  /* Árbol de estructura en la ficha */
  .arbol-macro { padding: 0.75rem 0; border-bottom: 1px solid #EEF4F0; }
  .arbol-macro:last-child { border-bottom: none; }
  .arbol-linea { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
  .arbol-linea.macro { font-weight: 700; color: #1A2620; }
  .arbol-linea.hija { padding: 0.3rem 0 0 1.5rem; color: #556860; font-size: 0.85rem; }
  .arbol-conteo { font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #6E887E; white-space: nowrap; }

  /* Padrón por Excel */
  .padron-pasos { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
  .padron-paso { display: flex; gap: 0.8rem; align-items: flex-start; background: #FBFDFC; border: 1.5px solid #DCE8E0; border-radius: 12px; padding: 0.9rem 1rem; }
  .padron-num { width: 28px; height: 28px; border-radius: 50%; background: #2E5D4B; color: #FFFFFF; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .padron-titulo { font-weight: 700; color: #1A2620; font-size: 0.9rem; margin-bottom: 0.2rem; }
  .padron-texto { font-size: 0.78rem; color: #6E887E; margin: 0 0 0.6rem; line-height: 1.4; }
  .padron-resultado { margin-top: 1.1rem; padding-top: 1rem; border-top: 1px solid #EEF4F0; }
  .padron-tabla { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
  .padron-tabla th { position: sticky; top: 0; background: #F8FAF9; text-align: left; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6E887E; padding: 0.5rem 0.7rem; border-bottom: 1px solid #EEF4F0; }
  .padron-tabla td { padding: 0.5rem 0.7rem; border-bottom: 1px solid #F1F5F3; vertical-align: top; color: #1A2620; }
  .padron-tabla td.mono { font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; }

  .grid-fichas { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
</style>
