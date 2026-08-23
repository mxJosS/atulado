/* ==========================================================================
   A TU LADO — SINGLE PAGE APPLICATION (SPA) & FULL INTERACTION ENGINE
   Controlador unificado de navegación AJAX, minijuego de respiración y componentes
   ========================================================================== */

/* ════ 1. GLOBAL HELPER FUNCTIONS ════ */
window.fillDemoCredentials = function() {
  const emailInput = document.getElementById('email');
  const passInput = document.getElementById('password');
  if (emailInput) emailInput.value = 'demo@atulado.com.mx';
  if (passInput) passInput.value = 'password123';
};

window.togglePasswordVisibility = function() {
  const passInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');
  if (passInput && eyeIcon) {
    const isPass = passInput.type === 'password';
    passInput.type = isPass ? 'text' : 'password';
    eyeIcon.className = isPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
  }
};

window.nextGroundingStep = function(stepNum) {
  document.querySelectorAll('.grounding-step').forEach(el => el.style.display = 'none');
  const nextEl = document.getElementById(`step${stepNum}`);
  if (nextEl) nextEl.style.display = 'block';
  const bar = document.getElementById('groundingProgressBar');
  if (bar) bar.style.width = `${(stepNum / 5) * 100}%`;
};

window.finishGrounding = function() {
  document.querySelectorAll('.grounding-step').forEach(el => el.style.display = 'none');
  const completeEl = document.getElementById('stepComplete');
  if (completeEl) completeEl.style.display = 'block';
  const bar = document.getElementById('groundingProgressBar');
  if (bar) bar.style.width = '100%';
};

/* ════ GLOBAL FLOATING ZEN TOAST SYSTEM (5 SEGUNDOS CON BARRA DE LLENADO) ════ */
window.showZenToast = function(message, type = 'success', duration = 5000) {
  let container = document.getElementById('zenToastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'zenToastContainer';
    document.body.appendChild(container);
  }

  // Quitar cualquier toast previo para mantener la vista limpia
  container.querySelectorAll('.zen-toast-pill').forEach(t => t.remove());

  const toast = document.createElement('div');
  toast.className = `zen-toast-pill ${type}`;
  
  const icon = type === 'error' 
    ? 'fa-triangle-exclamation' 
    : (type === 'info' ? 'fa-circle-info' : 'fa-circle-check');

  const iconColor = type === 'error'
    ? '#922B21'
    : (type === 'info' ? '#4A3575' : '#1E4A25');

  toast.innerHTML = `
    <i class="fa-solid ${icon}" style="font-size: 1.15rem; color: ${iconColor};"></i>
    <span>${message}</span>
    <div class="toast-fill-bar" style="animation-duration: ${duration}ms;"></div>
  `;

  toast.onclick = () => {
    toast.classList.add('toast-leaving');
    setTimeout(() => toast.remove(), 450);
  };

  container.appendChild(toast);

  // Animación de salida a los (duration - 500ms)
  setTimeout(() => {
    if (toast.isConnected) {
      toast.classList.add('toast-leaving');
    }
  }, Math.max(0, duration - 500));

  // Remoción del DOM a los duration ms
  setTimeout(() => {
    if (toast.isConnected) {
      toast.remove();
    }
  }, duration);
};

/* ════ 2. BREATHING ENGINE (ROBUST & SYNCED) ════ */
class BreathingController {
  constructor(containerId) {
    this.container = document.getElementById(containerId) || document;
    this.mode = '478';
    this.isRunning = false;
    this.timerId = null;
    this.countInterval = null;
    this.completedCycles = 0;
    this.phaseIndex = 0;

    this.patterns = {
      '478': [
        { name: 'Inhala por la nariz...', duration: 4, action: 'inhale' },
        { name: 'Sostén con calma...', duration: 7, action: 'hold' },
        { name: 'Exhala por la boca...', duration: 8, action: 'exhale' }
      ],
      'box': [
        { name: 'Inhala...', duration: 4, action: 'inhale' },
        { name: 'Sostén...', duration: 4, action: 'hold' },
        { name: 'Exhala...', duration: 4, action: 'exhale' },
        { name: 'Pausa...', duration: 4, action: 'hold' }
      ],
      'calm': [
        { name: 'Inhala suavemente...', duration: 4, action: 'inhale' },
        { name: 'Exhala despacio...', duration: 4, action: 'exhale' }
      ]
    };

    this.bindElements();
    this.bindEvents();
  }

  bindElements() {
    this.circle = this.container.querySelector('.breathing-zen-circle') || document.querySelector('.breathing-zen-circle');
    this.actionText = this.container.querySelector('.circle-action-text') || document.querySelector('.circle-action-text');
    this.counterText = this.container.querySelector('.circle-counter-text') || document.querySelector('.circle-counter-text');
    this.toggleBtn = this.container.querySelector('#toggleZenBreathBtn, #mainToggleBreathBtn') || document.querySelector('#toggleZenBreathBtn, #mainToggleBreathBtn');
    this.playIcon = this.container.querySelector('#zenPlayIcon, #mainPlayIcon') || document.querySelector('#zenPlayIcon, #mainPlayIcon');
    this.playText = this.container.querySelector('#zenPlayText, #mainPlayText') || document.querySelector('#zenPlayText, #mainPlayText');
    this.cycleDisplay = this.container.querySelector('#zenCycleCount, #mainCycleCount') || document.querySelector('#zenCycleCount, #mainCycleCount');
    this.modeButtons = this.container.querySelectorAll('.breath-mode-btn, .page-breath-mode');
    if (!this.modeButtons || this.modeButtons.length === 0) {
      this.modeButtons = document.querySelectorAll('.breath-mode-btn, .page-breath-mode');
    }
  }

  bindEvents() {
    if (this.modeButtons && this.modeButtons.length > 0) {
      this.modeButtons.forEach(btn => {
        btn.onclick = (e) => {
          e.preventDefault();
          this.modeButtons.forEach(b => {
            b.style.background = 'rgba(255,255,255,0.08)';
            b.classList.remove('active');
          });
          btn.style.background = '#2E5D4B';
          btn.classList.add('active');
          this.mode = btn.getAttribute('data-mode') || '478';
          this.reset();
        };
      });
    }

    if (this.toggleBtn) {
      this.toggleBtn.onclick = (e) => {
        e.preventDefault();
        if (this.isRunning) {
          this.pause();
        } else if (this.isPaused) {
          this.resume();
        } else {
          this.start();
        }
      };
    }

    if (this.circle) {
      this.circle.onclick = (e) => {
        e.preventDefault();
        if (this.isRunning || this.isPaused) {
          // Clic en el círculo mientras corre o está en pausa -> Detención total y vuelta a "Toca para comenzar"
          this.reset();
        } else {
          // Clic en el círculo inactivo -> Iniciar ejercicio
          this.start();
        }
      };
    }
  }

  start() {
    this.isRunning = true;
    this.isPaused = false;
    this.phaseRemaining = 0;
    if (this.circle) {
      this.circle.className = 'breathing-zen-circle running';
    }
    if (this.playIcon) this.playIcon.className = 'fa-solid fa-pause';
    if (this.playText) this.playText.textContent = 'PAUSAR EJERCICIO';
    if (this.counterText) this.counterText.style.display = 'block';
    this.runPhase(this.phaseIndex);
  }

  pause() {
    this.isRunning = false;
    this.isPaused = true;
    clearTimeout(this.timerId);
    clearInterval(this.countInterval);
    if (this.playIcon) this.playIcon.className = 'fa-solid fa-play';
    if (this.playText) this.playText.textContent = 'REANUDAR EJERCICIO';
    if (this.circle) {
      this.circle.className = 'breathing-zen-circle stopped';
      this.circle.style.transition = 'transform 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease';
    }
    if (this.actionText) {
      this.actionText.innerHTML = '<span style="color: #FFA59C; font-family: var(--font-display); font-size: 1.55rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Detener</span><span style="display:block; font-size: 0.76rem; font-family: var(--font-sans); color: #C8DDD1; margin-top: 4px; font-weight: 400; opacity: 0.85;">(Toca el círculo para reiniciar)</span>';
    }
  }

  resume() {
    this.isRunning = true;
    this.isPaused = false;
    if (this.circle) {
      this.circle.classList.remove('stopped', 'paused');
    }
    if (this.playIcon) this.playIcon.className = 'fa-solid fa-pause';
    if (this.playText) this.playText.textContent = 'PAUSAR EJERCICIO';
    if (this.counterText) this.counterText.style.display = 'block';
    this.runPhase(this.phaseIndex, this.phaseRemaining > 0 ? this.phaseRemaining : null);
  }

  reset() {
    this.isRunning = false;
    this.isPaused = false;
    clearTimeout(this.timerId);
    clearInterval(this.countInterval);
    this.phaseIndex = 0;
    this.phaseRemaining = 0;
    if (this.playIcon) this.playIcon.className = 'fa-solid fa-play';
    if (this.playText) this.playText.textContent = 'INICIAR EJERCICIO';
    if (this.actionText) this.actionText.innerHTML = 'Toca para comenzar';
    if (this.circle) {
      this.circle.className = 'breathing-zen-circle';
      this.circle.style.transition = 'transform 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease';
    }
    if (this.counterText) this.counterText.style.display = 'none';
  }

  runPhase(idx, customDuration = null) {
    if (!this.isRunning) return;

    const pattern = this.patterns[this.mode];
    if (idx >= pattern.length) {
      this.completedCycles++;
      if (this.cycleDisplay) this.cycleDisplay.textContent = this.completedCycles;
      idx = 0;
    }
    this.phaseIndex = idx;
    const phase = pattern[idx];
    const duration = customDuration !== null ? customDuration : phase.duration;

    if (this.actionText) this.actionText.textContent = phase.name;
    if (this.circle) {
      this.circle.style.transition = `transform ${duration}s cubic-bezier(0.4, 0, 0.2, 1), box-shadow ${duration}s ease, border-color 0.5s ease`;
      this.circle.className = `breathing-zen-circle ${phase.action} running`;
    }

    let remaining = duration;
    this.phaseRemaining = remaining;
    if (this.counterText) this.counterText.textContent = `${remaining}s`;

    clearInterval(this.countInterval);
    this.countInterval = setInterval(() => {
      if (!this.isRunning) {
        clearInterval(this.countInterval);
        return;
      }
      remaining--;
      this.phaseRemaining = remaining;
      if (remaining > 0) {
        if (this.counterText) this.counterText.textContent = `${remaining}s`;
      } else {
        clearInterval(this.countInterval);
      }
    }, 1000);

    clearTimeout(this.timerId);
    this.timerId = setTimeout(() => {
      if (this.isRunning) {
        this.runPhase(idx + 1);
      }
    }, duration * 1000);
  }
}

/* ════ 3. SPA AJAX ROUTER (ZERO-RELOAD ENGINE) ════ */
class SpaRouter {
  constructor() {
    this.isNavigating = false;
    this.progressBar = document.getElementById('spaProgressBar');
    this.init();
  }

  init() {
    // Intercept clicks on links
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (!link) return;

      // Ignore external, download, tel, mailto, hash-only, logout forms, or explicit no-spa links
      if (
        link.target === '_blank' ||
        link.hasAttribute('download') ||
        link.hasAttribute('data-no-spa') ||
        link.getAttribute('href')?.startsWith('tel:') ||
        link.getAttribute('href')?.startsWith('mailto:') ||
        link.getAttribute('href')?.startsWith('#') ||
        link.closest('form') ||
        e.metaKey || e.ctrlKey || e.shiftKey || e.altKey
      ) {
        return;
      }

      const href = link.getAttribute('href');
      if (!href) return;

      try {
        const url = new URL(link.href, window.location.origin);

        // Must be same origin
        if (url.origin !== window.location.origin) return;

        // Ignore same exact URL
        if (url.href === window.location.href) {
          e.preventDefault();
          return;
        }

        // Check if layout switch is required (Dashboard vs Public)
        const isDashboardLink = url.pathname.startsWith('/dashboard') || 
                                url.pathname.startsWith('/historial') || 
                                url.pathname.startsWith('/plan-de-seguridad') || 
                                url.pathname.startsWith('/mis-favoritos') || 
                                url.pathname.startsWith('/perfil');
        const currentIsDashboard = !!document.querySelector('.dashboard-layout');

        // If switching between layouts, let standard browser navigation execute directly
        if (currentIsDashboard !== isDashboardLink) {
          return;
        }

        e.preventDefault();
        this.navigate(url.href, true);
      } catch (err) {
        // Fallback to default browser navigation
      }
    });

    // Browser Back / Forward buttons
    window.addEventListener('popstate', () => {
      this.navigate(window.location.href, false);
    });
  }

  setProgressBar(percent) {
    if (!this.progressBar) this.progressBar = document.getElementById('spaProgressBar');
    if (!this.progressBar) return;

    if (percent === 0) {
      this.progressBar.classList.remove('loading');
      this.progressBar.style.width = '0%';
      this.progressBar.style.opacity = '0';
    } else {
      this.progressBar.classList.add('loading');
      this.progressBar.style.width = `${percent}%`;
      this.progressBar.style.opacity = '1';
    }
  }

  async navigate(url, pushState = true) {
    if (this.isNavigating) {
      return;
    }
    this.isNavigating = true;
    this.setProgressBar(40);

    try {
      const res = await fetch(url, { 
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) {
        window.location.href = url;
        return;
      }

      this.setProgressBar(75);
      const html = await res.text();

      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const currentSpaContent = document.getElementById('spaContent');
      const newSpaContent = doc.getElementById('spaContent');

      const currentIsDashboard = !!document.querySelector('.dashboard-layout');
      const newIsDashboard = !!doc.querySelector('.dashboard-layout');

      // If layout mismatch or missing container, fallback to standard redirect
      if (!newSpaContent || !currentSpaContent || (currentIsDashboard !== newIsDashboard)) {
        this.setProgressBar(100);
        window.location.href = url;
        return;
      }

      // Smooth swap animation
      currentSpaContent.classList.add('spa-transitioning');
      await new Promise(r => setTimeout(r, 60));

      currentSpaContent.innerHTML = newSpaContent.innerHTML;

      if (doc.title) {
        document.title = doc.title;
      }

      // Update active nav & sidebar links
      const currentPath = new URL(url).pathname;
      document.querySelectorAll('.nav-link, .sidebar-item, .user-profile-badge').forEach(el => {
        const href = el.getAttribute('href');
        if (href) {
          try {
            const elUrl = new URL(href, window.location.origin);
            if (elUrl.pathname === currentPath) {
              el.classList.add('active');
            } else {
              el.classList.remove('active');
            }
          } catch (e) {}
        }
      });

      // Update topbar header title if present
      const newTopbarTitle = doc.querySelector('.topbar-page-title');
      const currentTopbarTitle = document.querySelector('.topbar-page-title');
      if (newTopbarTitle && currentTopbarTitle) {
        currentTopbarTitle.innerHTML = newTopbarTitle.innerHTML;
      }

      // Close mobile sidebar and drawers if open
      const sideBar = document.getElementById('dashboardSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      if (sideBar) sideBar.classList.remove('mobile-open');
      if (overlay) overlay.style.display = 'none';

      if (pushState) {
        history.pushState({ spa: true, url }, '', url);
      }

      window.scrollTo({ top: 0, behavior: 'instant' });

      // Safely initialize components
      try {
        initAllComponents();
      } catch (compErr) {
        console.warn('Component init:', compErr);
      }

      currentSpaContent.classList.remove('spa-transitioning');
      this.setProgressBar(100);

    } catch (err) {
      console.error('SPA fallback:', err);
      window.location.href = url;
    } finally {
      setTimeout(() => {
        this.setProgressBar(0);
        this.isNavigating = false;
      }, 150);
    }
  }
}

/* ════ 4. UNIFIED COMPONENT INITIALIZERS ════ */
function initAllComponents() {
  // A. Mobile Drawer Toggle
  const hamburgerBtn = document.getElementById('navHamburgerBtn');
  const mobileDrawer = document.getElementById('navMobileDrawer');
  if (hamburgerBtn && mobileDrawer) {
    hamburgerBtn.onclick = (e) => {
      e.preventDefault();
      const isOpen = mobileDrawer.classList.toggle('open');
      hamburgerBtn.setAttribute('aria-expanded', isOpen);
    };
  }

  document.querySelectorAll('.nav-mobile-link').forEach(link => {
    link.onclick = () => {
      if (mobileDrawer) mobileDrawer.classList.remove('open');
    };
  });

  // B. Dashboard Sidebar Mobile Toggle
  const sideToggle = document.getElementById('mobileSidebarToggle');
  const sideBar = document.getElementById('dashboardSidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (sideToggle && sideBar && overlay) {
    if (window.innerWidth <= 960) {
      sideToggle.style.display = 'inline-flex';
    }
    sideToggle.onclick = (e) => {
      e.preventDefault();
      sideBar.classList.toggle('mobile-open');
      overlay.style.display = sideBar.classList.contains('mobile-open') ? 'block' : 'none';
    };
    overlay.onclick = () => {
      sideBar.classList.remove('mobile-open');
      overlay.style.display = 'none';
    };
  }

  // C. Quick Emotional Check on Home Page
  document.querySelectorAll('.home-emo-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      document.querySelectorAll('.home-emo-btn').forEach(b => {
        b.style.background = '#FFFFFF';
        b.style.color = '#1A2620';
        b.style.borderColor = '#DCE8E0';
      });
      btn.style.background = '#2E5D4B';
      btn.style.color = '#FFFFFF';
      btn.style.borderColor = '#2E5D4B';

      const tool = btn.getAttribute('data-tool');
      const tip = btn.getAttribute('data-tip');
      const url = btn.getAttribute('data-url');

      const box = document.getElementById('homeRecBox');
      if (box) {
        document.getElementById('homeRecTitle').textContent = `Recomendación: ${tool}`;
        document.getElementById('homeRecTip').textContent = tip;
        document.getElementById('homeRecUrl').href = url;
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    };
  });

  // D. 8 Feelings Cards on /sientes Page
  document.querySelectorAll('.emotion-card-8').forEach(card => {
    card.onclick = (e) => {
      e.preventDefault();
      document.querySelectorAll('.emotion-card-8').forEach(c => c.classList.remove('active-feeling'));
      card.classList.add('active-feeling');

      const name = card.getAttribute('data-name');
      const tool = card.getAttribute('data-tool');
      const tip = card.getAttribute('data-tip');
      const url = card.getAttribute('data-url');

      const box = document.getElementById('feelingRecommendationBox');
      if (box) {
        const emoTarget = document.getElementById('recEmotionName');
        if (emoTarget) emoTarget.textContent = name;
        document.getElementById('recTitle').textContent = `Recomendación: ${tool}`;
        document.getElementById('recTip').textContent = tip;
        const btn = document.getElementById('recActionBtn');
        btn.href = url;
        btn.innerHTML = `<span>Ir a ${tool}</span> <i class="fa-solid fa-arrow-right"></i>`;
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    };
  });

  // E. Minijuegos de Respiración (/sientes y /herramientas/respiracion)
  if (document.querySelector('.breathing-zen-circle')) {
    window.activeBreather = new BreathingController(document.querySelector('.breathing-module-container, #mainRespiracionContainer')?.id || document.body);
  }

  // F. 5 Smilies Check-in on /dashboard
  document.querySelectorAll('.smily-card-option').forEach(card => {
    card.onclick = (e) => {
      document.querySelectorAll('.smily-card-option').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      const radio = card.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;

      const emo = card.getAttribute('data-emo');
      if (emo && document.getElementById('primaryEmotionInput')) {
        document.getElementById('primaryEmotionInput').value = emo;
      }
    };
  });

  // Emotion tag pills on /dashboard
  document.querySelectorAll('.emotion-tag-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      document.querySelectorAll('.emotion-tag-btn').forEach(b => b.classList.remove('active-tag'));
      btn.classList.add('active-tag');
      const input = document.getElementById('primaryEmotionInput');
      if (input) input.value = btn.getAttribute('data-val') || btn.textContent.trim();
    };
  });

  // G. AJAX Dashboard Check-in Submission
  const checkinForm = document.getElementById('moodCheckinForm');
  if (checkinForm) {
    checkinForm.onsubmit = async (e) => {
      e.preventDefault();
      const saveBtn = document.getElementById('saveCheckinBtn');
      const saveText = document.getElementById('saveCheckinBtnText');

      saveBtn.disabled = true;
      if (saveText) saveText.textContent = 'Guardando registro...';

      try {
        const formData = new FormData(checkinForm);
        const res = await fetch(checkinForm.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.success) {
          // Mostrar Toast Circular FLOTANTE afuera de la tarjeta durante 5 segundos
          window.showZenToast(data.message, 'success', 5000);

          if (saveText) saveText.textContent = '¡Guardado con éxito!';

          // Update Streak Bubble for today
          const todayBubble = document.querySelector('.day-circle-bubble.today-active');
          if (todayBubble) {
            todayBubble.classList.add('completed');
          }

          setTimeout(() => {
            saveBtn.disabled = false;
            if (saveText) saveText.textContent = 'Actualizar registro de hoy';
          }, 2500);
        } else {
          throw new Error('Error al guardar');
        }
      } catch (err) {
        window.showZenToast('Ocurrió un error al guardar. Revisa los campos e intenta de nuevo.', 'error', 5000);
        saveBtn.disabled = false;
        if (saveText) saveText.textContent = 'Guardar registro emocional';
      }
    };
  }

  // H. Password Toggle on Login / Register
  const togglePassBtn = document.getElementById('togglePasswordBtn');
  if (togglePassBtn) {
    togglePassBtn.onclick = (e) => {
      e.preventDefault();
      window.togglePasswordVisibility();
    };
  }

  // I. Profile Avatar Color Selector
  document.querySelectorAll('.avatar-radio').forEach(radio => {
    radio.onchange = () => {
      document.querySelectorAll('.avatar-swatch').forEach(sw => {
        sw.style.borderColor = 'transparent';
        const check = sw.querySelector('.check-mark');
        if (check) check.style.display = 'none';
      });
      const selectedSwatch = radio.closest('label').querySelector('.avatar-swatch');
      if (selectedSwatch) {
        selectedSwatch.style.borderColor = '#2E5D4B';
        const check = selectedSwatch.querySelector('.check-mark');
        if (check) check.style.display = 'block';
      }
    };
  });

  // J. Favorite Toggle Ajax Buttons
  document.querySelectorAll('.favorite-toggle-btn').forEach(btn => {
    btn.onclick = async (e) => {
      e.preventDefault();
      const id = btn.getAttribute('data-id');
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!id || !token) return;

      try {
        const res = await fetch(`/recursos/${id}/favorito`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        if (data.success) {
          const icon = btn.querySelector('i');
          if (data.status === 'added') {
            btn.classList.add('is-fav');
            if (icon) icon.style.color = '#C8B87A';
          } else {
            btn.classList.remove('is-fav');
            if (icon) icon.style.color = '#8EADA4';
          }
        }
      } catch (e) {}
    };
  });

  // K. Auto-dismiss Blade flash toast pills tras 5 segundos
  document.querySelectorAll('.zen-toast-pill').forEach(toast => {
    setTimeout(() => {
      if (toast.isConnected) {
        toast.classList.add('toast-leaving');
      }
    }, 4500);

    setTimeout(() => {
      if (toast.isConnected) {
        toast.remove();
      }
    }, 5000);
  });
}

// Global Document Listener for Dynamic Click Fallbacks (Píldoras, Caritas, Auto-llenar, etc.)
document.addEventListener('click', (e) => {
  // 1. Emotion Tag Pill Delegated Click
  const emoBtn = e.target.closest('.emotion-tag-btn');
  if (emoBtn) {
    e.preventDefault();
    document.querySelectorAll('.emotion-tag-btn').forEach(b => b.classList.remove('active-tag'));
    emoBtn.classList.add('active-tag');
    const input = document.getElementById('primaryEmotionInput');
    if (input) input.value = emoBtn.getAttribute('data-val') || emoBtn.textContent.trim();
    return;
  }

  // 2. Smily Card Delegated Click
  const smilyCard = e.target.closest('.smily-card-option');
  if (smilyCard && !e.target.matches('input[type="radio"]')) {
    document.querySelectorAll('.smily-card-option').forEach(c => c.classList.remove('selected'));
    smilyCard.classList.add('selected');
    const radio = smilyCard.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
    const emo = smilyCard.getAttribute('data-emo');
    if (emo && document.getElementById('primaryEmotionInput')) {
      document.getElementById('primaryEmotionInput').value = emo;
    }
    return;
  }

  // 3. Auto-llenar Delegated Click
  const autofillBtn = e.target.closest('[onclick*="fillDemoCredentials"], .demo-autofill-btn');
  if (autofillBtn) {
    e.preventDefault();
    window.fillDemoCredentials();
    return;
  }
});

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  window.spaRouter = new SpaRouter();
  initAllComponents();
});
