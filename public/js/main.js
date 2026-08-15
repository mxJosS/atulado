/* ==========================================================================
   A TU LADO — CLIENT INTERACTIONS & SOUND SYNTHESIS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Menu Drawer Toggle
  const hamburgerBtn = document.getElementById('navHamburgerBtn');
  const mobileDrawer = document.getElementById('navMobileDrawer');
  
  if (hamburgerBtn && mobileDrawer) {
    hamburgerBtn.addEventListener('click', () => {
      const isOpen = mobileDrawer.classList.toggle('open');
      hamburgerBtn.setAttribute('aria-expanded', isOpen);
    });
  }

  // 2. Dashboard Sidebar Toggle (Mobile)
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('dashboardSidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (sidebarToggleBtn && sidebar) {
    sidebarToggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('mobile-open');
      if (sidebarOverlay) sidebarOverlay.classList.toggle('open');
    });

    if (sidebarOverlay) {
      sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('open');
      });
    }
  }

  // 3. Auto-dismiss alerts after 5 seconds
  document.querySelectorAll('.alert-banner').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });
});

/* ════ WEB AUDIO API SOUND GENERATOR (CHIMES & BELLS) ════ */
class ZenAudio {
  constructor() {
    this.ctx = null;
    this.enabled = true;
  }

  init() {
    if (!this.ctx) {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (AudioContext) {
        this.ctx = new AudioContext();
      }
    }
  }

  playChime(freq = 440, type = 'sine', duration = 1.8) {
    if (!this.enabled) return;
    try {
      this.init();
      if (!this.ctx) return;
      if (this.ctx.state === 'suspended') {
        this.ctx.resume();
      }

      const osc = this.ctx.createOscillator();
      const gain = this.ctx.createGain();

      osc.type = type;
      osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
      
      // Soft envelope attack & gentle decay
      gain.gain.setValueAtTime(0.001, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.2, this.ctx.currentTime + 0.08);
      gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);

      osc.connect(gain);
      gain.connect(this.ctx.destination);

      osc.start();
      osc.stop(this.ctx.currentTime + duration);
    } catch (e) {
      console.log('Audio feedback not available', e);
    }
  }
}

window.zenAudio = new ZenAudio();

/* ════ FAVORITE RESOURCE TOGGLER (AJAX) ════ */
async function toggleResourceFavorite(resourceId, btnElement) {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) return;

  try {
    const response = await fetch(`/recursos/${resourceId}/favorito`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      }
    });

    const data = await response.json();
    if (data.success) {
      if (data.status === 'added') {
        btnElement.classList.add('is-favorited');
        btnElement.innerHTML = '⭐ Guardado';
      } else {
        btnElement.classList.remove('is-favorited');
        btnElement.innerHTML = '☆ Guardar';
      }
      window.zenAudio.playChime(587.33); // D5 note
    }
  } catch (err) {
    console.error('Error toggling favorite:', err);
  }
}
