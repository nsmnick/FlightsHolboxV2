// Paints the actual bottom border of the hero image as a moving wave,
// rather than decoration sitting near a still-straight edge: each frame,
// simplex noise generates a rippling top edge for a filled shape, coloured
// to match the page background, so the hard rectangular corner of the hero
// image is continuously replaced by a soft, animated wavy line.
import { createNoise3D } from "simplex-noise";

// Two overlapping layers (fainter one behind, solid one in front) each with
// their own noise offset so they ripple out of phase instead of in lockstep.
const LAYERS = [
  { fill: "rgba(255, 255, 255, 0.55)", noiseOffset: 0, baseline: 0.55, amplitude: 0.35 },
  { fill: "rgba(255, 255, 255, 1)", noiseOffset: 5, baseline: 0.65, amplitude: 0.3 },
];

const NOISE_SPEED = 0.0015;
const NOISE_X_SCALE = 700;
const STEP = 8;

function initWave(canvas) {
  const ctx = canvas.getContext("2d");
  const noise = createNoise3D();
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  let width = 0;
  let height = 0;
  let time = 0;

  const resize = () => {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    width = rect.width;
    height = rect.height;
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  };

  const drawFrame = () => {
    ctx.clearRect(0, 0, width, height);

    LAYERS.forEach(({ fill, noiseOffset, baseline, amplitude }) => {
      ctx.beginPath();
      ctx.moveTo(0, height);

      for (let x = 0; x <= width; x += STEP) {
        const y = height * baseline + noise(x / NOISE_X_SCALE, noiseOffset, time) * (height * amplitude);
        ctx.lineTo(x, y);
      }

      ctx.lineTo(width, height);
      ctx.closePath();
      ctx.fillStyle = fill;
      ctx.fill();
    });
  };

  const render = () => {
    time += NOISE_SPEED;
    drawFrame();
    requestAnimationFrame(render);
  };

  resize();
  drawFrame();

  if (!prefersReducedMotion) {
    requestAnimationFrame(render);
  }

  window.addEventListener(
    "resize",
    () => {
      resize();
      drawFrame();
    },
    { passive: true },
  );
}

export default function initHeroWave() {
  document.querySelectorAll(".hero-panel__wave").forEach(initWave);
}
