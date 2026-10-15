  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/marca/logo-atulado-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/marca/logo-atulado-256.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/marca/apple-touch-icon.png') }}">
  <meta name="theme-color" content="#2E5D4B">
  {{-- App instalable (PWA). El service worker no guarda páginas con datos personales: ver public/sw.js --}}
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="A tu lado">
  <script>
    if ('serviceWorker' in navigator) {
      addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
  </script>
