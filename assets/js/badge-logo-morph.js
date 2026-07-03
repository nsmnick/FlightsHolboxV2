// Scroll-scrubbed logo morph: the badge starts centred over the hero and
// travels to its resting spot in the menu bar as the user scrolls, tied
// 1:1 to scroll position (same technique as the hero-name → nav-logo
// morph on jacob-morley-website — a fixed "clone" element whose
// transform is recalculated every scroll frame from the live positions
// of a real hero reference point and the real nav target element).
function lerp(a, b, t) {
  return a + (b - a) * t;
}

function easeInOut(t) {
  return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
}

// Must match $mobile-menu-snap in assets/styles/setup/_global.scss.
const MOBILE_MENU_SNAP = 1250;

export default function initBadgeLogoMorph() {
  const target = document.getElementById("badgeLogoTarget");
  const morph = document.getElementById("badgeLogoMorph");
  const morphImg = morph ? morph.querySelector(".site-badge-logo-morph__img") : null;
  const heroPanel = document.querySelector(".hero-panel");

  if (!target || !morph || !morphImg) return;

  // No hero on this page to morph from — just show the logo permanently
  // in its menu-bar spot, except on mobile where it should never sit there.
  if (!heroPanel) {
    morph.style.display = "none";

    const syncNonHeroLogo = () => {
      target.classList.toggle("is-visible", window.innerWidth >= MOBILE_MENU_SNAP);
    };

    syncNonHeroLogo();
    window.addEventListener("resize", syncNonHeroLogo, { passive: true });
    return;
  }

  let heroTopDocY = 0;
  let heroHeight = 0;
  let heroImgSize = 0;
  let navSize = 0;

  function measure() {
    const heroRect = heroPanel.getBoundingClientRect();
    heroTopDocY = heroRect.top + window.scrollY;
    heroHeight = heroPanel.offsetHeight;

    // Read sizes via computed style, not getBoundingClientRect — the morph
    // clone has a live transform applied to it, which would otherwise
    // contaminate the "natural" size we scale from.
    heroImgSize = parseFloat(getComputedStyle(morphImg).width);
    navSize = parseFloat(getComputedStyle(target).width);
  }

  function update() {
    const scrollY = window.scrollY;
    const rawP = Math.max(0, Math.min(1, scrollY / (heroHeight * 0.6)));
    const p = easeInOut(rawP);

    // Live target position (re-read every frame — it can itself move,
    // e.g. when the header slides away on scroll-down).
    const targetRect = target.getBoundingClientRect();
    const targetCenterX = targetRect.left + targetRect.width / 2;
    const targetCenterY = targetRect.top + targetRect.height / 2;

    // Live hero-centre position — heroTopDocY/heroHeight are fixed
    // document coordinates, so subtracting scrollY reproduces exactly
    // how a normally-flowing element would move as the page scrolls.
    const heroCenterX = window.innerWidth / 2;
    const heroCenterY = heroTopDocY + heroHeight / 2 - scrollY;

    const scaleEnd = navSize / heroImgSize;
    const currentScale = lerp(1, scaleEnd, p);

    const currentCenterX = lerp(heroCenterX, targetCenterX, p);
    const currentCenterY = lerp(heroCenterY, targetCenterY, p);

    // transform-origin is top-left, so convert the desired centre point
    // back to a top-left offset at the current (interpolated) scale.
    const half = (heroImgSize * currentScale) / 2;
    const topLeftX = currentCenterX - half;
    const topLeftY = currentCenterY - half;

    morph.style.transform = `translate(${topLeftX}px, ${topLeftY}px) scale(${currentScale})`;

    const landed = p >= 0.999;

    if (window.innerWidth < MOBILE_MENU_SNAP) {
      // Mobile menu bar: no logo should be left sitting in it — let the
      // animation carry it the rest of the way there, then fade it out
      // over the final part of the journey instead of landing solid.
      const fadeStart = 0.75;
      const fadeAmount = Math.max(0, (p - fadeStart) / (1 - fadeStart));
      morph.style.opacity = String(1 - fadeAmount);
      morph.style.pointerEvents = fadeAmount >= 1 ? "none" : "auto";
      target.classList.remove("is-visible");
    } else {
      // Desktop: hand off to the real (accessible) target link once landed.
      morph.style.opacity = landed ? "0" : "1";
      morph.style.pointerEvents = landed ? "none" : "auto";
      target.classList.toggle("is-visible", landed);
    }
  }

  measure();
  update();

  window.addEventListener("scroll", update, { passive: true });

  let resizeTimeout;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      measure();
      update();
    }, 150);
  });
}
