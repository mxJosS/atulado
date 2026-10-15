/*
 * Service worker de A tu lado.
 *
 * Privacidad primero: NUNCA se guardan páginas (HTML) ni respuestas de la app,
 * sólo archivos públicos (CSS, JS, imágenes, fuentes). Así, en un celular
 * compartido, nadie puede ver sin sesión el registro diario o el plan de otra
 * persona.
 *
 * Sin conexión, cualquier página muestra /sin-conexion.html: las líneas de
 * crisis con llamada de un toque y un ejercicio de respiración.
 *
 * Al cambiar este archivo, sube VERSION para que los teléfonos lo actualicen.
 */
const VERSION = 'atulado-v1';
const SIN_CONEXION = '/sin-conexion.html';
const ESTATICOS = /^\/(css|js|images|vendor|build|fonts)\//;

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(VERSION).then((c) => c.addAll([SIN_CONEXION, '/images/marca/logo-atulado-128.png'])));
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((nombres) => Promise.all(nombres.filter((n) => n !== VERSION).map((n) => caches.delete(n))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);

  // Páginas: siempre de la red; sin red, la página de crisis.
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match(SIN_CONEXION)));
    return;
  }

  // Archivos públicos del propio sitio: del caché al instante y se refrescan detrás.
  if (url.origin === location.origin && ESTATICOS.test(url.pathname)) {
    e.respondWith(
      caches.open(VERSION).then(async (cache) => {
        const guardado = await cache.match(req);
        const red = fetch(req).then((res) => {
          if (res.ok) cache.put(req, res.clone());
          return res;
        }).catch(() => guardado);
        return guardado || red;
      })
    );
  }
  // Todo lo demás (formularios, datos, otros dominios) va directo a la red.
});
