/* ==========================================================================
   A TU LADO — Movimiento de la marca en todo el proyecto (Motion · https://motion.dev)

   Tranquilo a propósito: lo que entra en pantalla aparece suave y en cascada,
   el árbol «respira», los destellos titilan y el halo de cada portada se
   desplaza con el scroll. Las líneas de crisis nunca se ocultan ni se retrasan.

   Marcas en las vistas:
     data-aparecer             aparece al entrar en pantalla (sube y se aclara)
     data-aparecer="suave"     sólo se aclara (elementos que ya usan transform)
     data-aparecer="enfoque"   pasa de desenfocado a nítido (títulos)
     data-aparecer="escribir"  se revela de arriba abajo, como escrito a mano
     data-aparecer="palabras"  el texto aparece palabra por palabra (títulos grandes)
     data-aparecer-grupo       sus hijos aparecen en cascada
     data-retraso="0.5"        segundos extra antes de aparecer
     data-flotar               flota despacio (el árbol)
     data-destello             titila
     data-mecer                se mece como hoja al viento
     data-caer                 cae despacio, girando, una y otra vez (hojas)
     data-paralaje             se desplaza con el scroll de su sección
     data-paralaje="suave"     igual, sin crecer (tarjetas y escenas)
     data-foco                 una luz sigue al cursor (con ::after en el CSS)

   Sin Motion, sin JavaScript o con «reducir movimiento» en el sistema, todo
   se ve completo y quieto. Estado inicial oculto: sección 11 de publico.css;
   respaldo si Motion tarda: <head> de layouts/guest.
   ========================================================================== */
(() => {
  const raiz = document.documentElement;
  const M = window.Motion;

  if (!M || matchMedia('(prefers-reduced-motion: reduce)').matches) {
    raiz.classList.remove('anim');
    return;
  }
  window.__animListo = true; // el respaldo del <head> ya no hace falta

  const { animate, inView, scroll } = M;
  const SALIDA_SUAVE = [0.22, 1, 0.36, 1];
  const ESCALON = 0.07;         // segundos entre elementos de una misma cascada
  const ESCALON_MAX = 0.5;
  const observados = new WeakSet();
  let limpiezas = [];

  /* ── 1. Aparecer al entrar en pantalla ──────────────────────────────────── */
  // Lo que entra en pantalla en el mismo cuadro forma una cascada, en el orden
  // del documento: así una cuadrícula aparece fila por fila al bajar.
  let cola = [];

  function encolar(el) {
    if (cola.push(el) === 1) requestAnimationFrame(vaciarCola);
  }

  function vaciarCola() {
    const lote = cola.sort((a, b) => (a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1));
    cola = [];
    lote.forEach((el, i) => aparecer(el, Math.min(i * ESCALON, ESCALON_MAX)));
  }

  function aparecer(el, escalon) {
    const tipo = el.dataset.aparecer ?? el.parentElement?.dataset.aparecerGrupo ?? '';
    if (tipo === 'palabras') return aparecerPalabras(el, escalon);

    // Se sube con la propiedad «translate», no con «transform»: así se suma al
    // transform propio del elemento y a los de :hover en lugar de pisarlos.
    const cuadros = { opacity: [0, 1] };
    let duracion = 0.7;

    if (tipo === 'escribir') {
      cuadros.clipPath = ['inset(0 0 100% 0)', 'inset(0 0 0% 0)'];
      duracion = 1.6;
    } else if (tipo === 'enfoque') {
      cuadros.filter = ['blur(8px)', 'blur(0px)'];
      cuadros.translate = ['0px 10px', '0px 0px'];
      duracion = 0.9;
    } else if (tipo !== 'suave') {
      cuadros.translate = ['0px 18px', '0px 0px'];
    }

    // Motion deja escritos los valores finales justo después de terminar; un
    // par de cuadros más tarde se devuelve el estilo original del elemento.
    const estiloOriginal = el.getAttribute('style');
    const restaurar = () => {
      if (estiloOriginal === null) el.removeAttribute('style');
      else el.setAttribute('style', estiloOriginal);
    };
    const terminar = () => {
      el.dataset.visto = '';
      requestAnimationFrame(() => requestAnimationFrame(restaurar));
    };

    const control = animate(el, cuadros, {
      duration: duracion,
      delay: escalon + (parseFloat(el.dataset.retraso) || 0),
      ease: SALIDA_SUAVE,
    });
    (control.finished ?? Promise.resolve(control)).then(terminar, terminar);
  }

  // Títulos palabra por palabra: cada palabra en su <span class="palabra">,
  // respetando las etiquetas internas (<em>, <br>).
  function partirEnPalabras(el) {
    if (el.hasAttribute('data-partido')) return;
    el.setAttribute('data-partido', '');
    const recorrer = (nodo) => {
      [...nodo.childNodes].forEach((hijo) => {
        if (hijo.nodeType === Node.ELEMENT_NODE) return recorrer(hijo);
        if (hijo.nodeType !== Node.TEXT_NODE) return;
        const fragmento = document.createDocumentFragment();
        hijo.textContent.split(/(\s+)/).forEach((trozo) => {
          if (!trozo) return;
          if (/^\s+$/.test(trozo)) return fragmento.append(trozo);
          const palabra = document.createElement('span');
          palabra.className = 'palabra';
          palabra.textContent = trozo;
          fragmento.append(palabra);
        });
        hijo.replaceWith(fragmento);
      });
    };
    recorrer(el);
  }

  function aparecerPalabras(el, escalon) {
    const palabras = [...el.querySelectorAll('.palabra')];
    const retraso = escalon + (parseFloat(el.dataset.retraso) || 0);
    palabras.forEach((p) => { p.style.opacity = '0'; });
    el.dataset.visto = '';

    palabras.forEach((p, i) => {
      const fin = () => requestAnimationFrame(() => requestAnimationFrame(() => p.removeAttribute('style')));
      const control = animate(p,
        { opacity: [0, 1], translate: ['0px 22px', '0px 0px'], filter: ['blur(6px)', 'blur(0px)'] },
        { duration: 0.8, delay: retraso + i * 0.06, ease: SALIDA_SUAVE });
      (control.finished ?? Promise.resolve(control)).then(fin, fin);
    });
  }

  function prepararEntradas(zona) {
    // Sin la clase «anim» (el respaldo ya mostró todo) no hay entradas.
    if (!raiz.classList.contains('anim')) return;

    zona.querySelectorAll('[data-aparecer="palabras"]:not([data-visto])').forEach(partirEnPalabras);
    const objetivos = zona.querySelectorAll('[data-aparecer], [data-aparecer-grupo] > *');
    objetivos.forEach((el) => {
      if (observados.has(el) || el.hasAttribute('data-visto')) return;
      observados.add(el);
      // Sin devolver función, inView deja de observar tras la primera vez.
      inView(el, () => { encolar(el); }, { amount: 0.12, margin: '0px 0px -6% 0px' });
    });
  }

  /* ── 2. Ambiente: árbol, destellos, hojas y halos ───────────────────────── */
  function prepararAmbiente(zona) {
    zona.querySelectorAll('[data-flotar]').forEach((el) => {
      limpiezas.push(detener(animate(el,
        { transform: ['translateY(0px)', 'translateY(-7px)', 'translateY(0px)'] },
        { duration: 6, repeat: Infinity, ease: 'easeInOut' })));
    });

    zona.querySelectorAll('[data-destello]').forEach((el, i) => {
      limpiezas.push(detener(animate(el,
        { opacity: [1, 0.25, 1], transform: ['scale(1)', 'scale(0.72)', 'scale(1)'] },
        { duration: 2.6 + (i % 4) * 0.6, delay: i * 0.45, repeat: Infinity, ease: 'easeInOut' })));
    });

    zona.querySelectorAll('[data-mecer]').forEach((el, i) => {
      const base = el.style.transform || '';
      limpiezas.push(detener(animate(el,
        { transform: [`${base} translateY(0px) rotate(-5deg)`, `${base} translateY(4px) rotate(5deg)`, `${base} translateY(0px) rotate(-5deg)`] },
        { duration: 5 + i, repeat: Infinity, ease: 'easeInOut' })));
    });

    // Hojas que caen girando dentro de su contenedor; cada una a su ritmo.
    zona.querySelectorAll('[data-caer]').forEach((el, i) => {
      const alto = el.parentElement.clientHeight || 280;
      const paso = (y, x, giro) => `translate(${x}px, ${y}px) rotate(${giro}deg)`;
      limpiezas.push(detener(animate(el,
        {
          opacity: [0, 0.9, 0.9, 0],
          transform: [paso(0, 0, 0), paso(alto * 0.35, 14, 70), paso(alto * 0.7, -10, 140), paso(alto, 8, 210)],
        },
        { duration: 9 + i * 2.3, delay: i * 2.8, repeat: Infinity, ease: 'linear' })));
    });

    // Halos de las portadas: bajan y crecen un poco mientras la sección sale de
    // pantalla. «suave» sólo baja (para escenas y tarjetas con contenido).
    zona.querySelectorAll('[data-paralaje]').forEach((el) => {
      const base = el.style.transform || '';
      const suave = el.dataset.paralaje === 'suave';
      const seccion = el.closest('section') || el.parentElement;
      const cuadros = suave
        ? [`${base} translateY(0px)`, `${base} translateY(60px)`]
        : [`${base} translateY(0px) scale(1)`, `${base} translateY(90px) scale(1.18)`];
      limpiezas.push(scroll(
        animate(el, { transform: cuadros }, { ease: 'linear' }),
        { target: seccion, offset: ['start start', 'end start'] },
      ));
    });
  }

  /* ── Luz que sigue al cursor en las tarjetas con data-foco ──────────────── */
  document.addEventListener('pointermove', (e) => {
    const tarjeta = e.target.closest?.('[data-foco]');
    if (!tarjeta) return;
    const caja = tarjeta.getBoundingClientRect();
    tarjeta.style.setProperty('--foco-x', `${e.clientX - caja.left}px`);
    tarjeta.style.setProperty('--foco-y', `${e.clientY - caja.top}px`);
  }, { passive: true });

  const detener = (control) => () => control.stop?.();

  /* ── 3. Menú superior: más sólido al bajar ───────────────────────────────── */
  const menu = document.getElementById('siteNavbar');
  if (menu) {
    const marcar = () => menu.classList.toggle('nav-desplazado', window.scrollY > 12);
    addEventListener('scroll', marcar, { passive: true });
    marcar();
  }

  /* ── 4. Arranque y navegación sin recarga (main.js) ─────────────────────── */
  function iniciar() {
    limpiezas.forEach((fn) => fn());
    limpiezas = [];

    const zona = document.getElementById('spaContent') || document.body;
    prepararEntradas(zona);
    prepararAmbiente(zona);
  }

  iniciar();
  document.addEventListener('spa:navegado', iniciar);
})();
