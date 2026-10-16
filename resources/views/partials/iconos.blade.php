  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/marca/logo-atulado-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/marca/logo-atulado-256.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/marca/apple-touch-icon.png') }}">
  <meta name="theme-color" content="#2E5D4B">
  {{-- App instalable (PWA). El service worker no guarda páginas con datos personales: ver public/sw.js --}}
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="A tu lado">
  {{-- Botones «Instalar app» ([data-pwa-instalar]): sólo se ven si el teléfono
       puede instalarla y aún no está instalada. Android abre el instalador;
       iPhone no lo permite desde la página, así que se explica cómo. --}}
  <style>html:not([data-pwa]) [data-pwa-instalar] { display: none !important; }</style>
  <script>
    (() => {
      if ('serviceWorker' in navigator) {
        addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
      }
      const html = document.documentElement;
      if (matchMedia('(display-mode: standalone)').matches || navigator.standalone) return;

      let instalador = null;
      if (/iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
        html.dataset.pwa = 'ios';
      }
      addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); instalador = e; html.dataset.pwa = 'instalable'; });
      addEventListener('appinstalled', () => {
        instalador = null;
        delete html.dataset.pwa;
        window.showZenAlert?.({
          title: '¡Listo, ya tienes A tu lado!',
          message: 'La app quedó instalada. Búscala en tu pantalla de inicio con el ícono de <strong>A tu lado</strong>: se abre directo, sin navegador.',
          buttonText: 'Genial',
          type: 'success',
          icon: 'fa-circle-check',
        });
      });

      const instalar = async () => {
        if (instalador) {
          instalador.prompt();
          await instalador.userChoice;
          instalador = null;
          delete html.dataset.pwa;
          return;
        }
        // iPhone no deja instalar desde la página: se guía en 3 pasos.
        const pasos = '<span style="display:block;text-align:left;line-height:1.9">'
          + '<strong>1.</strong> Toca el botón <strong>Compartir</strong> <i class="fa-solid fa-arrow-up-from-bracket"></i> (abajo en Safari, arriba en Chrome).<br>'
          + '<strong>2.</strong> Desliza y elige <strong>«Agregar a inicio»</strong> <i class="fa-regular fa-square-plus"></i>.<br>'
          + '<strong>3.</strong> Toca <strong>«Agregar»</strong>. ¡Listo! Aparecerá en tu pantalla de inicio.</span>';
        window.showZenAlert
          ? showZenAlert({ title: 'Instala A tu lado en tu iPhone', message: pasos, buttonText: 'Entendido', type: 'success', icon: 'fa-mobile-screen' })
          : alert('Toca Compartir y elige «Agregar a inicio».');
      };

      document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-pwa-instalar]')) return;
        e.preventDefault();
        instalar();
      });

      // Invitación única al terminar un cuestionario: si dice «Ahora no», no vuelve a salir.
      window.invitarInstalarPWA = async () => {
        if (!html.dataset.pwa || !window.showZenConfirm) return;
        try {
          if (localStorage.getItem('pwa-invitacion')) return;
          localStorage.setItem('pwa-invitacion', '1');
        } catch (e) { return; }
        const si = await showZenConfirm({
          title: '¿Quieres tenerla a un toque?',
          message: 'Instala A tu lado en tu pantalla de inicio para abrirla rápido cuando la necesites, como cualquier otra app.',
          confirmText: 'Instalar',
          cancelText: 'Ahora no',
          type: 'success',
          icon: 'fa-mobile-screen',
        });
        if (si) instalar();
      };
    })();
  </script>
