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

/* ════ MOTOR CLÍNICO V1.0: FUNCIONES GLOBALES DE MODALES Y TRIAJE ════ */
window.closeClinicalModals = function() {
  const modals = ['who5ModalOverlay', 'mdiModalOverlay', 'asqModalOverlay', 'containmentModalOverlay'];
  modals.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
};

window.openWho5Modal = function(origen = 'programada') {
  window.closeClinicalModals();
  const modal = document.getElementById('who5ModalOverlay');
  const inputOrigen = document.getElementById('who5OrigenInput');
  if (inputOrigen) inputOrigen.value = origen;
  if (modal) modal.style.display = 'flex';
};

window.openMdiModal = function() {
  window.closeClinicalModals();
  const modal = document.getElementById('mdiModalOverlay');
  if (modal) modal.style.display = 'flex';
};

window.openAsqModal = function() {
  window.closeClinicalModals();
  const modal = document.getElementById('asqModalOverlay');
  if (modal) modal.style.display = 'flex';
};

window.openContainmentModal = function() {
  window.closeClinicalModals();
  const modal = document.getElementById('containmentModalOverlay');
  if (modal) modal.style.display = 'flex';
};

window.selectClinicalRadio = function(labelEl) {
  const container = labelEl.closest('.clinical-options-grid');
  if (container) {
    container.querySelectorAll('.clinical-radio-btn').forEach(btn => btn.classList.remove('selected'));
  }
  labelEl.classList.add('selected');
  const radio = labelEl.querySelector('input[type="radio"]');
  if (radio) radio.checked = true;
};

window.checkAsqP5Visibility = function() {
  const p1 = document.querySelector('input[name="p1"]:checked')?.value;
  const p2 = document.querySelector('input[name="p2"]:checked')?.value;
  const p3 = document.querySelector('input[name="p3"]:checked')?.value;
  const p4 = document.querySelector('input[name="p4"]:checked')?.value;

  const esPositiva = (v) => v === 'si' || v === 'prefiero_no_contestar';
  const container5 = document.getElementById('asqP5Container');

  if (container5) {
    if (esPositiva(p1) || esPositiva(p2) || esPositiva(p3) || esPositiva(p4)) {
      container5.style.display = 'block';
    } else {
      container5.style.display = 'none';
    }
  }
};

window.registrarCrisisAccion = async function(tipoAccion) {
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    await fetch('/assessment/crisis/accion', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ tipo_accion: tipoAccion })
    });
  } catch (err) {}
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

/* ════ GLOBAL ZEN CONFIRMATION & ALERT MODALS (REEMPLAZO TOTAL DE DIÁLOGOS NATIVOS) ════ */
window.showZenConfirm = function(options = {}) {
  const {
    title = '¿Confirmar acción?',
    message = '¿Deseas continuar con esta acción?',
    confirmText = 'Sí, continuar',
    cancelText = 'Cancelar',
    type = 'danger',
    icon = null
  } = (typeof options === 'string' ? { message: options } : options);

  return new Promise((resolve) => {
    let overlay = document.getElementById('zenGlobalModalOverlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'zenGlobalModalOverlay';
      overlay.className = 'zen-global-modal-overlay';
      document.body.appendChild(overlay);
    }

    const defaultIcon = type === 'danger' 
      ? 'fa-triangle-exclamation' 
      : (type === 'warning' ? 'fa-circle-exclamation' : (type === 'success' ? 'fa-circle-check' : 'fa-circle-question'));

    const iconHtml = icon 
      ? (icon.startsWith('game-icons:') || icon.startsWith('ra-') 
          ? `<iconify-icon icon="${icon}" style="font-size: 2rem;"></iconify-icon>` 
          : `<i class="fa-solid ${icon}"></i>`)
      : `<i class="fa-solid ${defaultIcon}"></i>`;

    const confirmBtnClass = type === 'danger' ? 'btn-danger-zen' : 'btn-primary-zen';

    overlay.innerHTML = `
      <div class="zen-modal-card" role="dialog" aria-modal="true">
        <div class="zen-modal-icon-circle ${type}">
          ${iconHtml}
        </div>
        <h3 class="zen-modal-title">${title}</h3>
        <p class="zen-modal-message">${message}</p>
        <div class="zen-modal-actions">
          <button type="button" class="btn btn-secondary zen-modal-btn-cancel">${cancelText}</button>
          <button type="button" class="btn ${confirmBtnClass} zen-modal-btn-confirm">${confirmText}</button>
        </div>
      </div>
    `;

    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
      overlay.classList.add('active');
    });

    const closeDialog = (result) => {
      overlay.classList.remove('active');
      setTimeout(() => {
        overlay.style.display = 'none';
        resolve(result);
      }, 200);
    };

    const cancelBtn = overlay.querySelector('.zen-modal-btn-cancel');
    const confirmBtn = overlay.querySelector('.zen-modal-btn-confirm');

    if (cancelBtn) cancelBtn.onclick = (e) => { e.preventDefault(); closeDialog(false); };
    if (confirmBtn) confirmBtn.onclick = (e) => { e.preventDefault(); closeDialog(true); };

    overlay.onclick = (e) => {
      if (e.target === overlay) closeDialog(false);
    };
  });
};

window.showZenAlert = function(options = {}) {
  const {
    title = 'Notificación',
    message = '',
    buttonText = 'Entendido',
    type = 'info',
    icon = null
  } = (typeof options === 'string' ? { message: options } : options);

  return new Promise((resolve) => {
    let overlay = document.getElementById('zenGlobalModalOverlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'zenGlobalModalOverlay';
      overlay.className = 'zen-global-modal-overlay';
      document.body.appendChild(overlay);
    }

    const defaultIcon = type === 'danger' 
      ? 'fa-triangle-exclamation' 
      : (type === 'warning' ? 'fa-circle-exclamation' : (type === 'success' ? 'fa-circle-check' : 'fa-circle-info'));

    const iconHtml = icon 
      ? (icon.startsWith('game-icons:') || icon.startsWith('ra-') 
          ? `<iconify-icon icon="${icon}" style="font-size: 2rem;"></iconify-icon>` 
          : `<i class="fa-solid ${icon}"></i>`)
      : `<i class="fa-solid ${defaultIcon}"></i>`;

    overlay.innerHTML = `
      <div class="zen-modal-card" role="dialog" aria-modal="true">
        <div class="zen-modal-icon-circle ${type}">
          ${iconHtml}
        </div>
        <h3 class="zen-modal-title">${title}</h3>
        <p class="zen-modal-message">${message}</p>
        <div class="zen-modal-actions">
          <button type="button" class="btn btn-primary-zen zen-modal-btn-confirm" style="width: 100%;">${buttonText}</button>
        </div>
      </div>
    `;

    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
      overlay.classList.add('active');
    });

    const closeDialog = () => {
      overlay.classList.remove('active');
      setTimeout(() => {
        overlay.style.display = 'none';
        resolve(true);
      }, 200);
    };

    const confirmBtn = overlay.querySelector('.zen-modal-btn-confirm');
    if (confirmBtn) confirmBtn.onclick = (e) => { e.preventDefault(); closeDialog(); };

    overlay.onclick = (e) => {
      if (e.target === overlay) closeDialog();
    };
  });
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

        e.preventDefault();
        this.navigate(url.href, true);
      } catch (err) {
        // Fallback to default browser navigation
      }
    });

    // Intercept form submissions across dashboard and SPA views
    document.addEventListener('submit', (e) => {
      const form = e.target.closest('form');
      if (!form) return;

      // Allow default submission if marked data-no-spa, target=_blank, or logout
      const action = form.getAttribute('action') || window.location.href;
      if (
        form.hasAttribute('data-no-spa') ||
        form.target === '_blank' ||
        action.includes('/logout') ||
        action.includes('/login') ||
        action.includes('/registro')
      ) {
        return;
      }

      // If form has custom AJAX handler with stopImmediatePropagation or explicit ID, let it handle
      if (form.id === 'moodCheckinForm' || form.id === 'who5Form' || form.id === 'mdiForm' || form.id === 'asqForm') {
        return;
      }

      try {
        const url = new URL(action, window.location.origin);
        if (url.origin !== window.location.origin) return;

        e.preventDefault();
        this.submitForm(form);
      } catch (err) {
        // Fallback
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
        headers: { 
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html, application/xhtml+xml, */*'
        }
      });

      if (!res.ok) {
        window.location.href = url;
        return;
      }

      this.setProgressBar(75);
      const html = await res.text();
      const targetUrl = res.url || url;

      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const currentSpaContent = document.getElementById('spaContent');
      const newSpaContent = doc.getElementById('spaContent');

      const currentIsDashboard = !!document.querySelector('.dashboard-layout');
      const newIsDashboard = !!doc.querySelector('.dashboard-layout');

      // If layout mismatch or missing container, fallback to standard redirect
      if (!newSpaContent || !currentSpaContent || (currentIsDashboard !== newIsDashboard)) {
        this.setProgressBar(100);
        window.location.href = targetUrl;
        return;
      }

      // Smooth swap animation
      currentSpaContent.classList.add('spa-transitioning');
      await new Promise(r => setTimeout(r, 60));

      currentSpaContent.innerHTML = newSpaContent.innerHTML;

      if (doc.title) {
        document.title = doc.title;
      }

      // Transfer any flash toast messages from response
      this.syncToasts(doc);

      // Execute any inline or page-specific scripts from the incoming document
      this.executePageScripts(doc);

      // Update active nav & sidebar links
      this.updateActiveLinks(targetUrl);

      // Update topbar header title if present
      const newTopbarTitle = doc.querySelector('.topbar-page-title');
      const currentTopbarTitle = document.querySelector('.topbar-page-title');
      if (newTopbarTitle && currentTopbarTitle) {
        currentTopbarTitle.innerHTML = newTopbarTitle.innerHTML;
      }

      // Close mobile sidebar and drawers if open
      const sideBar = document.getElementById('dashboardSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      const mobileDrawer = document.getElementById('navMobileDrawer');
      const hamburgerBtn = document.getElementById('navHamburgerBtn');

      if (sideBar) sideBar.classList.remove('mobile-open');
      if (overlay) overlay.style.display = 'none';
      if (mobileDrawer) mobileDrawer.classList.remove('open');
      if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');

      if (pushState) {
        history.pushState({ spa: true, url: targetUrl }, '', targetUrl);
      }

      window.scrollTo({ top: 0, behavior: 'instant' });

      // Safely initialize components
      try {
        initAllComponents();
      } catch (compErr) {}

      currentSpaContent.classList.remove('spa-transitioning');
      this.setProgressBar(100);

    } catch (err) {
      window.location.href = url;
    } finally {
      setTimeout(() => {
        this.setProgressBar(0);
        this.isNavigating = false;
      }, 150);
    }
  }

  async submitForm(form) {
    if (this.isNavigating) return;
    this.isNavigating = true;
    this.setProgressBar(40);

    const action = form.getAttribute('action') || window.location.href;
    const method = (form.getAttribute('method') || 'POST').toUpperCase();
    const formData = new FormData(form);

    const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitBtns.forEach(btn => {
      btn.disabled = true;
      btn.style.opacity = '0.7';
    });

    try {
      const res = await fetch(action, {
        method: method,
        body: formData,
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html, application/xhtml+xml, */*'
        }
      });

      this.setProgressBar(75);
      const targetUrl = res.url || action;
      const html = await res.text();

      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const currentSpaContent = document.getElementById('spaContent');
      const newSpaContent = doc.getElementById('spaContent');

      if (!newSpaContent || !currentSpaContent) {
        window.location.href = targetUrl;
        return;
      }

      // Smooth swap
      currentSpaContent.classList.add('spa-transitioning');
      await new Promise(r => setTimeout(r, 60));

      currentSpaContent.innerHTML = newSpaContent.innerHTML;

      if (doc.title) {
        document.title = doc.title;
      }

      // Transfer flash toast notifications
      this.syncToasts(doc);

      // Execute scripts
      this.executePageScripts(doc);

      // Update active nav & sidebar links
      this.updateActiveLinks(targetUrl);

      // Update URL if changed
      if (targetUrl !== window.location.href) {
        history.pushState({ spa: true, url: targetUrl }, '', targetUrl);
      }

      // Re-init components
      try {
        initAllComponents();
      } catch (compErr) {}

      currentSpaContent.classList.remove('spa-transitioning');
      this.setProgressBar(100);

    } catch (err) {
      form.submit();
    } finally {
      submitBtns.forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '1';
      });
      setTimeout(() => {
        this.setProgressBar(0);
        this.isNavigating = false;
      }, 150);
    }
  }

  syncToasts(doc) {
    const incomingToasts = doc.querySelectorAll('#zenToastContainer .zen-toast-pill');
    if (incomingToasts.length > 0) {
      let activeContainer = document.getElementById('zenToastContainer');
      if (!activeContainer) {
        activeContainer = document.createElement('div');
        activeContainer.id = 'zenToastContainer';
        document.body.appendChild(activeContainer);
      }
      activeContainer.innerHTML = '';
      incomingToasts.forEach(t => {
        const cloned = t.cloneNode(true);
        cloned.onclick = () => {
          cloned.classList.add('toast-leaving');
          setTimeout(() => cloned.remove(), 450);
        };
        activeContainer.appendChild(cloned);
      });
      setTimeout(() => {
        activeContainer.querySelectorAll('.zen-toast-pill').forEach(tp => {
          tp.classList.add('toast-leaving');
          setTimeout(() => tp.remove(), 450);
        });
      }, 4500);
    }
  }

  executePageScripts(doc) {
    const pageScripts = doc.querySelectorAll('script:not([src])');
    pageScripts.forEach(oldScript => {
      const code = oldScript.textContent.trim();
      if (code) {
        try {
          const scriptElem = document.createElement('script');
          scriptElem.textContent = code;
          document.body.appendChild(scriptElem);
          setTimeout(() => scriptElem.remove(), 150);
        } catch (scriptErr) {}
      }
    });
  }

  updateActiveLinks(targetUrl) {
    try {
      const currentPath = new URL(targetUrl, window.location.origin).pathname;
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
    } catch (e) {}
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
      e.stopPropagation();
      const isOpen = mobileDrawer.classList.toggle('open');
      hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    // Close on any click inside drawer (links, buttons, login, register)
    mobileDrawer.querySelectorAll('a, button').forEach(el => {
      el.onclick = () => {
        mobileDrawer.classList.remove('open');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
      };
    });
  }

  // Close mobile drawer when clicking outside
  document.addEventListener('click', (e) => {
    const mobileDrawer = document.getElementById('navMobileDrawer');
    const hamburgerBtn = document.getElementById('navHamburgerBtn');
    if (mobileDrawer && mobileDrawer.classList.contains('open')) {
      if (!mobileDrawer.contains(e.target) && !hamburgerBtn?.contains(e.target)) {
        mobileDrawer.classList.remove('open');
        if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');
      }
    }
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

  // G. AJAX Dashboard Check-in Submission & Cascade Clinical Engine Flow
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
          // Mostrar Toast Circular FLOTANTE afuera de la tarjeta
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

          // ════ Evaluador del Motor Clínico de 4 Capas ════
          if (data.evaluacion) {
            const ev = data.evaluacion;
            if (ev.ruta === 'B' && ev.abrir_who5) {
              setTimeout(() => {
                window.openWho5Modal(ev.origen_who5 || 'programada');
              }, 600);
            } else if (ev.ruta === 'C' && ev.abrir_mdi) {
              setTimeout(() => {
                window.openMdiModal();
              }, 600);
            }
          }
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

  // G.1 WHO-5 Form Submission (Capa 1)
  const who5Form = document.getElementById('who5Form');
  if (who5Form) {
    who5Form.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById('who5SubmitBtn');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const formData = new FormData(who5Form);
        const res = await fetch('/assessment/who5', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.success) {
          const resWho5 = data.resultado;
          if (resWho5.abrir_mdi) {
            window.openMdiModal();
          } else {
            window.closeClinicalModals();
            window.showZenToast(resWho5.mensaje || 'Evaluación de bienestar completada.', 'success', 5000);
          }
        }
      } catch (err) {
        window.showZenToast('Por favor responde todas las preguntas del cuestionario.', 'error', 4000);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    };
  }

  // G.2 MDI Form Submission (Capa 2)
  const mdiForm = document.getElementById('mdiForm');
  if (mdiForm) {
    mdiForm.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById('mdiSubmitBtn');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const formData = new FormData(mdiForm);
        const res = await fetch('/assessment/mdi', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.success) {
          const resMdi = data.resultado;
          if (resMdi.abrir_asq) {
            window.openAsqModal();
          } else if (resMdi.nivel === 'ROJO') {
            window.openContainmentModal();
          } else {
            window.closeClinicalModals();
            window.showZenToast('Respuestas registradas. Hemos adaptado tus sugerencias de bienestar.', 'success', 5000);
          }
        }
      } catch (err) {
        window.showZenToast('Por favor completa todos los ítems para continuar.', 'error', 4000);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    };
  }

  // G.3 ASQ Form Submission (Capa 3)
  const asqForm = document.getElementById('asqForm');
  if (asqForm) {
    asqForm.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById('asqSubmitBtn');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const formData = new FormData(asqForm);
        const res = await fetch('/assessment/asq', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await res.json();
        if (data.success) {
          const resAsq = data.resultado;
          if (resAsq.nivel === 'ROJO' || resAsq.nivel === 'ROJO_AGUDO') {
            window.openContainmentModal();
          } else {
            window.closeClinicalModals();
            window.showZenToast('Gracias por tu honestidad. Cuentas con nosotros en todo momento.', 'success', 5000);
          }
        }
      } catch (err) {
        window.showZenToast('Por favor selecciona una opción en cada pregunta.', 'error', 4000);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
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

// Global Document Listener for Dynamic Click Fallbacks (Píldoras, Caritas, Auto-llenar, Popovers, etc.)
document.addEventListener('click', (e) => {
  // 0. Verified Badge Popover Close Button
  const closeBtn = e.target.closest('.verified-popover-close');
  if (closeBtn) {
    e.preventDefault();
    e.stopPropagation();
    const popover = closeBtn.closest('.verified-popover-card');
    if (popover) {
      popover.classList.remove('open');
    }
    return;
  }

  // 0b. Verified Badge Trigger Click
  const badgeWrap = e.target.closest('.professional-badge-wrap');
  if (badgeWrap) {
    const popover = badgeWrap.querySelector('.verified-popover-card');
    if (popover && !e.target.closest('.verified-popover-card')) {
      e.preventDefault();
      e.stopPropagation();
      const isOpen = popover.classList.contains('open');
      // Close others first
      document.querySelectorAll('.verified-popover-card.open').forEach(p => p.classList.remove('open'));
      if (!isOpen) {
        popover.classList.add('open');
      }
      return;
    }
  } else {
    // Click outside any badge wrap -> close all open popovers
    document.querySelectorAll('.verified-popover-card.open').forEach(p => p.classList.remove('open'));
  }

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

// Delegated Form Confirmation Handling
document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (form && form.matches && (form.matches('.delete-mood-form') || form.hasAttribute('data-confirm'))) {
    e.preventDefault();
    const customTitle = form.getAttribute('data-confirm-title') || '¿Eliminar este registro emocional?';
    const customMessage = form.getAttribute('data-confirm') || 'Esta entrada de tu diario y su estado emocional serán borrados de forma permanente.';
    const confirmBtnText = form.getAttribute('data-confirm-btn') || 'Sí, eliminar';
    
    const confirmed = await window.showZenConfirm({
      title: customTitle,
      message: customMessage,
      confirmText: confirmBtnText,
      cancelText: 'Conservar',
      type: 'danger',
      icon: 'fa-trash-can'
    });

    if (confirmed) {
      form.classList.remove('delete-mood-form');
      form.removeAttribute('data-confirm');
      form.submit();
    }
  }
});

// Close popovers and modals on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.verified-popover-card.open').forEach(p => p.classList.remove('open'));
    const zenModal = document.getElementById('zenGlobalModalOverlay');
    if (zenModal && zenModal.classList.contains('active')) {
      zenModal.classList.remove('active');
      setTimeout(() => { zenModal.style.display = 'none'; }, 200);
    }
  }
});

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  window.spaRouter = new SpaRouter();
  initAllComponents();
});
