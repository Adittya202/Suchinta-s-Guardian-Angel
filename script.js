/**
 * Suchinta's Guardian Angel - Core Logic
 * 
 * Features:
 * - Live synchronization with MySQL database via api.php
 * - Automatic background reload when window regains focus (real-time sync with Admin Dashboard)
 * - Pure random advice selection from active database records
 * - Non-consecutive check (never repeats the exact same advice twice in a row)
 * - Clean cloud display: only the advice message itself
 * - Micro-animations: angel bounce, bright warm sparkle burst, smooth text fade
 * - Native Web Audio API celestial chime synthesizer
 * - Clipboard copy with toast notification
 */

// Fallback caring advice dataset (used only if server is unreachable)
const FALLBACK_ADVICES = [
  { id: 1, content: "Remember to take a sip of water and unclench your jaw right now. Yes, I see you! Stay hydrated, champion." },
  { id: 2, content: "Take a deep breath and give yourself some credit. You are handling so much right now and doing it with real grace." },
  { id: 3, content: "Don't let a tiny bump spoil your entire day. Step back, stretch, and tackle things just one small piece at a time." },
  { id: 4, content: "It is 100% okay to hit pause and recharge. Even legendary superheroes take naps, and you deserve your cozy downtime." },
  { id: 5, content: "Trust your gut and your abilities today. You've got sharp brains, a kind heart, and the grit to figure anything out." },
  { id: 6, content: "Quick reminder: perfection is wildly overrated. Being real, kind, and patient with yourself is what truly counts." },
  { id: 7, content: "If today feels a little heavy, remember that tough days only make the upcoming victories sweeter. You've got this!" },
  { id: 8, content: "Make time to eat something delicious and nourishing today. Good food fuels good vibes, and your energy is precious." },
  { id: 9, content: "Look at how far you've already come! Never look back to doubt your worth—only to celebrate your progress." },
  { id: 10, content: "Sending you an extra boost of guardian angel positive energy today. Go out there and just be your awesome self!" }
];

// Fallback Pro Advice paragraph (used only if server is unreachable)
const FALLBACK_PRO_ADVICES = [
  {
    id: 1,
    content: "Dearest Suchinta, whenever the world feels overwhelming or the noise around you gets too loud, take a moment to step back and remember who you are. You don't have to carry the weight of everything all at once. Every challenge you face is just a stepping stone, and your kindness, strength, and brilliance will guide you through any storm. Give yourself patience, drink warm water, take a deep breath, and know that you are deeply cherished, respected, and protected always."
  }
];

// App State
let allAdvices = [];
let allProAdvices = [];
let currentAdvice = null;
let currentProAdvice = null;
let currentDisplayedType = 'normal'; // 'normal' | 'pro'
let isAnimating = false;
let soundEnabled = true;

// DOM Elements
const quoteContent = document.getElementById('quoteContent');
const adviceText = document.getElementById('adviceText');
const angelButton = document.getElementById('angelButton');
const copyAdviceBtn = document.getElementById('copyAdviceBtn');
const soundToggleBtn = document.getElementById('soundToggleBtn');
const soundIcon = document.getElementById('soundIcon');
const sparkleBurst = document.getElementById('sparkleBurst');
const toastEl = document.getElementById('toast');
const adminGateLink = document.getElementById('adminGateLink');

// Pro Advice Modal DOM Elements
const proAdviceModal = document.getElementById('proAdviceModal');
const proModalContent = document.getElementById('proModalContent');
const closeProModalBtn = document.getElementById('closeProModalBtn');
const closeProModalFooterBtn = document.getElementById('closeProModalFooterBtn');
const copyProAdviceBtn = document.getElementById('copyProAdviceBtn');

/**
 * Determine API URL dynamically with timestamp cache-buster
 */
function getApiUrl() {
  const isWebProtocol = window.location.protocol === 'http:' || window.location.protocol === 'https:';
  const base = isWebProtocol ? 'api.php' : 'http://localhost/suchintas-guardian-angel/api.php';
  return `${base}?action=get_all&_t=${Date.now()}`;
}

/**
 * Pick an advice purely at random from allAdvices,
 * ensuring the exact same advice is never chosen twice consecutively.
 */
function getRandomAdvice() {
  if (allAdvices.length === 0) return null;
  if (allAdvices.length === 1) return allAdvices[0];

  let selected;
  do {
    const randomIndex = Math.floor(Math.random() * allAdvices.length);
    selected = allAdvices[randomIndex];
  } while (currentAdvice && selected.id === currentAdvice.id);

  return selected;
}

/**
 * Pick a pro advice purely at random from allProAdvices,
 * ensuring the exact same pro advice is never chosen twice consecutively.
 */
function getRandomProAdvice() {
  if (allProAdvices.length === 0) return null;
  if (allProAdvices.length === 1) return allProAdvices[0];

  let selected;
  do {
    const randomIndex = Math.floor(Math.random() * allProAdvices.length);
    selected = allProAdvices[randomIndex];
  } while (currentProAdvice && selected.id === currentProAdvice.id);

  return selected;
}

/**
 * Roll for advice type:
 * - 70% chance: Normal Advice
 * - 30% chance: Pro Advice
 */
function rollAdviceType() {
  // If pro advices exist and 30% probability hits
  if (allProAdvices.length > 0 && Math.random() < 0.30) {
    return 'pro';
  }
  // Otherwise if normal advices exist, return normal (70% probability)
  if (allAdvices.length > 0) {
    return 'normal';
  }
  // If no normal exist but pro exists, return pro
  if (allProAdvices.length > 0) {
    return 'pro';
  }
  return 'normal';
}

/**
 * Fetch both normal and pro advice lists live from database
 */
async function loadAdvices(initialDisplay = true) {
  try {
    const response = await fetch(getApiUrl(), { cache: 'no-store' });
    if (!response.ok) throw new Error(`HTTP error ${response.status}`);
    const data = await response.json();

    if (data && typeof data === 'object') {
      if (Array.isArray(data)) {
        allAdvices = data;
        allProAdvices = FALLBACK_PRO_ADVICES;
      } else {
        allAdvices = Array.isArray(data.normal) && data.normal.length > 0 ? data.normal : [];
        allProAdvices = Array.isArray(data.pro) && data.pro.length > 0 ? data.pro : [];
      }
    }
  } catch (error) {
    console.info('Database API notice:', error.message);
  }

  if (allAdvices.length === 0) {
    allAdvices = FALLBACK_ADVICES;
  }
  if (allProAdvices.length === 0) {
    allProAdvices = FALLBACK_PRO_ADVICES;
  }

  // Display initial random advice if starting up
  if (initialDisplay) {
    displayNextRandomAdvice(false);
  }
}

/**
 * Displays a new random advice with smooth fade transition
 * - 70% chance normal advice
 * - 30% chance pro advice
 */
function displayNextRandomAdvice(animateAngel = true) {
  if (isAnimating) return;
  if (allAdvices.length === 0 && allProAdvices.length === 0) return;
  isAnimating = true;

  const type = rollAdviceType();

  if (type === 'pro') {
    const nextPro = getRandomProAdvice();
    if (!nextPro) {
      isAnimating = false;
      return;
    }
    currentProAdvice = nextPro;
    currentDisplayedType = 'pro';
  } else {
    const nextAdvice = getRandomAdvice();
    if (!nextAdvice) {
      isAnimating = false;
      return;
    }
    currentAdvice = nextAdvice;
    currentDisplayedType = 'normal';
  }

  // Trigger animations & audio
  if (animateAngel) {
    triggerAngelBounce();
    createSparkleBurst();
    playCelestialChime();
  }

  // Smooth fade-out -> update text -> smooth fade-in
  adviceText.classList.remove('fade-in');
  adviceText.classList.add('fade-out');

  setTimeout(() => {
    if (currentDisplayedType === 'pro') {
      if (quoteContent) quoteContent.classList.add('is-pro');
      // Pro advice pop-in: displays only the clickable "Pro Advice" writing
      adviceText.innerHTML = `
        <div class="pro-advice-writing" id="proAdviceWriting" role="button" tabindex="0" title="Click to read your special Pro Advice">
          <div class="pro-writing-title-row">
            <span>✨</span>
            <span>Super Chat</span>
            <span>✨</span>
          </div>
          <div class="pro-writing-hint">
            <span>Tap to open message</span>
          </div>
        </div>
      `;

      const proBtn = document.getElementById('proAdviceWriting');
      if (proBtn) {
        proBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          openProAdviceModal();
        });
        proBtn.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            e.stopPropagation();
            openProAdviceModal();
          }
        });
      }
    } else {
      if (quoteContent) quoteContent.classList.remove('is-pro');
      // Normal advice text
      adviceText.textContent = currentAdvice.content;
    }

    adviceText.classList.remove('fade-out');
    adviceText.classList.add('fade-in');

    setTimeout(() => {
      adviceText.classList.remove('fade-in');
      isAnimating = false;
    }, 280);
  }, 200);
}

/**
 * Visual elastic bounce on angel character
 */
function triggerAngelBounce() {
  angelButton.classList.remove('bounce');
  void angelButton.offsetWidth; // Force DOM reflow
  angelButton.classList.add('bounce');
}

/**
 * Bright, warm sunlight sparkle particles
 */
function createSparkleBurst() {
  sparkleBurst.innerHTML = '';
  const colors = ['#f59e0b', '#fbbf24', '#f43f5e', '#ec4899', '#ffffff', '#fed7aa'];
  const particleCount = 14;

  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';

    const angle = (i / particleCount) * (Math.PI * 2) + (Math.random() * 0.4);
    const distance = 45 + Math.random() * 55;
    const tx = Math.cos(angle) * distance;
    const ty = Math.sin(angle) * distance;
    const color = colors[Math.floor(Math.random() * colors.length)];
    const size = 5 + Math.random() * 6;

    particle.style.setProperty('--tx', `${tx}px`);
    particle.style.setProperty('--ty', `${ty}px`);
    particle.style.background = color;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    particle.style.boxShadow = `0 0 6px ${color}`;

    sparkleBurst.appendChild(particle);
  }

  setTimeout(() => {
    sparkleBurst.innerHTML = '';
  }, 800);
}

/**
 * Soft harmonic chime synthesizer (Web Audio API)
 */
function playCelestialChime() {
  if (!soundEnabled) return;

  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;

    const ctx = new AudioContext();
    if (ctx.state === 'suspended') {
      ctx.resume();
    }

    // Gentle harmonic pentatonic frequencies (E5, G#5, B5, E6)
    const notes = [659.25, 830.61, 987.77, 1318.51];
    const baseFreq = notes[Math.floor(Math.random() * notes.length)];

    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = 'sine';
    osc.frequency.setValueAtTime(baseFreq, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(baseFreq * 1.5, ctx.currentTime + 0.3);

    gain.gain.setValueAtTime(0.001, ctx.currentTime);
    gain.gain.linearRampToValueAtTime(0.07, ctx.currentTime + 0.04);
    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.65);

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.7);
  } catch (err) {
    // Audio context may require user gesture on certain browsers
  }
}

/**
 * Temporary floating toast notification
 */
function showToast(message) {
  toastEl.textContent = message;
  toastEl.classList.add('show');
  setTimeout(() => {
    toastEl.classList.remove('show');
  }, 2200);
}

/**
 * Open the Pro Advice Modal / Message Box with the full paragraph
 */
function openProAdviceModal() {
  if (!currentProAdvice || !proAdviceModal || !proModalContent) return;
  proModalContent.textContent = currentProAdvice.content;
  proAdviceModal.classList.add('is-open');
  proAdviceModal.setAttribute('aria-hidden', 'false');
  playCelestialChime();
}

/**
 * Close the Pro Advice Modal
 */
function closeProAdviceModal() {
  if (!proAdviceModal) return;
  proAdviceModal.classList.remove('is-open');
  proAdviceModal.setAttribute('aria-hidden', 'true');
}

/**
 * Copy advice text to clipboard (works for both normal and pro advice)
 */
async function copyCurrentAdvice() {
  const textToCopy = currentDisplayedType === 'pro' && currentProAdvice
    ? currentProAdvice.content
    : (currentAdvice ? currentAdvice.content : null);

  if (!textToCopy) return;

  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(textToCopy);
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = textToCopy;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    }
    showToast(currentDisplayedType === 'pro' ? "🌟 Pro Advice copied! 📋" : "✨ Advice copied! ✨");
  } catch (err) {
    showToast("Unable to copy advice");
  }
}

// Pro Advice Modal Event Listeners
if (closeProModalBtn) {
  closeProModalBtn.addEventListener('click', closeProAdviceModal);
}
if (closeProModalFooterBtn) {
  closeProModalFooterBtn.addEventListener('click', closeProAdviceModal);
}
if (proAdviceModal) {
  // Close when clicking on backdrop outside the card
  proAdviceModal.addEventListener('click', (e) => {
    if (e.target === proAdviceModal) {
      closeProAdviceModal();
    }
  });
}

// Escape key to dismiss Pro Advice Modal
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && proAdviceModal && proAdviceModal.classList.contains('is-open')) {
    closeProAdviceModal();
  }
});

// Copy Pro Advice from inside the modal
if (copyProAdviceBtn) {
  copyProAdviceBtn.addEventListener('click', async () => {
    if (!currentProAdvice) return;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(currentProAdvice.content);
      } else {
        const textarea = document.createElement('textarea');
        textarea.value = currentProAdvice.content;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
      }
      showToast("🌟 Pro Advice copied! 📋");
    } catch (err) {
      showToast("Unable to copy advice");
    }
  });
}

const cornerSun = document.getElementById('cornerSun');
if (cornerSun) {
  cornerSun.addEventListener('click', () => {
    playCelestialChime();
    showToast("☀️ Sending you warm sunshine and love, Suchinta! ✨");
  });
}

// Event Listeners
angelButton.addEventListener('click', () => displayNextRandomAdvice(true));
copyAdviceBtn.addEventListener('click', copyCurrentAdvice);

soundToggleBtn.addEventListener('click', () => {
  soundEnabled = !soundEnabled;
  soundIcon.textContent = soundEnabled ? '🔔' : '🔕';
  soundToggleBtn.title = soundEnabled ? 'Sound enabled' : 'Sound muted';
  showToast(soundEnabled ? "Sound enabled 🔔" : "Sound muted 🔕");
});

// Keyboard Accessibility (Space or Enter on Angel)
angelButton.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    displayNextRandomAdvice(true);
  }
});

// Automatically sync latest advices from database whenever window regains focus
window.addEventListener('focus', () => {
  loadAdvices(false);
});

// Ensure admin link works even if opened via file:// protocol
if (adminGateLink && window.location.protocol === 'file:') {
  adminGateLink.href = 'http://localhost/suchintas-guardian-angel/admin.php';
}

// ==========================================================
// BACKGROUND ANIMATED WILDLIFE (FLYING BIRDS, RUNNING CATS & DOGS)
// ==========================================================

const wildlifeStage = document.getElementById('wildlifeStage');

function getBirdSvg() {
  return `
    <div class="bird-entity">
      <svg viewBox="0 0 52 36" width="44" height="30" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="birdGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="100%" stop-color="#fef08a"/>
          </linearGradient>
        </defs>
        <path d="M 6 18 L 0 12 L 2 22 Z" fill="#fef3c7"/>
        <g class="bird-wing-anim">
          <path d="M 20 16 C 18 4, 30 2, 35 8 C 30 14, 24 16, 20 16 Z" fill="#ffffff" stroke="#fde047" stroke-width="0.8"/>
        </g>
        <ellipse cx="24" cy="18" rx="14" ry="10" fill="url(#birdGrad)"/>
        <circle cx="34" cy="14" r="8" fill="#ffffff"/>
        <circle cx="36" cy="13" r="1.5" fill="#1e293b"/>
        <ellipse cx="33" cy="16" rx="2" ry="1.2" fill="#f43f5e" opacity="0.45"/>
        <polygon points="41,13 48,15 41,17" fill="#f59e0b"/>
      </svg>
    </div>
  `;
}

function getCatSvg() {
  return `
    <div class="cat-entity">
      <svg viewBox="0 0 65 45" width="56" height="39" xmlns="http://www.w3.org/2000/svg">
        <path class="cat-tail" d="M 12 25 Q 4 14 8 8" fill="none" stroke="#fed7aa" stroke-width="4" stroke-linecap="round"/>
        <ellipse cx="32" cy="26" rx="18" ry="11" fill="#fed7aa"/>
        <ellipse cx="32" cy="27" rx="14" ry="8" fill="#ffffff"/>
        <g class="cat-legs-back">
          <path d="M 18 34 L 14 43" stroke="#fed7aa" stroke-width="3.5" stroke-linecap="round"/>
          <path d="M 24 34 L 28 43" stroke="#fbcfe8" stroke-width="3" stroke-linecap="round"/>
        </g>
        <g class="cat-legs-front">
          <path d="M 40 34 L 46 43" stroke="#fed7aa" stroke-width="3.5" stroke-linecap="round"/>
          <path d="M 45 34 L 39 43" stroke="#fbcfe8" stroke-width="3" stroke-linecap="round"/>
        </g>
        <circle cx="48" cy="18" r="10" fill="#fed7aa"/>
        <polygon points="43,10 47,3 51,10" fill="#fed7aa"/>
        <polygon points="50,10 54,3 57,11" fill="#fed7aa"/>
        <polygon points="45,9 47,5 49,9" fill="#f43f5e"/>
        <circle cx="51" cy="17" r="1.5" fill="#1e293b"/>
        <ellipse cx="46" cy="20" rx="2" ry="1.2" fill="#f43f5e" opacity="0.5"/>
        <path d="M 52 20 L 56 20 M 52 22 L 55 23" stroke="#94a3b8" stroke-width="0.8"/>
      </svg>
    </div>
  `;
}

function getDogSvg() {
  return `
    <div class="dog-entity">
      <svg viewBox="0 0 70 45" width="60" height="39" xmlns="http://www.w3.org/2000/svg">
        <path class="dog-tail" d="M 12 22 Q 4 15 6 10" fill="none" stroke="#f59e0b" stroke-width="4.5" stroke-linecap="round"/>
        <ellipse cx="34" cy="26" rx="19" ry="12" fill="#fde68a"/>
        <ellipse cx="34" cy="27" rx="14" ry="8" fill="#ffffff"/>
        <g class="dog-legs-back">
          <path d="M 20 34 L 14 43" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
          <path d="M 26 34 L 30 43" stroke="#d97706" stroke-width="3.5" stroke-linecap="round"/>
        </g>
        <g class="dog-legs-front">
          <path d="M 42 34 L 48 43" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
          <path d="M 47 34 L 41 43" stroke="#d97706" stroke-width="3.5" stroke-linecap="round"/>
        </g>
        <circle cx="50" cy="17" r="11" fill="#fde68a"/>
        <ellipse cx="57" cy="20" rx="5" ry="4" fill="#fef3c7"/>
        <circle cx="59" cy="18" r="2" fill="#78350f"/>
        <path class="dog-ear" d="M 44 12 C 40 8, 38 18, 42 22 C 45 22, 46 16, 44 12 Z" fill="#d97706"/>
        <circle cx="52" cy="15" r="1.6" fill="#1e293b"/>
        <circle cx="52.5" cy="14.5" r="0.6" fill="#ffffff"/>
        <ellipse cx="48" cy="20" rx="2" ry="1.2" fill="#f43f5e" opacity="0.45"/>
      </svg>
    </div>
  `;
}

let isGroundPetActive = false;

function spawnBird() {
  if (!wildlifeStage) return;
  // Limit concurrent birds to at most 2
  if (wildlifeStage.querySelectorAll('.bird-entity').length >= 2) return;

  const isLeftToRight = Math.random() > 0.5;
  const runner = document.createElement('div');
  runner.className = `wildlife-runner ${isLeftToRight ? 'run-ltr' : 'run-rtl'}`;
  runner.innerHTML = getBirdSvg();

  // Upper sky flight: 10% to 35% of screen height
  const topPercent = 10 + Math.random() * 25;
  runner.style.top = `${topPercent}vh`;
  // Smooth gliding duration: 13s to 19s
  const duration = 13 + Math.random() * 6;
  runner.style.animationDuration = `${duration}s`;

  runner.addEventListener('animationend', () => runner.remove());
  wildlifeStage.appendChild(runner);
}

function spawnGroundPet() {
  if (!wildlifeStage || isGroundPetActive) return;

  const isDog = Math.random() > 0.5;
  const isLeftToRight = Math.random() > 0.5;

  const runner = document.createElement('div');
  runner.className = `wildlife-runner ${isLeftToRight ? 'run-ltr' : 'run-rtl'}`;
  runner.innerHTML = isDog ? getDogSvg() : getCatSvg();

  // Run along bottom: 84% to 91% of screen height
  const bottomPercent = 84 + Math.random() * 7;
  runner.style.top = `${bottomPercent}vh`;

  // Gentle, calm trot duration: 9s to 14s
  const duration = 9 + Math.random() * 5;
  runner.style.animationDuration = `${duration}s`;

  isGroundPetActive = true;
  runner.addEventListener('animationend', () => {
    runner.remove();
    isGroundPetActive = false;
  });

  wildlifeStage.appendChild(runner);
}

function initWildlifeCycle() {
  if (!wildlifeStage) return;

  // 1. Birds: gentle flight every 12s - 22s
  setTimeout(() => spawnBird(), 2500);

  function scheduleNextBird() {
    const nextBirdDelay = 12000 + Math.random() * 10000;
    setTimeout(() => {
      spawnBird();
      scheduleNextBird();
    }, nextBirdDelay);
  }
  scheduleNextBird();

  // 2. Cats & Dogs: much less frequent, occasional surprise every 25s - 45s
  setTimeout(() => spawnGroundPet(), 8000);

  function scheduleNextGroundPet() {
    const nextPetDelay = 25000 + Math.random() * 20000;
    setTimeout(() => {
      spawnGroundPet();
      scheduleNextGroundPet();
    }, nextPetDelay);
  }
  scheduleNextGroundPet();
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  loadAdvices(true);
  initWildlifeCycle();
});
