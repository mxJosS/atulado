{{-- Ojito para mostrar u ocultar la contraseña. Va justo después del <input>, ambos dentro de un contenedor con position: relative. --}}
<button type="button"
  onclick="const i = this.previousElementSibling, ver = i.type === 'password'; i.type = ver ? 'text' : 'password'; this.setAttribute('aria-pressed', ver); this.firstElementChild.className = 'fa-solid ' + (ver ? 'fa-eye-slash' : 'fa-eye');"
  aria-label="Mostrar u ocultar contraseña" aria-pressed="false"
  style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #556860; padding: 6px; display: flex; align-items: center; justify-content: center;">
  <i class="fa-solid fa-eye" style="font-size: 0.95rem;"></i>
</button>
