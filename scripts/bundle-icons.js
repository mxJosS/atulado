import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import gameIconsJson from '@iconify-json/game-icons/icons.json' with { type: 'json' };

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const neededKeys = [
  'watering-can', 'plant-watering', 'water-drop', 'droplet-splash',
  'sun', 'sunbeams', 'sun-radiations',
  'spray', 'aerosol', 'delicate-perfume', 'bottle-vapors',
  'music-spell', 'musical-notes', 'love-song',
  'fertilizer-bag', 'bubbling-flask', 'round-potion', 'magic-potion', 'fizzing-flask',
  'ground-sprout', 'sprout', 'flower-pot', 'bonsai-tree', 'lotus-flower', 'sunflower', 'cactus', 'bamboo',
  'party-popper', 'trophy-cup', 'laurel-crown', 'crown', 'sparkles', 'firework-rocket',
  'shield', 'shield-reflect', 'fire-silhouette', 'burning-passion', 'meditation', 'heart-bottle'
];

const collection = {
  prefix: 'game-icons',
  icons: {},
  width: gameIconsJson.width || 512,
  height: gameIconsJson.height || 512
};

neededKeys.forEach(k => {
  if (gameIconsJson.icons[k]) {
    collection.icons[k] = gameIconsJson.icons[k];
  }
});

const content = `/**
 * A tu lado — Standalone Game Icons Collection Pack
 * Powered by Game-Icons.net & Iconify
 * Embedded offline for zero-latency instant rendering
 */
(function() {
  var gameIconsData = ${JSON.stringify(collection, null, 2)};

  function registerIcons() {
    if (typeof Iconify !== 'undefined' && Iconify.addCollection) {
      try {
        Iconify.addCollection(gameIconsData);
      } catch(e) {}
    }
    if (typeof window !== 'undefined') {
      if (window.IconifyPreload) {
        window.IconifyPreload.push(gameIconsData);
      } else {
        window.IconifyPreload = [gameIconsData];
      }
    }
  }

  // Standalone SVG generator helper
  window.getGameIconSvg = function(iconName, options) {
    options = options || {};
    var size = options.size || 24;
    var color = options.color || 'currentColor';
    var extraClass = options.class || '';
    var name = iconName.replace(/^game-icons:/, '');
    var iconData = gameIconsData.icons[name];
    if (!iconData) {
      return '<i class="fa-solid fa-gamepad"></i>';
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 512 512" class="game-icon-svg ' + extraClass + '" style="display: inline-block; vertical-align: middle; fill: ' + color + '; width: ' + size + 'px; height: ' + size + 'px;">' + iconData.body + '</svg>';
  };

  registerIcons();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerIcons);
  }
})();
`;

const targetDir = path.join(__dirname, '..', 'public', 'js');
if (!fs.existsSync(targetDir)) {
  fs.mkdirSync(targetDir, { recursive: true });
}

fs.writeFileSync(path.join(targetDir, 'game-icons-pack.js'), content, 'utf8');
console.log('Successfully generated public/js/game-icons-pack.js with ' + Object.keys(collection.icons).length + ' game icons!');
