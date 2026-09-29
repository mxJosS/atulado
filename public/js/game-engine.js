/**
 * Plants vs Zombies Style Zen Garden & Tree Game Engine
 * A Tu Lado - Emotional Wellness & Gamification Engine
 */

(function(window) {
  'use strict';

  // Nivel máximo de cada planta. El árbol del logo crece visiblemente hasta aquí.
  const NIVEL_MAXIMO = 50;

  function limitarNivel(n) {
    return Math.max(1, Math.min(NIVEL_MAXIMO, parseInt(n, 10) || 1));
  }

  let soundEnabled = true;
  let audioCtx = null;
  let activeTool = 'water';
  let totalCalmPoints = 125;
  let dailyTasksCompleted = 0;
  const DAILY_TASKS_MAX = 5;
  let currentThought = 'water';
  let skySunInterval = null;

  function getZenStorageKey(key) {
    const uid = (typeof window !== 'undefined' && window.ATULADO_USER_ID !== undefined && window.ATULADO_USER_ID !== null) ? window.ATULADO_USER_ID : 'guest';
    return `atulado_zen_u${uid}_${key}`;
  }

  // Load Economy from LocalStorage
  try {
    const savedTotal = localStorage.getItem(getZenStorageKey('total_calm'));
    if (savedTotal !== null) {
      totalCalmPoints = parseInt(savedTotal, 10) || 0;
    }
  } catch(e) {}

  try {
    const todayStr = new Date().toISOString().slice(0, 10);
    const storedDate = localStorage.getItem(getZenStorageKey('daily_date'));
    if (storedDate === todayStr) {
      dailyTasksCompleted = parseInt(localStorage.getItem(getZenStorageKey('daily_tasks')), 10) || 0;
    } else {
      dailyTasksCompleted = 0;
      localStorage.setItem(getZenStorageKey('daily_date'), todayStr);
      localStorage.setItem(getZenStorageKey('daily_tasks'), '0');
    }
  } catch(e) {}

  // Species metadata
  const ALL_SPECIES = [
    { id: 'Tree', key: 'tree', name: 'Árbol Sabiduría', cost: 0 },
    { id: 'Lotus', key: 'lotus', name: 'Loto Serena', cost: 150 },
    { id: 'Bonsai', key: 'bonsai', name: 'Bonsái Resiliencia', cost: 300 },
    { id: 'Sunflower', key: 'sunflower', name: 'Girasol Gratitud', cost: 450 },
    { id: 'Cactus', key: 'cactus', name: 'Cactus Fortaleza', cost: 600 },
    { id: 'Bamboo', key: 'bamboo', name: 'Bambú de Paz', cost: 750 },
    { id: 'Lavender', key: 'lavender', name: 'Lavanda Calma', cost: 900 },
    { id: 'Orchid', key: 'orchid', name: 'Orquídea Armonía', cost: 1100 }
  ];

  const SPECIES_NAMES = {
    tree: 'Árbol Sabiduría',
    lotus: 'Loto Serena',
    bonsai: 'Bonsái Resiliencia',
    sunflower: 'Girasol Gratitud',
    cactus: 'Cactus Fortaleza',
    bamboo: 'Bambú de Paz',
    lavender: 'Lavanda Calma',
    orchid: 'Orquídea Armonía'
  };

  // Plant Leveling System
  let plantLevels = {
    tree: { level: 1, xp: 0 },
    lotus: { level: 1, xp: 0 },
    bonsai: { level: 1, xp: 0 },
    sunflower: { level: 1, xp: 0 },
    cactus: { level: 1, xp: 0 },
    bamboo: { level: 1, xp: 0 },
    lavender: { level: 1, xp: 0 },
    orchid: { level: 1, xp: 0 }
  };

  try {
    const savedLevels = localStorage.getItem(getZenStorageKey('plant_levels'));
    if (savedLevels) {
      const parsed = JSON.parse(savedLevels);
      Object.keys(plantLevels).forEach(k => {
        if (parsed[k]) {
          plantLevels[k] = {
            level: limitarNivel(parsed[k].level),
            xp: Math.max(0, parseInt(parsed[k].xp, 10) || 0)
          };
        }
      });
    }
  } catch(e) {}

  function saveZenPlantLevels() {
    try {
      localStorage.setItem(getZenStorageKey('plant_levels'), JSON.stringify(plantLevels));
    } catch(e) {}
    guardarEnServidor();
  }

  function getXpRequired(level) {
    if (level >= NIVEL_MAXIMO) return 0;
    const base = 40;
    const growth = Math.pow(1 + (level - 1) * 0.082, 2.25);
    return Math.round(base * growth + (level * 10));
  }

  function addPlantXp(plantKey, amount, showPopup = false) {
    if (!plantLevels[plantKey]) {
      plantLevels[plantKey] = { level: 1, xp: 0 };
    }

    const p = plantLevels[plantKey];
    if (p.level >= NIVEL_MAXIMO) {
      p.level = NIVEL_MAXIMO;
      p.xp = 0;
      saveZenPlantLevels();
      updatePlantHudDisplay();
      updateShopUI();
      return;
    }

    p.xp += amount;
    let req = getXpRequired(p.level);
    let leveledUp = false;

    while (p.xp >= req && p.level < NIVEL_MAXIMO) {
      p.xp -= req;
      p.level += 1;
      leveledUp = true;
      req = getXpRequired(p.level);
    }

    if (p.level >= NIVEL_MAXIMO) {
      p.level = NIVEL_MAXIMO;
      p.xp = 0;
    }

    saveZenPlantLevels();
    updatePlantHudDisplay();
    updateShopUI();

    if (showPopup && amount > 0) {
      spawnScorePopup(`<iconify-icon icon="game-icons:ground-sprout" style="color: #5AB56E; vertical-align: middle;"></iconify-icon> +${amount} XP (${SPECIES_NAMES[plantKey] || 'Planta'})`);
    }

    if (leveledUp) {
      if (plantKey === 'tree' && (zenInventory.activePlant || 'tree') === 'tree') renderActivePlant();
      triggerLevelUpCelebration(plantKey, p.level);
    }
  }

  function triggerLevelUpCelebration(plantKey, newLevel) {
    playMusicChord();
    setTimeout(playCollectChime, 250);
    createSunFx();
    createFertilizerFx();

    const plantName = SPECIES_NAMES[plantKey] || 'Tu árbol';
    if (newLevel >= NIVEL_MAXIMO) {
      window.showZenNotification(
        plantKey === 'tree' ? '¡El Árbol de la Sabiduría!' : `¡Nivel ${NIVEL_MAXIMO} Máximo!`,
        plantKey === 'tree'
          ? `Tu árbol llegó al nivel ${NIVEL_MAXIMO}: ${alturaArbol(newLevel)} de altura y frutos dorados. Lo cultivaste con constancia y cuidado diario.`
          : `¡Felicidades! ${plantName} ha alcanzado el nivel ${NIVEL_MAXIMO} máximo tras tu constancia y cuidado diario. Has cultivado la máxima serenidad y armonía.`,
        "game-icons:laurel-crown",
        "#F1C40F"
      );
    } else {
      window.showZenNotification(
        `¡${plantName} Subió a Nivel ${newLevel}!`,
        plantKey === 'tree'
          ? `Tu árbol ya mide ${alturaArbol(newLevel)}. Cada nivel requiere más cuidado; sigue cuidándolo para llevarlo al Nivel ${NIVEL_MAXIMO}.`
          : `Tu constancia da frutos. Cada nivel requiere más cuidado y dedicación. Sigue cuidando tu planta para llevarla al Nivel ${NIVEL_MAXIMO}.`,
        "game-icons:party-popper",
        "#5AB56E"
      );
    }
  }

  function updatePlantHudDisplay() {
    const activeKey = zenInventory.activePlant || 'tree';
    const pData = plantLevels[activeKey] || { level: 1, xp: 0 };

    const nameElem = document.getElementById('activePlantNameDisplay');
    const levelElem = document.getElementById('pvzCurrentTreeLevel');
    const levelTag = document.getElementById('pvzHudLevelTag');
    const fillElem = document.getElementById('pvzHappinessFill');
    const textElem = document.getElementById('pvzHappinessText');

    if (nameElem) nameElem.innerText = SPECIES_NAMES[activeKey] || 'Árbol Sabiduría';
    if (levelElem) levelElem.innerText = pData.level;

    if (levelTag) {
      if (pData.level >= NIVEL_MAXIMO) {
        levelTag.classList.add('max-level');
        levelTag.innerHTML = `<i class="fa-solid fa-crown"></i> Nv. <strong>${NIVEL_MAXIMO} MAX</strong>`;
      } else {
        levelTag.classList.remove('max-level');
        levelTag.innerHTML = `Nv. <strong id="pvzCurrentTreeLevel">${pData.level}</strong>`;
      }
    }

    // Altura del árbol del logo, como los «pies» del Árbol de la Sabiduría
    const alturaElem = document.getElementById('pvzAlturaArbol');
    if (alturaElem) {
      alturaElem.style.display = activeKey === 'tree' ? '' : 'none';
      alturaElem.innerHTML = `<i class="fa-solid fa-ruler-vertical"></i> ${alturaArbol(pData.level)}`;
    }
    pintarVistasPrevias();

    let percent = 0;
    if (pData.level >= NIVEL_MAXIMO) {
      percent = 100;
    } else {
      const req = getXpRequired(pData.level);
      percent = req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0;
    }

    if (fillElem) fillElem.style.width = percent + '%';
    if (textElem) textElem.innerText = percent + '%';

    actualizarPildoraNivel();
  }

  /**
   * La píldora de la barra superior («Nvl») muestra el nivel real de la planta
   * activa del juego. El servidor sólo pinta un valor inicial; aquí se corrige
   * al cargar cada página y cada vez que la planta sube de nivel.
   */
  function actualizarPildoraNivel() {
    const activeKey = (typeof zenInventory !== 'undefined' && zenInventory.activePlant) || 'tree';
    const pData = plantLevels[activeKey] || { level: 1, xp: 0 };
    document.querySelectorAll('.tree-level-label').forEach(el => {
      el.textContent = `Nvl ${pData.level}`;
    });
  }

  // Persistent Inventory
  let zenInventory = {
    shield: false,
    unlockedPlants: ['tree'],
    activePlant: 'tree',
    unlockedPots: ['terracotta'],
    activePot: 'terracotta'
  };

  try {
    const saved = localStorage.getItem(getZenStorageKey('inventory'));
    if (saved) {
      const parsed = JSON.parse(saved);
      zenInventory = Object.assign(zenInventory, parsed);
    }
  } catch(e) {}

  function saveZenInventory() {
    try {
      localStorage.setItem(getZenStorageKey('inventory'), JSON.stringify(zenInventory));
    } catch(e) {}
    guardarEnServidor();
  }

  function saveZenEconomy() {
    guardarCacheEconomia();
    guardarEnServidor();
  }

  function guardarCacheEconomia() {
    try {
      localStorage.setItem(getZenStorageKey('total_calm'), totalCalmPoints.toString());
      localStorage.setItem(getZenStorageKey('daily_tasks'), dailyTasksCompleted.toString());
      localStorage.setItem(getZenStorageKey('daily_date'), new Date().toISOString().slice(0, 10));
    } catch(e) {}
  }

  /* ── Jardín guardado en la cuenta ─────────────────────────────────────────
     El estado vive en el servidor (POST /jardin, tabla jardines) y sigue a la
     persona en cualquier dispositivo; localStorage queda sólo como caché.
     Si la cuenta aún no tiene jardín, se sube lo que hubiera en este navegador. */
  function exportarEstado() {
    const plantas = {};
    Object.keys(plantLevels).forEach(k => {
      plantas[k] = { nivel: limitarNivel(plantLevels[k].level), xp: Math.max(0, Math.floor(plantLevels[k].xp) || 0) };
    });
    return {
      plantas,
      soles: Math.max(0, Math.floor(totalCalmPoints) || 0),
      tareas_fecha: new Date().toISOString().slice(0, 10),
      tareas_hechas: Math.max(0, dailyTasksCompleted | 0),
      inventario: {
        escudo: !!zenInventory.shield,
        plantas: zenInventory.unlockedPlants,
        planta_activa: zenInventory.activePlant || 'tree',
        macetas: zenInventory.unlockedPots,
        maceta_activa: zenInventory.activePot || 'terracotta'
      }
    };
  }

  function aplicarEstado(estado) {
    if (!estado || typeof estado !== 'object') return;
    Object.keys(plantLevels).forEach(k => {
      const p = estado.plantas && estado.plantas[k];
      if (p) plantLevels[k] = { level: limitarNivel(p.nivel), xp: Math.max(0, parseInt(p.xp, 10) || 0) };
    });
    if (Number.isFinite(estado.soles)) totalCalmPoints = estado.soles;
    const hoy = new Date().toISOString().slice(0, 10);
    dailyTasksCompleted = estado.tareas_fecha === hoy ? (parseInt(estado.tareas_hechas, 10) || 0) : 0;
    const inv = estado.inventario || {};
    zenInventory = Object.assign(zenInventory, {
      shield: !!inv.escudo,
      unlockedPlants: Array.isArray(inv.plantas) && inv.plantas.length ? inv.plantas : zenInventory.unlockedPlants,
      activePlant: inv.planta_activa || zenInventory.activePlant,
      unlockedPots: Array.isArray(inv.macetas) && inv.macetas.length ? inv.macetas : zenInventory.unlockedPots,
      activePot: inv.maceta_activa || zenInventory.activePot
    });
    try {
      localStorage.setItem(getZenStorageKey('plant_levels'), JSON.stringify(plantLevels));
      localStorage.setItem(getZenStorageKey('inventory'), JSON.stringify(zenInventory));
    } catch(e) {}
    guardarCacheEconomia();
  }

  let guardadoPendiente = null;

  function guardarEnServidor(inmediato = false) {
    if (!window.ATULADO_JARDIN_URL) return;
    clearTimeout(guardadoPendiente);
    const enviar = () => {
      guardadoPendiente = null;
      const token = document.querySelector('meta[name="csrf-token"]');
      fetch(window.ATULADO_JARDIN_URL, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
        },
        body: JSON.stringify(exportarEstado())
      }).catch(() => {});
    };
    if (inmediato) enviar();
    else guardadoPendiente = setTimeout(enviar, 1500);
  }

  // Al cerrar o cambiar de pestaña no se pierde el último cambio.
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden' && guardadoPendiente) guardarEnServidor(true);
  });

  if (window.ATULADO_JARDIN) {
    aplicarEstado(window.ATULADO_JARDIN);
  } else if (window.ATULADO_JARDIN_URL) {
    guardarEnServidor();
  }

  const TOOL_SPECS = {
    water: { cost: 0, cooldown: 1500, calmGain: 15, xpGain: 15, name: 'Regadera' },
    sun: { cost: 15, cooldown: 3000, calmGain: 25, xpGain: 20, name: 'Luz Solar' },
    spray: { cost: 20, cooldown: 3500, calmGain: 30, xpGain: 25, name: 'Spray Calma' },
    phonograph: { cost: 25, cooldown: 4000, calmGain: 35, xpGain: 30, name: 'Fonógrafo' },
    fertilizer: { cost: 35, cooldown: 5000, calmGain: 45, xpGain: 45, name: 'Fertilizante' }
  };

  const toolCooldowns = { water: false, sun: false, spray: false, phonograph: false, fertilizer: false };

  const WISDOM_BANK = [
    "Las emociones son como olas: alcanzan un punto máximo y luego se disipan suavemente.",
    "La aceptación radical no significa resignarse, sino dejar de gastar energía en luchar contra lo que ya es.",
    "Inhala en 4 tiempos, sostén en 7 y exhala en 8. Tu sistema nervioso siempre sabe cómo regresar al centro.",
    "Tu mente es un jardín. No puedes evitar que lleguen malas hierbas, pero sí decides cuáles pensamientos regar.",
    "Cuando sientas una tormenta, ancla tus pies en el suelo: 5 cosas que ves, 4 que tocas, 3 que escuchas.",
    "La autocompasión es tratarte con la misma amabilidad con la que cuidarías a un buen amigo en apuros.",
    "No tienes que resolver toda tu vida hoy; solo necesitas dar el siguiente paso amable hacia ti.",
    "Sentir dolor es parte de la condición humana, pero sufrir en soledad no tiene por qué serlo."
  ];

  const THOUGHT_CONFIG = {
    water: { icon: 'game-icons:watering-can', color: '#5DADE2', text: '¡Tengo sed! Ríegame con la regadera' },
    fertilizer: { icon: 'game-icons:fertilizer-bag', color: '#58D68D', text: '¡Necesito nutrientes! Aplica fertilizante' },
    phonograph: { icon: 'game-icons:music-spell', color: '#AF7AC5', text: '¡Toca música relajante en el fonógrafo!' },
    spray: { icon: 'game-icons:delicate-perfume', color: '#48C9B0', text: '¡Disipa la tensión con spray de calma!' },
    sun: { icon: 'game-icons:sunbeams', color: '#F39C12', text: '¡Dame un baño de luz solar dorada!' }
  };

  // Audio tone helper
  function playPvzTone(freq, type = 'sine', duration = 0.35) {
    if (!soundEnabled) return;
    try {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') audioCtx.resume();

      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();

      osc.type = type;
      osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

      gain.gain.setValueAtTime(0.001, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.18, audioCtx.currentTime + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);

      osc.connect(gain);
      gain.connect(audioCtx.destination);

      osc.start();
      osc.stop(audioCtx.currentTime + duration);
    } catch(e) {}
  }

  function playCollectChime() {
    playPvzTone(1046.50, 'sine', 0.15);
    setTimeout(() => playPvzTone(1318.51, 'sine', 0.2), 70);
    setTimeout(() => playPvzTone(1567.98, 'sine', 0.25), 140);
  }

  function playMusicChord() {
    playPvzTone(523.25, 'triangle', 0.7);
    setTimeout(() => playPvzTone(659.25, 'triangle', 0.7), 100);
    setTimeout(() => playPvzTone(783.99, 'triangle', 0.7), 200);
    setTimeout(() => playPvzTone(1046.50, 'sine', 1.0), 300);
  }

  function playBuySuccessTone() {
    playPvzTone(784, 'triangle', 0.2);
    setTimeout(() => playPvzTone(1046.5, 'triangle', 0.3), 100);
    setTimeout(() => playPvzTone(1568, 'sine', 0.5), 220);
  }

  // ════ PUBLIC GLOBAL METHODS ATTACHED TO WINDOW ════
  window.toggleZenSound = function() {
    soundEnabled = !soundEnabled;
    const icon = document.getElementById('soundIcon');
    if (soundEnabled) {
      if (icon) icon.className = 'fa-solid fa-volume-high';
      playPvzTone(660, 'sine', 0.3);
    } else {
      if (icon) icon.className = 'fa-solid fa-volume-xmark';
    }
  };

  window.scrollPvzCarousel = function(trackId, direction) {
    const track = document.getElementById(trackId);
    if (track) {
      track.scrollBy({ left: direction * 230, behavior: 'smooth' });
      playPvzTone(520, 'sine', 0.08);
    }
  };

  window.switchPvzScreen = function(screenName) {
    const gardenScreen = document.getElementById('pvzGardenScreen');
    const shopScreen = document.getElementById('pvzShopScreen');
    const tabGardenBtn = document.getElementById('tabGardenBtn');
    const tabShopBtn = document.getElementById('tabShopBtn');

    if (screenName === 'garden') {
      if (gardenScreen) gardenScreen.style.display = 'flex';
      if (shopScreen) shopScreen.style.display = 'none';
      if (tabGardenBtn) tabGardenBtn.classList.add('active');
      if (tabShopBtn) tabShopBtn.classList.remove('active');
      updatePlantHudDisplay();
      playPvzTone(600, 'sine', 0.15);
    } else {
      if (gardenScreen) gardenScreen.style.display = 'none';
      if (shopScreen) shopScreen.style.display = 'block';
      if (tabGardenBtn) tabGardenBtn.classList.remove('active');
      if (tabShopBtn) tabShopBtn.classList.add('active');
      updateShopUI();
      playPvzTone(750, 'sine', 0.15);
    }
  };

  // Gestión de orientación y pantalla completa para móviles
  async function lockLandscapeMobile() {
    try {
      const docEl = document.documentElement;
      if (docEl.requestFullscreen) {
        await docEl.requestFullscreen().catch(() => {});
      } else if (docEl.webkitRequestFullscreen) {
        docEl.webkitRequestFullscreen();
      }
      if (screen.orientation && screen.orientation.lock) {
        await screen.orientation.lock('landscape').catch(() => {});
      }
    } catch (e) {}
  }

  async function unlockLandscapeMobile() {
    try {
      if (screen.orientation && screen.orientation.unlock) {
        screen.orientation.unlock();
      }
      if (document.fullscreenElement || document.webkitFullscreenElement) {
        if (document.exitFullscreen) {
          await document.exitFullscreen().catch(() => {});
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        }
      }
    } catch (e) {}
  }

  window.openTreeGame = function() {
    const modal = document.getElementById('treeGameModalOverlay');
    const loader = document.getElementById('gameLoaderScreen');
    const gameplay = document.getElementById('gameplayScreen');
    const fill = document.getElementById('loaderProgressFill');
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');

    if (!modal) return;

    // Montar en body para garantizar cobertura de pantalla completa sin márgenes
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    if (exitOverlay) exitOverlay.style.display = 'none';

    document.body.style.overflow = 'hidden';
    modal.classList.add('active');
    if (loader) loader.style.display = 'flex';
    if (gameplay) gameplay.style.display = 'none';
    if (fill) fill.style.width = '0%';

    lockLandscapeMobile();
    playPvzTone(432, 'sine', 0.5);

    setTimeout(() => { if (fill) fill.style.width = '45%'; }, 200);
    setTimeout(() => { if (fill) fill.style.width = '80%'; }, 550);
    setTimeout(() => {
      if (fill) fill.style.width = '100%';
      playPvzTone(528, 'sine', 0.7);
    }, 900);

    setTimeout(() => {
      if (loader) loader.style.display = 'none';
      if (gameplay) {
        gameplay.style.display = 'flex';
        gameplay.style.flexDirection = 'column';
      }
      renderActivePlant();
      renderActivePot();
      updateCalmDisplays();
      updatePlantHudDisplay();
      updateShieldDisplay();
      pickNewThought();
      spawnInitialSuns();
      startSkySuns();
    }, 1150);
  };

  window.promptExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) {
      exitOverlay.style.display = 'flex';
      playPvzTone(520, 'triangle', 0.2);
    } else {
      window.confirmExitTreeGame();
    }
  };

  window.cancelExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) {
      exitOverlay.style.display = 'none';
      playPvzTone(660, 'sine', 0.15);
    }
  };

  window.confirmExitTreeGame = function() {
    const exitOverlay = document.getElementById('pvzExitConfirmOverlay');
    if (exitOverlay) exitOverlay.style.display = 'none';

    document.body.style.overflow = '';
    const modal = document.getElementById('treeGameModalOverlay');
    if (modal) {
      modal.classList.remove('active');
    }
    unlockLandscapeMobile();
    if (skySunInterval) clearInterval(skySunInterval);
    playPvzTone(380, 'sine', 0.25);
  };

  window.closeTreeGame = function() {
    window.promptExitTreeGame();
  };

  window.selectPvzTool = function(toolName) {
    if (toolCooldowns[toolName]) return;

    activeTool = toolName;
    document.querySelectorAll('.pvz-tool-slot').forEach(slot => slot.classList.remove('active'));

    const slotId = 'tool' + toolName.charAt(0).toUpperCase() + toolName.slice(1);
    const slot = document.getElementById(slotId);
    if (slot) slot.classList.add('active');

    playPvzTone(700, 'sine', 0.1);
  };

  function pickNewThought() {
    const tools = ['water', 'sun', 'spray', 'phonograph', 'fertilizer'];
    currentThought = tools[Math.floor(Math.random() * tools.length)];
    const cfg = THOUGHT_CONFIG[currentThought];

    const bubble = document.getElementById('pvzThoughtBubble');
    const iconWrap = document.getElementById('thoughtIconWrap');
    const text = document.getElementById('thoughtText');

    if (bubble && text) {
      if (iconWrap) {
        iconWrap.innerHTML = `<iconify-icon icon="${cfg.icon}" style="color: ${cfg.color}; font-size: 1.4rem; vertical-align: middle;"></iconify-icon>`;
      }
      text.innerText = cfg.text;
      bubble.style.display = 'inline-flex';
    }
  }
  window.pickNewThought = pickNewThought;

  window.satisfyThoughtBubble = function(e) {
    if (e) e.stopPropagation();
    window.selectPvzTool(currentThought);
    window.applyActiveToolToTree();
  };

  window.handleStageClick = function(e) {
    if (e.target.closest('.pvz-collectible-item') || e.target.closest('.pvz-sky-sun') || e.target.closest('.pvz-thought-bubble')) return;
    window.applyActiveToolToTree();
  };

  function spawnScorePopup(text, left = '50%', top = '45%') {
    const canvas = document.getElementById('pvzStageCanvas');
    if (!canvas) return;

    const popup = document.createElement('div');
    popup.className = 'pvz-score-popup';
    popup.innerHTML = text;
    popup.style.left = left;
    popup.style.top = top;
    canvas.appendChild(popup);

    setTimeout(() => popup.remove(), 900);
  }

  window.applyActiveToolToTree = function(e) {
    if (e) e.stopPropagation();

    const spec = TOOL_SPECS[activeTool];
    if (!spec) return;

    if (toolCooldowns[activeTool]) return;

    startToolCooldown(activeTool, spec.cooldown);

    const treeSvg = document.getElementById('interactiveTreeSvg');
    const dialogue = document.getElementById('pvzWisdomDialogue');

    if (treeSvg) {
      treeSvg.classList.remove('pvz-plant-joy');
      void treeSvg.offsetWidth;
      treeSvg.classList.add('pvz-plant-joy');
    }

    const randQuote = WISDOM_BANK[Math.floor(Math.random() * WISDOM_BANK.length)];
    if (dialogue) dialogue.innerText = `"${randQuote}"`;

    if (activeTool === 'water') {
      playPvzTone(587.33, 'sine', 0.6);
      createWaterFx();
    } else if (activeTool === 'sun') {
      playPvzTone(784, 'sine', 0.8);
      createSunFx();
    } else if (activeTool === 'spray') {
      playPvzTone(440, 'sine', 0.6);
      createSprayFx();
    } else if (activeTool === 'phonograph') {
      playMusicChord();
      createMusicFx();
    } else if (activeTool === 'fertilizer') {
      playPvzTone(659.25, 'triangle', 0.7);
      createFertilizerFx();
    }

    const activeKey = zenInventory.activePlant || 'tree';
    let earnedXp = spec.xpGain || 15;

    if (activeTool === currentThought) {
      if (dailyTasksCompleted < DAILY_TASKS_MAX) {
        dailyTasksCompleted += 1;
        totalCalmPoints += 20;
        earnedXp += 40;
        saveZenEconomy();
        updateCalmDisplays();
        spawnScorePopup(`<iconify-icon icon="game-icons:sunbeams" style="color: #F1C40F; vertical-align: middle;"></iconify-icon> +20 Soles (${dailyTasksCompleted}/${DAILY_TASKS_MAX})`);
      } else {
        earnedXp += 25;
        spawnScorePopup(`<iconify-icon icon="game-icons:sparkles" style="color: #F1C40F; vertical-align: middle;"></iconify-icon> ¡Deseo cumplido! (+${earnedXp} XP)`);
      }

      spawnDroppingSun(true);
      setTimeout(pickNewThought, 2800);
    } else {
      spawnDroppingSun(false);
    }

    addPlantXp(activeKey, earnedXp, true);
  };

  function startToolCooldown(toolName, durationMs) {
    toolCooldowns[toolName] = true;
    const slotId = 'tool' + toolName.charAt(0).toUpperCase() + toolName.slice(1);
    const slot = document.getElementById(slotId);
    if (slot) slot.classList.add('cooling');

    setTimeout(() => {
      toolCooldowns[toolName] = false;
      if (slot) slot.classList.remove('cooling');
    }, durationMs);
  }

  window.showZenNotification = function(title, message, icon = 'game-icons:sunbeams', color = '#F1C40F') {
    const modal = document.getElementById('pvzNotificationModal');
    const titleElem = document.getElementById('pvzNotifTitle');
    const msgElem = document.getElementById('pvzNotifMessage');
    const iconContainer = document.getElementById('pvzNotifIconContainer');
    const iconWrap = document.getElementById('pvzNotifIconWrap');

    if (titleElem) titleElem.innerText = title;
    if (msgElem) msgElem.innerText = message;

    if (iconContainer) {
      if (icon.startsWith('game-icons:') || icon.startsWith('ra ') || icon.startsWith('fa-')) {
        if (icon.startsWith('game-icons:')) {
          iconContainer.innerHTML = `<iconify-icon icon="${icon}" style="font-size: 2.1rem; color: ${color}; line-height: 1;"></iconify-icon>`;
        } else if (icon.startsWith('ra ')) {
          iconContainer.innerHTML = `<i class="${icon}" style="font-size: 1.9rem; color: ${color};"></i>`;
        } else {
          iconContainer.innerHTML = `<i class="${icon}" style="font-size: 1.7rem; color: ${color};"></i>`;
        }
      } else {
        iconContainer.innerHTML = `<iconify-icon icon="game-icons:${icon}" style="font-size: 2.1rem; color: ${color}; line-height: 1;"></iconify-icon>`;
      }
    }

    if (iconWrap) {
      iconWrap.style.color = color;
      iconWrap.style.borderColor = color;
      iconWrap.style.background = color + '22';
      iconWrap.style.boxShadow = `0 0 24px ${color}55`;
    }

    if (modal) modal.style.display = 'flex';
    playPvzTone(350, 'triangle', 0.2);
  };

  window.closeZenNotification = function() {
    const modal = document.getElementById('pvzNotificationModal');
    if (modal) modal.style.display = 'none';
    playPvzTone(600, 'sine', 0.15);
  };

  window.buyStreakShield = function() {
    if (zenInventory.shield) {
      window.showZenNotification("Escudo Ya Activo", "Ya cuentas con un Escudo Zen activo que protege tu racha diaria.", "game-icons:shield-reflect", "#5DADE2");
      return;
    }

    if (totalCalmPoints < 100) {
      playPvzTone(260, 'sawtooth', 0.25);
      window.showZenNotification("Soles Insuficientes", "Necesitas 100 Soles para canjear el Escudo Protector de Racha. ¡Completa tareas o recolecta soles en el jardín!", "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= 100;
    zenInventory.shield = true;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    updateShieldDisplay();
    updateShopUI();

    window.showZenNotification("¡Escudo Activado!", "Tu racha diaria ahora está blindada contra ausencias. Se ha añadido la insignia de protección en tu barra superior.", "game-icons:shield-reflect", "#5DADE2");
  };

  window.unlockOrSelectPlant = function(plantKey, cost) {
    if (zenInventory.unlockedPlants.includes(plantKey)) {
      window.selectPlant(plantKey);
      return;
    }

    if (totalCalmPoints < cost) {
      playPvzTone(260, 'sawtooth', 0.25);
      window.showZenNotification("Soles Insuficientes", `Necesitas ${cost} Soles para desbloquear esta especie. Sigue completando tareas para acumular más soles.`, "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= cost;
    zenInventory.unlockedPlants.push(plantKey);
    zenInventory.activePlant = plantKey;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    renderActivePlant();
    updatePlantHudDisplay();
    updateShopUI();

    window.showZenNotification("¡Nueva Especie Desbloqueada!", `Has desbloqueado ${SPECIES_NAMES[plantKey] || 'esta planta'} y ahora se encuentra en tu Invernadero Zen lista para subir de nivel.`, "game-icons:ground-sprout", "#5AB56E");
  };

  window.selectPlant = function(plantKey) {
    zenInventory.activePlant = plantKey;
    saveZenInventory();
    playPvzTone(660, 'sine', 0.2);
    renderActivePlant();
    updatePlantHudDisplay();
    updateShopUI();
  };

  window.unlockOrSelectPot = function(potKey, cost) {
    if (zenInventory.unlockedPots.includes(potKey)) {
      window.selectPot(potKey);
      return;
    }

    if (totalCalmPoints < cost) {
      playPvzTone(260, 'sawtooth', 0.25);
      window.showZenNotification("Soles Insuficientes", `Necesitas ${cost} Soles para desbloquear esta maceta artesanal.`, "game-icons:sunbeams", "#F1C40F");
      return;
    }

    totalCalmPoints -= cost;
    zenInventory.unlockedPots.push(potKey);
    zenInventory.activePot = potKey;
    saveZenInventory();
    saveZenEconomy();

    playBuySuccessTone();
    updateCalmDisplays();
    renderActivePot();
    updateShopUI();

    window.showZenNotification("¡Maceta Equipada!", "Has desbloqueado y equipado tu nueva maceta artesanal.", "game-icons:flower-pot", "#C8B87A");
  };

  window.selectPot = function(potKey) {
    zenInventory.activePot = potKey;
    saveZenInventory();
    playPvzTone(600, 'sine', 0.2);
    renderActivePot();
    updateShopUI();
  };

  function updateShieldDisplay() {
    const shieldInd = document.getElementById('shieldIndicator');
    if (shieldInd) {
      shieldInd.style.display = zenInventory.shield ? 'inline-flex' : 'none';
    }
  }

  function renderActivePot() {
    const potWrapper = document.getElementById('pvzTreePot');
    if (!potWrapper) return;
    potWrapper.className = 'pvz-pot-wrapper pot-' + (zenInventory.activePot || 'terracotta');
  }

  /* ══ Árbol del logo que crece nivel a nivel (1–50) ═══════════════════════
     Pixel art procedural en una rejilla fija de 48×72, con el estilo del logo:
     copa redonda con contorno oscuro y sombreado, destello verde claro,
     racimos rojos y tronco café con su rama. Cada nivel conserva todo lo del
     anterior: flores y frutos tienen posiciones fijas y un nivel de aparición.
       1       brote de dos hojas
       2–5     retoño: el tallo sube y echa hojas
       6–15    arbolito: tronco y copa; aparecen los primeros racimos rojos
       16–25   la silueta completa del logo
       26–40   más alto: copa en capas, raíces y más racimos
       41–49   frondoso: flores rosas y luciérnagas
       50      frutos dorados y aura: el Árbol de la Sabiduría
     En el escenario, la «cámara» se aleja conforme crece (como en el juego
     original): de brote grande y pixelado a árbol alto y lleno de detalle. */
  const ARBOL_ANCHO = 48;
  const ARBOL_ALTO = 72;
  const TONO = {
    borde: '#0B2E16', sombra: '#1F6B35', base: '#2A8744', luz: '#35A354', brillo: '#72D98A',
    tallo: '#3F9C4A', hoja: '#6CCB5F',
    tronco: '#7A3E1C', troncoLuz: '#9A5426', troncoSombra: '#552810', troncoBorde: '#2E1406',
    rojo: '#E62E2E', rojoOscuro: '#A91E1E', rojoBrillo: '#FF6B5E',
    oro: '#F4C542', oroOscuro: '#B8860B', oroBrillo: '#FFF3B0',
    rosa: '#F8BBD0', rosaOscuro: '#EC407A',
    luciernaga: '#FFE680',
    pasto: '#34A853', pastoLuz: '#6FDB86', pastoSombra: '#1F7A3A'
  };
  let contadorArboles = 0;

  function azarConSemilla(semilla) {
    let a = semilla >>> 0;
    return function() {
      a = (a + 0x6D2B79F5) >>> 0;
      let t = a;
      t = Math.imul(t ^ (t >>> 15), t | 1);
      t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  // Puntos fijos en un disco unitario, separados entre sí; cada uno aparece en su nivel.
  function puntosFijos(semilla, cuantos, radio2, separacion2, desde, hasta) {
    const azar = azarConSemilla(semilla);
    const puntos = [];
    for (let i = 0; puntos.length < cuantos && i < 8000; i++) {
      const u = azar() * 2 - 1, v = azar() * 2 - 1;
      if (u * u + v * v > radio2) continue;
      if (puntos.some(p => (p.u - u) ** 2 + (p.v - v) ** 2 < separacion2)) continue;
      puntos.push({ u, v });
    }
    const n = puntos.length;
    return puntos.map((p, i) => Object.assign(p, { desde: Math.round(desde + (hasta - desde) * (n > 1 ? i / (n - 1) : 0)) }));
  }

  const RACIMOS = puntosFijos(20260928, 16, 0.58, 0.13, 8, 38);
  const RACIMOS_MEDIO = puntosFijos(1717, 8, 0.45, 0.18, 27, 45);
  const RACIMOS_CORONA = puntosFijos(4242, 3, 0.3, 0.25, 41, 48);
  const FLORES = puntosFijos(909, 12, 0.75, 0.06, 41, 49);
  const LUCIERNAGAS = [[0.1, 0.34], [0.88, 0.26], [0.14, 0.6], [0.86, 0.55], [0.3, 0.12], [0.72, 0.1]]
    .map((p, i) => ({ x: p[0], y: p[1], desde: 44 + i }));

  function alturaArbol(nivel) {
    return (0.1 + (limitarNivel(nivel) - 1) * 0.24).toFixed(1).replace('.', ',') + ' m';
  }

  /**
   * SVG del árbol en su nivel.
   *   opciones.id / clase   atributos del <svg>
   *   opciones.suelo        dibuja la franja de pasto del logo
   *   opciones.recorte      encuadra sólo el árbol (vistas previas pequeñas)
   */
  function dibujarArbol(nivel, opciones = {}) {
    const L = limitarNivel(nivel);
    const W = ARBOL_ANCHO, H = ARBOL_ALTO;
    const rej = Array.from({ length: H }, () => new Array(W).fill(null));
    const dentro = (x, y) => x >= 0 && x < W && y >= 0 && y < H;
    const pon = (x, y, c) => { x = Math.round(x); y = Math.round(y); if (dentro(x, y)) rej[y][x] = c; };
    const lleno = (x, y) => dentro(x, y) && rej[y][x] !== null;
    const esTronco = c => c === TONO.tronco || c === TONO.troncoLuz || c === TONO.troncoSombra;
    const esCopa = c => c === TONO.base || c === TONO.luz || c === TONO.sombra || c === TONO.brillo;

    const pie = opciones.suelo ? H - 6 : H - 1;   // última fila del árbol
    const cx = 24;
    let copaCentro = null;                         // para el aura del nivel 50
    let copaMasAlta = null;

    if (L <= 5) {
      // Brote y retoño: tallo verde (leñoso abajo desde el nivel 4) y hojas alternadas
      const alto = [0, 3, 4, 6, 8, 10][L];
      const x = 23;
      for (let i = 0; i < alto; i++) pon(x, pie - i, (L >= 4 && i < 2) ? TONO.tronco : TONO.tallo);
      const tope = pie - alto + 1;
      const hoja = (y, lado) => {
        pon(x + lado, y, TONO.hoja);
        pon(x + 2 * lado, y, TONO.hoja);
        pon(x + 2 * lado, y - 1, TONO.hoja);
        pon(x + 3 * lado, y - 1, TONO.brillo);
      };
      hoja(tope, -1);
      hoja(tope, 1);
      if (L >= 3) pon(x, tope - 1, TONO.brillo);
      for (let y = pie - 2, lado = -1; y > tope + 1; y -= 3, lado = -lado) hoja(y, lado);
    } else {
      const rx = Math.min(20, 4.2 + L * 0.34);
      const ry = rx * 0.84;
      const tAncho = L < 12 ? 2 : L < 24 ? 3 : L < 38 ? 4 : 5;
      const tAlto = Math.round(3 + L * 0.2);
      const baseCopa = pie - tAlto;
      const cy = baseCopa - ry + 2;

      const copas = [{ x: cx, y: cy, rx, ry, capa: 0 }];
      if (L >= 16) {
        copas.push({ x: cx - rx * 0.55, y: cy + ry * 0.28, rx: rx * 0.5, ry: ry * 0.46, capa: 0 });
        copas.push({ x: cx + rx * 0.55, y: cy + ry * 0.28, rx: rx * 0.5, ry: ry * 0.46, capa: 0 });
      }
      // Copa que se ensancha hacia arriba en lóbulos redondos (como el logo, más grande)
      let izquierda = null, derecha = null, corona = null;
      if (L >= 26) {
        izquierda = { x: cx - rx * 0.3, y: cy - ry * 0.72, rx: rx * 0.6, ry: ry * 0.62, capa: 1 };
        derecha = { x: cx + rx * 0.3, y: cy - ry * 0.72, rx: rx * 0.6, ry: ry * 0.62, capa: 1 };
        copas.push(izquierda, derecha);
      }
      if (L >= 40) { corona = { x: cx, y: cy - ry * 1.12, rx: rx * 0.5, ry: ry * 0.45, capa: 2 }; copas.push(corona); }
      copaCentro = copas[0];
      copaMasAlta = corona || izquierda || copas[0];

      // Tronco: sube dos filas dentro de la copa
      const x0 = Math.round(cx - tAncho / 2);
      for (let y = baseCopa - 2; y <= pie; y++) {
        for (let i = 0; i < tAncho; i++) {
          const c = i === tAncho - 1 ? TONO.troncoSombra : (i === 1 || (tAncho === 2 && i === 0)) ? TONO.troncoLuz : TONO.tronco;
          pon(x0 + i, y, c);
        }
      }
      // Rama a la derecha, como en el logo
      if (L >= 10) {
        const by = baseCopa + Math.max(1, Math.round(tAlto * 0.3));
        const largo = 2 + Math.floor(L / 12);
        for (let i = 0; i < largo; i++) {
          pon(x0 + tAncho + i, by, TONO.tronco);
          pon(x0 + tAncho + i, by + 1, TONO.troncoSombra);
        }
        pon(x0 + tAncho + largo - 1, by - 1, TONO.tronco);
      }
      // Raíces
      if (L >= 26) {
        pon(x0 - 1, pie, TONO.tronco);
        pon(x0 + tAncho, pie, TONO.troncoSombra);
      }
      if (L >= 34) {
        pon(x0 - 2, pie, TONO.tronco);
        pon(x0 - 1, pie - 1, TONO.tronco);
        pon(x0 + tAncho + 1, pie, TONO.troncoSombra);
        pon(x0 + tAncho, pie - 1, TONO.troncoSombra);
      }

      // Copa: la capa más alta queda encima; luz desde arriba a la izquierda
      for (let y = 0; y < H; y++) {
        for (let x = 0; x < W; x++) {
          let mejor = null, mejorD = 2;
          for (const c of copas) {
            const d = ((x + 0.5 - c.x) / c.rx) ** 2 + ((y + 0.5 - c.y) / c.ry) ** 2;
            if (d <= 1 && (mejor === null || c.capa > mejor.capa || (c.capa === mejor.capa && d < mejorD))) { mejor = c; mejorD = d; }
          }
          if (!mejor) continue;
          const nx = (x + 0.5 - mejor.x) / mejor.rx;
          const ny = (y + 0.5 - mejor.y) / mejor.ry;
          const luz = -0.55 * nx - 0.8 * ny;
          rej[y][x] = (ny > 0.6 || luz < -0.55) ? TONO.sombra : luz > 0.45 ? TONO.luz : TONO.base;
        }
      }

      // Destello verde claro del logo (arriba a la izquierda de la copa más alta)
      if (L >= 12) {
        const w = L >= 25 ? 4 : 3, h = L >= 25 ? 3 : 2;
        const hx = Math.round(copaMasAlta.x - copaMasAlta.rx * 0.45);
        const hy = Math.round(copaMasAlta.y - copaMasAlta.ry * 0.55);
        for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) if (lleno(hx + i, hy + j)) pon(hx + i, hy + j, TONO.brillo);
      }

      // Racimos rojos (dorados algunos en el nivel 50)
      const racimo = (px, py, grande, dorado) => {
        const R = dorado ? TONO.oro : TONO.rojo;
        const O = dorado ? TONO.oroOscuro : TONO.rojoOscuro;
        const B = dorado ? TONO.oroBrillo : TONO.rojoBrillo;
        const forma = grande
          ? [[1, 0, R], [2, 0, R], [0, 1, R], [1, 1, B], [2, 1, R], [3, 1, R], [0, 2, R], [1, 2, R], [2, 2, R], [3, 2, O], [1, 3, R], [2, 3, O]]
          : [[1, 0, R], [0, 1, R], [1, 1, B], [2, 1, R], [1, 2, O]];
        const ox = Math.round(px) - (grande ? 2 : 1), oy = Math.round(py) - (grande ? 2 : 1);
        forma.forEach(([dx, dy, c]) => {
          const x = ox + dx, y = oy + dy;
          if (lleno(x, y) && esCopa(rej[y][x])) rej[y][x] = c;
        });
      };
      const sembrar = (lista, copa, inicio) => {
        if (!copa) return;
        lista.forEach((r, i) => {
          if (L >= r.desde) racimo(copa.x + r.u * copa.rx * 0.95, copa.y + r.v * copa.ry * 0.95, L >= 20, L >= NIVEL_MAXIMO && (i + inicio) % 3 === 0);
        });
      };
      sembrar(RACIMOS, copas[0], 0);
      sembrar(RACIMOS_MEDIO.filter((r, i) => i % 2 === 0), izquierda, 1);
      sembrar(RACIMOS_MEDIO.filter((r, i) => i % 2 === 1), derecha, 2);
      sembrar(RACIMOS_CORONA, corona, 0);

      // Flores rosas del árbol frondoso
      FLORES.forEach(f => {
        if (L < f.desde) return;
        const x = Math.round(cx + f.u * rx), y = Math.round(cy + f.v * ry);
        if (lleno(x, y) && esCopa(rej[y][x]) && lleno(x, y + 1) && esCopa(rej[y + 1][x])) {
          rej[y][x] = TONO.rosa;
          rej[y + 1][x] = TONO.rosaOscuro;
        }
      });
    }

    // Franja de pasto del logo (vistas previas): un poco más ancha que el árbol
    if (opciones.suelo) {
      let izq = W, der = 0;
      for (let y = 0; y <= pie; y++) for (let x = 0; x < W; x++) if (rej[y][x]) { izq = Math.min(izq, x); der = Math.max(der, x); }
      const desde = Math.max(2, izq - 4), hasta = Math.min(W - 3, der + 4);
      for (let y = pie + 1; y <= H - 2; y++) {
        for (let x = desde; x <= hasta; x++) {
          rej[y][x] = y === pie + 1 ? TONO.pastoLuz : y === H - 2 ? TONO.pastoSombra : TONO.pasto;
        }
      }
    }

    // Contorno oscuro de una celda alrededor de todo, como en el logo
    const contorno = [];
    for (let y = 0; y < H; y++) {
      for (let x = 0; x < W; x++) {
        if (rej[y][x] !== null) continue;
        const vecinos = [[1, 0], [-1, 0], [0, 1], [0, -1]]
          .map(([dx, dy]) => (lleno(x + dx, y + dy) ? rej[y + dy][x + dx] : null))
          .filter(Boolean);
        if (vecinos.length) contorno.push([x, y, vecinos.every(esTronco) ? TONO.troncoBorde : TONO.borde]);
      }
    }
    contorno.forEach(([x, y, c]) => { rej[y][x] = c; });

    // Corona de destellos dorados en el nivel máximo
    let arriba = H;
    for (let y = 0; y < H && arriba === H; y++) if (rej[y].some(Boolean)) arriba = y;
    if (L >= NIVEL_MAXIMO) {
      [[cx, arriba - 3], [cx - 6, arriba - 1], [cx + 6, arriba - 1]].forEach(([sx, sy]) => {
        [[0, 0], [1, 0], [-1, 0], [0, 1], [0, -1]].forEach(([dx, dy]) => {
          if (dentro(sx + dx, sy + dy) && !lleno(sx + dx, sy + dy)) pon(sx + dx, sy + dy, dx || dy ? TONO.oro : TONO.oroBrillo);
        });
      });
      arriba = Math.max(0, arriba - 4);
    }

    // Filas de <rect>: une las celdas iguales contiguas
    let rects = '';
    for (let y = 0; y < H; y++) {
      for (let x = 0; x < W;) {
        const c = rej[y][x];
        if (!c) { x++; continue; }
        let x2 = x + 1;
        while (x2 < W && rej[y][x2] === c) x2++;
        rects += `<rect x="${x}" y="${y}" width="${x2 - x}" height="1" fill="${c}"/>`;
        x = x2;
      }
    }

    // Luciérnagas (titilan con css/style.css) y aura dorada del nivel 50
    let luces = '';
    LUCIERNAGAS.forEach(l => {
      const x = Math.round(l.x * W), y = Math.round(arriba + l.y * (H - arriba));
      if (L >= l.desde && !lleno(x, y)) luces += `<rect class="arbol-luciernaga" x="${x}" y="${y}" width="1" height="1" fill="${TONO.luciernaga}"/>`;
    });
    let defs = '', aura = '';
    if (L >= NIVEL_MAXIMO && copaCentro) {
      const idAura = 'auraArbol' + (++contadorArboles);
      defs = `<defs><radialGradient id="${idAura}"><stop offset="0%" stop-color="#FFE680" stop-opacity="0.55"/><stop offset="100%" stop-color="#FFE680" stop-opacity="0"/></radialGradient></defs>`;
      aura = `<circle cx="${cx}" cy="${(copaCentro.y - copaCentro.ry * 0.6).toFixed(1)}" r="${(copaCentro.rx * 1.45).toFixed(1)}" fill="url(#${idAura})" shape-rendering="auto"/>`;
    }

    // Encuadre
    let vb;
    if (opciones.recorte) {
      let minX = W, maxX = 0, minY = H, maxY = 0;
      for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) if (rej[y][x]) {
        minX = Math.min(minX, x); maxX = Math.max(maxX, x); minY = Math.min(minY, y); maxY = Math.max(maxY, y);
      }
      const lado = Math.max(maxX - minX + 1, maxY - minY + 1) + 2;
      const mx = (minX + maxX + 1) / 2, my = (minY + maxY + 1) / 2;
      vb = `${(mx - lado / 2).toFixed(2)} ${(my - lado / 2).toFixed(2)} ${lado} ${lado}`;
    } else {
      // Escenario: la «cámara» se aleja conforme el árbol crece
      const fraccion = 0.3 + 0.62 * Math.pow((L - 1) / (NIVEL_MAXIMO - 1), 0.85);
      const filas = Math.min(H, (H - arriba) / fraccion);
      const columnas = filas * W / H;
      vb = `${(cx - columnas / 2).toFixed(2)} ${(H - filas).toFixed(2)} ${columnas.toFixed(2)} ${filas.toFixed(2)}`;
    }

    const id = opciones.id ? ` id="${opciones.id}"` : '';
    return `<svg${id} class="${opciones.clase || ''}" viewBox="${vb}" preserveAspectRatio="xMidYMax meet" shape-rendering="crispEdges" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Árbol de A tu lado, nivel ${L}">${defs}${aura}${rects}${luces}</svg>`;
  }

  // Vistas previas del árbol (tablero y tienda): elementos con data-arbol-vista
  function pintarVistasPrevias() {
    const nivel = (plantLevels.tree || { level: 1 }).level;
    document.querySelectorAll('[data-arbol-vista]').forEach(el => {
      if (el.getAttribute('data-arbol-vista') === String(nivel)) return;
      el.setAttribute('data-arbol-vista', String(nivel));
      el.innerHTML = dibujarArbol(nivel, { recorte: true, suelo: true, clase: 'arbol-vista-svg' });
    });
    document.querySelectorAll('.arbol-nivel-texto').forEach(el => { el.textContent = nivel; });
  }

  // El árbol ocupa el alto disponible del escenario (sobre la maceta)
  function ajustarAlturaArbol() {
    const svg = document.getElementById('interactiveTreeSvg');
    const escenario = document.getElementById('pvzStageCanvas');
    if (!svg || !escenario || !svg.classList.contains('arbol-crece')) return;
    const alto = Math.max(110, Math.min(380, escenario.clientHeight - 70));
    svg.style.setProperty('height', alto + 'px', 'important');
    svg.style.setProperty('width', Math.round(alto * ARBOL_ANCHO / ARBOL_ALTO) + 'px', 'important');
  }
  window.addEventListener('resize', ajustarAlturaArbol);

  /** Herramienta de prueba de las cuentas principales: sube o baja un nivel al instante. */
  window.ajustarNivelPlanta = function(delta) {
    const activeKey = zenInventory.activePlant || 'tree';
    const p = plantLevels[activeKey] || (plantLevels[activeKey] = { level: 1, xp: 0 });
    const nuevo = limitarNivel(p.level + delta);
    if (nuevo === p.level) {
      playPvzTone(260, 'sawtooth', 0.15);
      return;
    }
    p.level = nuevo;
    p.xp = 0;
    saveZenPlantLevels();
    renderActivePlant();
    updatePlantHudDisplay();
    updateShopUI();
    if (delta > 0) {
      playCollectChime();
      createSunFx();
    } else {
      playPvzTone(392, 'sine', 0.25);
    }
    const svg = document.getElementById('interactiveTreeSvg');
    if (svg) {
      svg.classList.remove('pvz-plant-joy');
      void svg.getBoundingClientRect();
      svg.classList.add('pvz-plant-joy');
    }
  };

  window.dibujarArbolAtulado = dibujarArbol;

  function renderActivePlant() {
    const container = document.getElementById('pvzPlantSvgContainer');
    const plantNameDisplay = document.getElementById('activePlantNameDisplay');
    if (!container) return;

    const p = zenInventory.activePlant || 'tree';
    let svgHtml = '';

    if (p === 'lotus') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Loto Serena';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <ellipse cx="16" cy="27" rx="14" ry="4" fill="#145A32"/>
          <ellipse cx="16" cy="26" rx="12" ry="3" fill="#1E8449"/>
          <path d="M16 8 C11 15 8 22 16 26 C24 22 21 15 16 8 Z" fill="#FADBD8"/>
          <path d="M16 11 C12 16 10 21 16 25 C22 21 20 16 16 11 Z" fill="#F1948A"/>
          <path d="M9 16 C6 20 8 24 16 26 C12 24 10 20 9 16 Z" fill="#E8DAEF"/>
          <path d="M23 16 C26 20 24 24 16 26 C20 24 22 20 23 16 Z" fill="#E8DAEF"/>
          <circle cx="16" cy="22" r="3" fill="#F1C40F"/>
        </svg>
      `;
    } else if (p === 'bonsai') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Bonsái Resiliencia';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <path d="M15 28 Q18 22 13 18 Q8 14 16 10 Q14 14 17 18 Q19 23 17 28 Z" fill="#5D4037"/>
          <ellipse cx="11" cy="14" rx="7" ry="4" fill="#1E4A25"/>
          <ellipse cx="11" cy="13" rx="5" ry="3" fill="#2E7D32"/>
          <ellipse cx="21" cy="10" rx="8" ry="4" fill="#1E4A25"/>
          <ellipse cx="21" cy="9" rx="6" ry="3" fill="#388E3C"/>
          <ellipse cx="16" cy="7" rx="6" ry="3" fill="#4CAF50"/>
        </svg>
      `;
    } else if (p === 'sunflower') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Girasol Gratitud';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="15" y="16" width="2" height="12" fill="#2E7D32"/>
          <path d="M15 22 Q10 20 8 23 Q12 25 15 23 Z" fill="#4CAF50"/>
          <path d="M17 20 Q22 18 24 21 Q20 23 17 21 Z" fill="#4CAF50"/>
          <circle cx="16" cy="12" r="10" fill="#F39C12"/>
          <circle cx="16" cy="12" r="8.5" fill="#F1C40F"/>
          <circle cx="16" cy="12" r="5" fill="#5D4037"/>
          <circle cx="16" cy="12" r="4" fill="#3E2723"/>
        </svg>
      `;
    } else if (p === 'cactus') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Cactus Fortaleza';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="13" y="8" width="6" height="21" rx="3" fill="#27AE60"/>
          <rect x="14" y="9" width="4" height="19" rx="2" fill="#2ECC71"/>
          <path d="M13 18 H8 V12 H10 V16 H13 Z" fill="#27AE60"/>
          <path d="M19 16 H24 V10 H22 V14 H19 Z" fill="#27AE60"/>
          <circle cx="16" cy="6" r="3.5" fill="#E91E63"/>
          <circle cx="16" cy="6" r="1.8" fill="#F1C40F"/>
          <circle cx="16" cy="12" r="0.9" fill="#FFF59D"/>
          <circle cx="16" cy="18" r="0.9" fill="#FFF59D"/>
          <circle cx="16" cy="24" r="0.9" fill="#FFF59D"/>
          <circle cx="9" cy="13" r="0.7" fill="#FFF59D"/>
          <circle cx="23" cy="11" r="0.7" fill="#FFF59D"/>
        </svg>
      `;
    } else if (p === 'bamboo') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Bambú de Paz';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <rect x="8" y="8" width="3.5" height="21" rx="1.5" fill="#2E7D32"/>
          <rect x="7" y="14" width="5.5" height="1.2" fill="#1B5E20"/>
          <rect x="7" y="21" width="5.5" height="1.2" fill="#1B5E20"/>
          <rect x="14.5" y="4" width="4" height="25" rx="2" fill="#43A047"/>
          <rect x="13.5" y="10" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="13.5" y="17" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="13.5" y="24" width="6" height="1.2" fill="#2E7D32"/>
          <rect x="21" y="9" width="3.5" height="20" rx="1.5" fill="#388E3C"/>
          <rect x="20" y="16" width="5.5" height="1.2" fill="#1B5E20"/>
          <path d="M18.5 10 Q25 8 28 11 Q23 12.5 18.5 11.5 Z" fill="#81C784"/>
          <path d="M11.5 14 Q5 12 4 15 Q8.5 16.5 11.5 15.5 Z" fill="#81C784"/>
          <path d="M18.5 5 Q24 3 26 6 Q22 7.5 18.5 6.5 Z" fill="#A5D6A7"/>
        </svg>
      `;
    } else if (p === 'lavender') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Lavanda Calma';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <line x1="16" y1="12" x2="16" y2="28" stroke="#388E3C" stroke-width="2"/>
          <line x1="10" y1="14" x2="15" y2="28" stroke="#2E7D32" stroke-width="1.8"/>
          <line x1="22" y1="14" x2="17" y2="28" stroke="#2E7D32" stroke-width="1.8"/>
          <ellipse cx="16" cy="6" rx="3.5" ry="4.5" fill="#8E24AA"/>
          <ellipse cx="16" cy="10" rx="4" ry="3.5" fill="#BA68C8"/>
          <ellipse cx="16" cy="14" rx="3.5" ry="3" fill="#CE93D8"/>
          <ellipse cx="10" cy="10" rx="3" ry="4" fill="#7B1FA2"/>
          <ellipse cx="11" cy="14" rx="3.2" ry="3" fill="#AB47BC"/>
          <ellipse cx="22" cy="10" rx="3" ry="4" fill="#7B1FA2"/>
          <ellipse cx="21" cy="14" rx="3.2" ry="3" fill="#AB47BC"/>
        </svg>
      `;
    } else if (p === 'orchid') {
      if (plantNameDisplay) plantNameDisplay.innerText = 'Orquídea Armonía';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px;">
          <path d="M12 28 Q15 16 22 6" stroke="#2E7D32" stroke-width="2.5" fill="none"/>
          <ellipse cx="9" cy="27" rx="7" ry="2.5" fill="#388E3C" transform="rotate(-20 9 27)"/>
          <ellipse cx="17" cy="27" rx="7" ry="2.5" fill="#388E3C" transform="rotate(20 17 27)"/>
          <circle cx="16" cy="14" r="5" fill="#F3E5F5"/>
          <ellipse cx="12.5" cy="13" rx="3.5" ry="2.5" fill="#CE93D8"/>
          <ellipse cx="19.5" cy="13" rx="3.5" ry="2.5" fill="#CE93D8"/>
          <circle cx="16" cy="15.5" r="2.5" fill="#E91E63"/>
          <circle cx="16" cy="15.5" r="1" fill="#FDD835"/>
          <circle cx="21" cy="6.5" r="3.8" fill="#F8BBD0"/>
          <ellipse cx="18.5" cy="6" rx="2.5" ry="1.8" fill="#EC407A"/>
          <ellipse cx="23.5" cy="6" rx="2.5" ry="1.8" fill="#EC407A"/>
          <circle cx="21" cy="7.5" r="1.8" fill="#C2185B"/>
        </svg>
      `;
    } else {
      // El árbol del logo: la planta principal, que crece con cada nivel
      if (plantNameDisplay) plantNameDisplay.innerText = SPECIES_NAMES.tree;
      svgHtml = dibujarArbol((plantLevels.tree || { level: 1 }).level, { id: 'interactiveTreeSvg', clase: 'ptree arbol-crece' });
    }

    container.innerHTML = svgHtml;

    const escenario = document.getElementById('pvzStageCanvas');
    if (escenario) escenario.classList.toggle('escenario-arbol', !!document.querySelector('#pvzPlantSvgContainer .arbol-crece'));
    ajustarAlturaArbol();
  }

  function updateShopUI() {
    const shopBal = document.getElementById('shopCalmBalance');
    if (shopBal) shopBal.innerText = totalCalmPoints;

    const btnShield = document.getElementById('btnShopShield');
    const cardShield = document.getElementById('shopCardShield');
    const priceBadgeShield = document.getElementById('priceBadgeShield');
    if (btnShield && cardShield) {
      if (zenInventory.shield) {
        btnShield.className = 'pvz-shop-action-btn active-equipped';
        btnShield.innerHTML = '<i class="fa-solid fa-check"></i> <span>Escudo Activo</span>';
        btnShield.disabled = true;
        cardShield.classList.add('owned');
        if (priceBadgeShield) priceBadgeShield.style.display = 'none';
      } else {
        btnShield.className = 'pvz-shop-action-btn';
        btnShield.innerHTML = '<i class="fa-solid fa-hand-holding-heart"></i> <span>Canjear Escudo</span>';
        btnShield.disabled = false;
        cardShield.classList.remove('owned');
        if (priceBadgeShield) priceBadgeShield.style.display = 'inline-flex';
      }
    }

    ALL_SPECIES.forEach(p => {
      const btn = document.getElementById('btnPlant' + p.id);
      const card = document.getElementById('cardPlant' + p.id);
      const priceBadge = document.getElementById('priceBadge' + p.id);
      const levelLabel = document.getElementById('shopLevel' + p.id);
      const percentLabel = document.getElementById('shopPercent' + p.id);
      const levelFill = document.getElementById('shopLevelFill' + p.id);

      const pData = plantLevels[p.key] || { level: 1, xp: 0 };
      const req = getXpRequired(pData.level);
      const pct = pData.level >= NIVEL_MAXIMO ? 100 : (req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0);

      if (levelLabel) levelLabel.innerText = pData.level >= NIVEL_MAXIMO ? `Nv. ${NIVEL_MAXIMO} MAX` : `Nv. ${pData.level}`;
      if (percentLabel) percentLabel.innerText = `${pct}%`;
      if (levelFill) levelFill.style.width = `${pct}%`;

      if (!btn || !card) return;

      const isUnlocked = zenInventory.unlockedPlants.includes(p.key);
      const isActive = zenInventory.activePlant === p.key;

      if (isActive) {
        btn.className = 'pvz-shop-action-btn active-equipped';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>En el Invernadero</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else if (isUnlocked) {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = '<i class="fa-solid fa-seedling"></i> <span>Colocar en Jardín</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = `<i class="fa-solid fa-lock"></i> <span>Desbloquear</span>`;
        card.classList.remove('owned');
        if (priceBadge) priceBadge.style.display = 'inline-flex';
      }
    });

    const pots = [
      { id: 'Terracotta', key: 'terracotta', cost: 0 },
      { id: 'Jade', key: 'jade', cost: 80 },
      { id: 'Kintsugi', key: 'kintsugi', cost: 160 },
      { id: 'Marble', key: 'marble', cost: 240 },
      { id: 'Obsidian', key: 'obsidian', cost: 360 },
      { id: 'Wood', key: 'wood', cost: 480 }
    ];

    pots.forEach(pot => {
      const btn = document.getElementById('btnPot' + pot.id);
      const card = document.getElementById('cardPot' + pot.id);
      const priceBadge = document.getElementById('priceBadge' + pot.id);
      if (!btn || !card) return;

      const isUnlocked = zenInventory.unlockedPots.includes(pot.key);
      const isActive = zenInventory.activePot === pot.key;

      if (isActive) {
        btn.className = 'pvz-shop-action-btn active-equipped';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Equipada</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else if (isUnlocked) {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = '<i class="fa-solid fa-palette"></i> <span>Equipar</span>';
        card.classList.add('owned');
        if (priceBadge) priceBadge.style.display = 'none';
      } else {
        btn.className = 'pvz-shop-action-btn';
        btn.innerHTML = `<i class="fa-solid fa-lock"></i> <span>Desbloquear</span>`;
        card.classList.remove('owned');
        if (priceBadge) priceBadge.style.display = 'inline-flex';
      }
    });
  }

  function updateCalmDisplays() {
    const totalElem = document.getElementById('pvzTotalCalmPoints');
    const shopBal = document.getElementById('shopCalmBalance');
    const dailyCompletedElem = document.getElementById('pvzDailyTasksCompleted');

    if (totalElem) totalElem.innerText = totalCalmPoints;
    if (shopBal) shopBal.innerText = totalCalmPoints;
    if (dailyCompletedElem) dailyCompletedElem.innerText = dailyTasksCompleted;
  }

  function createWaterFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 10; i++) {
      const drop = document.createElement('div');
      drop.style.position = 'absolute';
      drop.style.left = (42 + Math.random() * 16) + '%';
      drop.style.top = (25 + Math.random() * 15) + '%';
      drop.style.color = '#5DADE2';
      drop.style.fontSize = '0.95rem';
      drop.style.pointerEvents = 'none';
      drop.style.transition = 'all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1)';
      drop.innerHTML = '<i class="fa-solid fa-droplet"></i>';
      fxLayer.appendChild(drop);

      setTimeout(() => {
        drop.style.transform = `translateY(${90 + Math.random() * 30}px) scale(0.6)`;
        drop.style.opacity = '0';
      }, 25);
      setTimeout(() => drop.remove(), 650);
    }
  }

  function createFertilizerFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 8; i++) {
      const sparkle = document.createElement('div');
      sparkle.style.position = 'absolute';
      sparkle.style.left = (38 + Math.random() * 24) + '%';
      sparkle.style.top = (30 + Math.random() * 30) + '%';
      sparkle.style.color = '#58D68D';
      sparkle.style.fontSize = '1.05rem';
      sparkle.style.pointerEvents = 'none';
      sparkle.style.transition = 'all 0.8s ease-out';
      sparkle.innerHTML = '<i class="fa-solid fa-star"></i>';
      fxLayer.appendChild(sparkle);

      setTimeout(() => {
        sparkle.style.transform = `translateY(-25px) rotate(${Math.random() * 180}deg) scale(1.3)`;
        sparkle.style.opacity = '0';
      }, 35);
      setTimeout(() => sparkle.remove(), 850);
    }
  }

  function createMusicFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    const notes = ['fa-music', 'fa-compact-disc', 'fa-music'];
    for (let i = 0; i < 5; i++) {
      const note = document.createElement('div');
      note.style.position = 'absolute';
      note.style.left = (38 + Math.random() * 24) + '%';
      note.style.top = (40 + Math.random() * 20) + '%';
      note.style.color = '#AF7AC5';
      note.style.fontSize = '1.15rem';
      note.style.pointerEvents = 'none';
      note.style.transition = 'all 1.1s ease-out';
      note.innerHTML = `<i class="fa-solid ${notes[i % notes.length]}"></i>`;
      fxLayer.appendChild(note);

      setTimeout(() => {
        note.style.transform = `translate(${Math.random() * 50 - 25}px, -75px) scale(1.2)`;
        note.style.opacity = '0';
      }, 40);
      setTimeout(() => note.remove(), 1200);
    }
  }

  function createSprayFx() {
    const fxLayer = document.getElementById('pvzFxLayer');
    if (!fxLayer) return;

    for (let i = 0; i < 7; i++) {
      const mist = document.createElement('div');
      mist.style.position = 'absolute';
      mist.style.left = (40 + Math.random() * 20) + '%';
      mist.style.top = (35 + Math.random() * 25) + '%';
      mist.style.color = '#48C9B0';
      mist.style.fontSize = '1.25rem';
      mist.style.pointerEvents = 'none';
      mist.style.transition = 'all 0.85s ease-out';
      mist.innerHTML = '<i class="fa-solid fa-wind"></i>';
      fxLayer.appendChild(mist);

      setTimeout(() => {
        mist.style.transform = `scale(1.6) translateY(-35px)`;
        mist.style.opacity = '0';
      }, 35);
      setTimeout(() => mist.remove(), 950);
    }
  }

  function createSunFx() {
    const glow = document.getElementById('sunGlow');
    if (!glow) return;
    glow.style.transform = 'translateX(-50%) scale(1.6)';
    setTimeout(() => { glow.style.transform = 'translateX(-50%) scale(1)'; }, 750);
  }

  function spawnInitialSuns() {
    setTimeout(() => spawnDroppingSun(true), 400);
  }

  function spawnDroppingSun(isBonus = false) {
    const container = document.getElementById('pvzCollectiblesLayer');
    if (!container) return;

    const count = isBonus ? 2 : 1;
    for (let c = 0; c < count; c++) {
      setTimeout(() => {
        const item = document.createElement('div');
        item.className = 'pvz-collectible-item';

        const isGem = Math.random() > 0.6;
        item.innerHTML = `<i class="fa-solid ${isGem ? 'fa-gem' : 'fa-sun'}"></i>`;

        const posX = 28 + Math.random() * 44;
        const posY = 52 + Math.random() * 22;
        item.style.left = posX + '%';
        item.style.top = posY + '%';

        item.onclick = function(e) {
          e.stopPropagation();
          collectSunItem(this, isGem ? 3 : 2);
        };

        container.appendChild(item);
      }, c * 250);
    }
  }

  function startSkySuns() {
    if (skySunInterval) clearInterval(skySunInterval);
    skySunInterval = setInterval(() => {
      spawnSkySun();
    }, 6500);
  }

  function spawnSkySun() {
    const container = document.getElementById('pvzCollectiblesLayer');
    if (!container) return;

    const skySun = document.createElement('div');
    skySun.className = 'pvz-sky-sun';
    skySun.innerHTML = '<i class="fa-solid fa-sun"></i>';
    skySun.style.left = (15 + Math.random() * 70) + '%';

    skySun.onclick = function(e) {
      e.stopPropagation();
      collectSunItem(this, 2);
    };

    container.appendChild(skySun);

    setTimeout(() => {
      if (skySun.parentElement) skySun.remove();
    }, 9000);
  }

  function collectSunItem(elem, points = 2) {
    playCollectChime();
    spawnScorePopup(`<i class="fa-solid fa-plus"></i>${points} Soles (Jardín)`, elem.style.left, elem.style.top);

    totalCalmPoints += points;
    saveZenEconomy();
    updateCalmDisplays();

    const activeKey = zenInventory.activePlant || 'tree';
    addPlantXp(activeKey, 8, false);

    const bankWrap = document.getElementById('calmBankDisplay');
    if (bankWrap) {
      bankWrap.style.transform = 'scale(1.15)';
      setTimeout(() => { bankWrap.style.transform = 'scale(1)'; }, 200);
    }

    elem.style.transition = 'all 0.45s cubic-bezier(0.2, 0.8, 0.2, 1)';
    elem.style.transform = 'scale(1.3) translateY(-120px)';
    elem.style.opacity = '0';
    setTimeout(() => elem.remove(), 500);
  }

  // Auto-open game check
  // Tras navegar sin recargar (main.js), el tablero trae vistas previas nuevas.
  document.addEventListener('spa:navegado', () => {
    try { pintarVistasPrevias(); actualizarPildoraNivel(); } catch(e) {}
  });

  document.addEventListener('DOMContentLoaded', () => {
    // Nivel real del árbol en la píldora superior y en las vistas previas, en cualquier página.
    try { actualizarPildoraNivel(); pintarVistasPrevias(); } catch(e) {}

    try {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('open_game') === '1' || window.location.hash === '#tree-game') {
        setTimeout(() => {
          if (typeof window.openTreeGame === 'function') {
            window.openTreeGame();
            try {
              window.history.replaceState({}, document.title, window.location.pathname);
            } catch(e) {}
          }
        }, 300);
      }
    } catch(e) {}
  });

})(window);
