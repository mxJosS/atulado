@props([
  'title' => 'Profesional Verificado',
  'message' => 'Las cuentas con esta insignia se han autenticado y pertenecen a profesionales de la salud mental con cédula y formación clínica validada en A tu lado.',
  'size' => 18
])
<span class="professional-badge-wrap" title="{{ $title }}" tabindex="0" role="button" aria-label="{{ $title }}">
  <svg class="meta-verified-badge" viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="metaBadgeGrad-{{ $size }}" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#0064E0"/>
        <stop offset="100%" stop-color="#0095F6"/>
      </linearGradient>
    </defs>
    <!-- Official Meta Verified Scalloped Rosette Contour -->
    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.7 4.3l-3.8-3.8 1.4-1.4 2.4 2.4 6.4-6.4 1.4 1.4-7.8 7.8z" fill="url(#metaBadgeGrad-{{ $size }})"/>
    <path d="M9.8 16.8l-3.8-3.8 1.4-1.4 2.4 2.4 6.4-6.4 1.4 1.4-7.8 7.8z" fill="#FFFFFF"/>
  </svg>

  <div class="verified-popover-card" role="tooltip">
    <div class="verified-popover-arrow"></div>
    <div class="verified-popover-header">
      <div class="verified-popover-title">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.7 4.3l-3.8-3.8 1.4-1.4 2.4 2.4 6.4-6.4 1.4 1.4-7.8 7.8z" fill="#0095F6"/>
          <path d="M9.8 16.8l-3.8-3.8 1.4-1.4 2.4 2.4 6.4-6.4 1.4 1.4-7.8 7.8z" fill="#FFFFFF"/>
        </svg>
        <span>{{ $title }}</span>
      </div>
      <button type="button" class="verified-popover-close" aria-label="Cerrar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="verified-popover-body">
      {{ $message }}
    </div>
    <div class="verified-popover-footer">
      <i class="fa-solid fa-certificate"></i>
      <span>Acreditación clínica validada en A tu lado</span>
    </div>
  </div>
</span>
