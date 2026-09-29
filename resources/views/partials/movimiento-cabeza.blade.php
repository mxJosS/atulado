{{-- Movimiento (js/animaciones.js): lo que va a aparecer espera oculto a que
     Motion lo anime. Si Motion no llega en 1 s, se muestra todo sin animar.
     Sólo en la carga inicial: la navegación sin recarga de main.js vuelve a
     ejecutar este script y ahí no debe ocultar nada. --}}
<script>
  if (document.readyState === 'loading' && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.classList.add('anim');
    setTimeout(function () {
      if (!window.__animListo) document.documentElement.classList.remove('anim');
    }, 1000);
  }
</script>
