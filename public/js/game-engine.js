/**
 * Plants vs Zombies Style Zen Garden & Tree Game Engine
 * A Tu Lado - Emotional Wellness & Gamification Engine
 */

(function(window) {
  'use strict';

  let soundEnabled = true;
  let audioCtx = null;
  let activeTool = 'water';
  let totalCalmPoints = 125;
  let dailyTasksCompleted = 0;
  const DAILY_TASKS_MAX = 5;
  let currentThought = 'water';
  let skySunInterval = null;

  // Load Economy from LocalStorage
  try {
    const savedTotal = localStorage.getItem('atulado_zen_total_calm');
    if (savedTotal !== null) {
      totalCalmPoints = parseInt(savedTotal, 10) || 0;
    }
  } catch(e) {}

  try {
    const todayStr = new Date().toISOString().slice(0, 10);
    const storedDate = localStorage.getItem('atulado_zen_daily_date');
    if (storedDate === todayStr) {
      dailyTasksCompleted = parseInt(localStorage.getItem('atulado_zen_daily_tasks'), 10) || 0;
    } else {
      dailyTasksCompleted = 0;
      localStorage.setItem('atulado_zen_daily_date', todayStr);
      localStorage.setItem('atulado_zen_daily_tasks', '0');
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
    const savedLevels = localStorage.getItem('atulado_zen_plant_levels');
    if (savedLevels) {
      const parsed = JSON.parse(savedLevels);
      Object.keys(plantLevels).forEach(k => {
        if (parsed[k]) {
          plantLevels[k] = {
            level: Math.max(1, Math.min(100, parseInt(parsed[k].level, 10) || 1)),
            xp: Math.max(0, parseInt(parsed[k].xp, 10) || 0)
          };
        }
      });
    }
  } catch(e) {}

  function saveZenPlantLevels() {
    try {
      localStorage.setItem('atulado_zen_plant_levels', JSON.stringify(plantLevels));
    } catch(e) {}
  }

  function getXpRequired(level) {
    if (level >= 100) return 0;
    const base = 40;
    const growth = Math.pow(1 + (level - 1) * 0.082, 2.25);
    return Math.round(base * growth + (level * 10));
  }

  function addPlantXp(plantKey, amount, showPopup = false) {
    if (!plantLevels[plantKey]) {
      plantLevels[plantKey] = { level: 1, xp: 0 };
    }

    const p = plantLevels[plantKey];
    if (p.level >= 100) {
      p.level = 100;
      p.xp = 0;
      saveZenPlantLevels();
      updatePlantHudDisplay();
      updateShopUI();
      return;
    }

    p.xp += amount;
    let req = getXpRequired(p.level);
    let leveledUp = false;

    while (p.xp >= req && p.level < 100) {
      p.xp -= req;
      p.level += 1;
      leveledUp = true;
      req = getXpRequired(p.level);
    }

    if (p.level >= 100) {
      p.level = 100;
      p.xp = 0;
    }

    saveZenPlantLevels();
    updatePlantHudDisplay();
    updateShopUI();

    if (showPopup && amount > 0) {
      spawnScorePopup(`<iconify-icon icon="game-icons:ground-sprout" style="color: #5AB56E; vertical-align: middle;"></iconify-icon> +${amount} XP (${SPECIES_NAMES[plantKey] || 'Planta'})`);
    }

    if (leveledUp) {
      triggerLevelUpCelebration(plantKey, p.level);
    }
  }

  function triggerLevelUpCelebration(plantKey, newLevel) {
    playMusicChord();
    setTimeout(playCollectChime, 250);
    createSunFx();
    createFertilizerFx();

    const plantName = SPECIES_NAMES[plantKey] || 'Tu árbol';
    if (newLevel >= 100) {
      window.showZenNotification(
        "¡Nivel 100 Máximo!",
        `¡Felicidades! ${plantName} ha alcanzado el nivel 100 máximo tras tu constancia y cuidado diario. Has cultivado la máxima serenidad y armonía.`,
        "game-icons:laurel-crown",
        "#F1C40F"
      );
    } else {
      window.showZenNotification(
        `¡${plantName} Subió a Nivel ${newLevel}!`,
        `Tu constancia da frutos. Cada nivel requiere más cuidado y dedicación. Sigue cuidando tu árbol para llevarlo al Nivel 100.`,
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
      if (pData.level >= 100) {
        levelTag.classList.add('max-level');
        levelTag.innerHTML = '<i class="fa-solid fa-crown"></i> Nv. <strong>100 MAX</strong>';
      } else {
        levelTag.classList.remove('max-level');
        levelTag.innerHTML = `Nv. <strong id="pvzCurrentTreeLevel">${pData.level}</strong>`;
      }
    }

    let percent = 0;
    if (pData.level >= 100) {
      percent = 100;
    } else {
      const req = getXpRequired(pData.level);
      percent = req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0;
    }

    if (fillElem) fillElem.style.width = percent + '%';
    if (textElem) textElem.innerText = percent + '%';
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
    const saved = localStorage.getItem('atulado_zen_inventory');
    if (saved) {
      const parsed = JSON.parse(saved);
      zenInventory = Object.assign(zenInventory, parsed);
    }
  } catch(e) {}

  function saveZenInventory() {
    try {
      localStorage.setItem('atulado_zen_inventory', JSON.stringify(zenInventory));
    } catch(e) {}
  }

  function saveZenEconomy() {
    try {
      localStorage.setItem('atulado_zen_total_calm', totalCalmPoints.toString());
      localStorage.setItem('atulado_zen_daily_tasks', dailyTasksCompleted.toString());
      localStorage.setItem('atulado_zen_daily_date', new Date().toISOString().slice(0, 10));
    } catch(e) {}
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
      if (plantNameDisplay) plantNameDisplay.innerText = 'Árbol Sabiduría';
      svgHtml = `
        <svg id="interactiveTreeSvg" class="ptree" viewBox="0 0 32 32" width="130" height="130" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 12px 24px rgba(0,0,0,0.65)); margin-bottom: -5px; z-index: 5;">
          <rect id="treeTrunk" x="14" y="18" width="4" height="10" fill="#6B3A1F"/>
          <rect x="13" y="21" width="1" height="6" fill="#4A2710"/>
          <rect x="17" y="22" width="1" height="5" fill="#4A2710"/>
          <rect id="foliage1" x="7" y="6" width="18" height="14" fill="#2D6B3A"/>
          <rect id="foliage2" x="5" y="8" width="22" height="12" fill="#2D6B3A"/>
          <rect id="foliage3" x="8" y="7" width="16" height="12" fill="#3D8C4F"/>
          <rect id="foliage4" x="10" y="5" width="12" height="14" fill="#5AB56E"/>
          <rect id="foliage5" x="12" y="4" width="8" height="3" fill="#3D8C4F"/>
          <rect id="foliage6" x="11" y="7" width="5" height="3" fill="#7FD68A"/>
          <rect id="apple1" x="9" y="12" width="2" height="2" fill="#C0392B"/>
          <rect id="apple2" x="20" y="11" width="2" height="2" fill="#C0392B"/>
          <rect id="apple3" x="15" y="15" width="2" height="2" fill="#C0392B"/>
        </svg>
      `;
    }

    container.innerHTML = svgHtml;
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
      const pct = pData.level >= 100 ? 100 : (req > 0 ? Math.min(99, Math.floor((pData.xp / req) * 100)) : 0);

      if (levelLabel) levelLabel.innerText = pData.level >= 100 ? 'Nv. 100 MAX' : `Nv. ${pData.level}`;
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
  document.addEventListener('DOMContentLoaded', () => {
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
