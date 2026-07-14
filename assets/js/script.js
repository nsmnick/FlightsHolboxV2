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
import initImageColumnPanel from "./image-column-panel";
import initBadgeLogoMorph from "./badge-logo-morph";
import initRouteMap, { initRouteMapPlanes } from "./route-map";
import initVideoFacade from "./video-facade";
import initSupportPanel from "./support-panel";
import initCustomSelects from "./custom-select";

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
  initImageColumnPanel();
  initBadgeLogoMorph();
  initRouteMap();
  initRouteMapPlanes();
  initVideoFacade();
  initSupportPanel();
  initCustomSelects();
});
