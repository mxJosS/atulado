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
  <style>html:not([data-pwa]) [data-pwa-instalar], html:not([data-pwa]) .pwa-solo-instalable { display: none !important; }</style>
  <script>
    (() => {
      if ('serviceWorker' in navigator) {
        addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
      }
      const html = document.documentElement;
      if (matchMedia('(display-mode: standalone)').matches || navigator.standalone) return;

      let instalador = null;
      const ua = navigator.userAgent;
      const ios = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
      // Botón visible en cualquier navegador; Chrome/Edge lo cambian a 'instalable' al ofrecer su instalador.
      html.dataset.pwa = ios ? 'ios' : 'guia';
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
          html.dataset.pwa = 'guia'; // si se instaló, «appinstalled» lo oculta
          return;
        }
        // Sin instalador nativo (iPhone, Safari, Firefox, Opera…): se guía según el navegador.
        const lista = (p) => '<span style="display:block;text-align:left;line-height:1.9">'
          + p.map((t, i) => '<strong>' + (i + 1) + '.</strong> ' + t).join('<br>') + '</span>';
        const movil = /android|mobile/i.test(ua);
        let titulo = 'Instala A tu lado';
        let pasos;
        if (ios) {
          titulo = 'Instala A tu lado en tu iPhone';
          pasos = lista([
            'Toca el botón <strong>Compartir</strong> <i class="fa-solid fa-arrow-up-from-bracket"></i> (abajo en Safari, arriba en Chrome).',
            'Desliza y elige <strong>«Agregar a inicio»</strong> <i class="fa-regular fa-square-plus"></i>.',
            'Toca <strong>«Agregar»</strong>. ¡Listo! Aparecerá en tu pantalla de inicio.',
          ]);
        } else if (/firefox|fxios/i.test(ua) && !movil) {
          pasos = 'Firefox en computadora todavía no permite instalar apps web. Abre <strong>atulado.com.mx</strong> en <strong>Chrome</strong>, <strong>Edge</strong> o <strong>Safari</strong> y toca «Instalar la app». Mientras, puedes guardarla en favoritos con <strong>Ctrl + D</strong>.';
        } else if (/safari/i.test(ua) && !/chrome|chromium|crios|edg|opr|firefox/i.test(ua)) {
          pasos = lista([
            'En la barra de menús, abre <strong>Archivo</strong> (o el botón Compartir <i class="fa-solid fa-arrow-up-from-bracket"></i>).',
            'Elige <strong>«Agregar al Dock»</strong>.',
            'Confirma con <strong>«Agregar»</strong>. La encontrarás en tu Dock y en Launchpad.',
          ]);
        } else {
          pasos = lista([
            'Abre el menú del navegador <strong>⋮</strong> o <strong>⋯</strong> (arriba a la derecha' + (movil ? ', o abajo en algunos celulares' : '') + ').',
            'Elige <strong>«Instalar A tu lado»</strong>, <strong>«Instalar app»</strong> o <strong>«Agregar a pantalla de inicio»</strong>.',
            'Confirma. ¡Listo! Aparecerá con su ícono' + (movil ? ' en tu pantalla de inicio.' : ' en tu escritorio y menú de inicio.'),
          ]);
        }
        window.showZenAlert
          ? showZenAlert({ title: titulo, message: pasos, buttonText: 'Entendido', type: 'success', icon: 'fa-mobile-screen' })
          : alert('Busca «Instalar app» o «Agregar a inicio» en el menú del navegador.');
      };

      document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-pwa-instalar]')) return;
        e.preventDefault();
        instalar();
      });

      // Invitación única al terminar un cuestionario: si dice «Ahora no», no vuelve a salir.
      window.invitarInstalarPWA = async () => {
        // Sólo donde instalar es real: Chrome/Edge con instalador, o iPhone.
        if (!['instalable', 'ios'].includes(html.dataset.pwa) || !window.showZenConfirm) return;
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
