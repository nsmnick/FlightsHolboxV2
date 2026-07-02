import initVue from "./vue/init";
import initMenu from "./main-menu";
//import initPageHeaderResize from "./page-header-resize";
import animationsV2 from "./animationsv2";
import featurePanel from "./feature-panel";
import initCookieAccept from "./cookie-accept";
import initSliders from "./sliders";
// import initHomeHeroVideo from "./home-hero-video";
import initAccordion from "./accordion";
import initFaqAccordions from "./faq-accordion";
import initToggleContent from "./toggle-content";

function ready(fn) {
  if (document.readyState !== "loading") {
    fn();
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}

ready(() => {
  initVue();
  initMenu();
  initCookieAccept();
  animationsV2();
  featurePanel();
  initSliders();
  initAccordion();
  initFaqAccordions();
  initToggleContent();

  let lastScrollY = window.scrollY;
  const navbar = document.getElementById("page-header");

  window.addEventListener("scroll", () => {
    if (window.scrollY > lastScrollY) {
      // scrolling down
      if (window.scrollY > 1) navbar.classList.add("hide");
    } else {
      // scrolling up
      navbar.classList.remove("hide");
    }

    lastScrollY = window.scrollY;
  });
});
