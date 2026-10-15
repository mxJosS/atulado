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
      addEventListener('appinstalled', () => { instalador = null; delete html.dataset.pwa; });

      document.addEventListener('click', async (e) => {
        if (!e.target.closest('[data-pwa-instalar]')) return;
        e.preventDefault();
        if (instalador) {
          instalador.prompt();
          await instalador.userChoice;
          instalador = null;
          delete html.dataset.pwa;
          return;
        }
        const pasos = 'Abre esta página en Safari, toca el botón Compartir (el cuadro con una flecha hacia arriba) y elige «Agregar a inicio».';
        window.showZenConfirm
          ? showZenConfirm({ title: 'Instala A tu lado en tu iPhone', message: pasos, confirmText: 'Entendido', cancelText: 'Cerrar', type: 'success', icon: 'fa-mobile-screen' })
          : alert(pasos);
      });
    })();
  </script>
