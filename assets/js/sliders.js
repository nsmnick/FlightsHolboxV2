/* eslint-disable no-unused-vars */
import Swiper from "swiper";
import { Navigation, Pagination, EffectFade, Autoplay } from "swiper/modules";
import "swiper/css";
import "swiper/css/effect-fade";

export default function initSliders() {
  const airportSliderEl = document.querySelector(".airport-swiper");
  if (airportSliderEl) {
    new Swiper(".airport-swiper", {
      modules: [Navigation],
      slidesPerView: 1,
      centeredSlides: false,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: ".airport-slider__btn--next",
        prevEl: ".airport-slider__btn--prev",
      },
      breakpoints: {
        600: {
          slidesPerView: 2,
          centeredSlides: true,
          spaceBetween: 24,
        },
        960: {
          slidesPerView: 3,
          centeredSlides: true,
          spaceBetween: 30,
        },
      },
    });
  }

  // Slider Panel (image-only carousel) — same Navigation/breakpoints config
  // as the Airport Slider Panel above, looped per-instance so multiple
  // Slider Panels on one page each get their own independently-scoped
  // arrows instead of all sharing the first one's.
  document.querySelectorAll(".image-slider-panel-swiper").forEach((swiperEl) => {
    const root = swiperEl.closest(".image-slider-panel__track");
    if (!root) return;

    new Swiper(swiperEl, {
      modules: [Navigation],
      slidesPerView: 1,
      centeredSlides: false,
      spaceBetween: 20,
      loop: true,
      wrapperClass: "image-slider-panel__wrapper",
      slideClass: "image-slider-panel__slide",
      navigation: {
        nextEl: root.querySelector(".image-slider-panel__btn--next"),
        prevEl: root.querySelector(".image-slider-panel__btn--prev"),
      },
      breakpoints: {
        600: {
          slidesPerView: 2,
          centeredSlides: true,
          spaceBetween: 24,
        },
        960: {
          slidesPerView: 3,
          centeredSlides: true,
          spaceBetween: 30,
        },
      },
    });
  });

  const activitiesSliderEl = document.querySelector(".activities-swiper");
  if (activitiesSliderEl) {
    new Swiper(".activities-swiper", {
      modules: [Navigation],
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      // Centred on mobile so the active slide sits in the middle of the
      // screen instead of flush against the track's left-anchored padding —
      // that left-anchored "peek the next card" layout is deliberate from
      // bp(lap) up (see .activities-slider__track), so it's turned back off
      // there.
      centeredSlides: true,
      navigation: {
        nextEl: ".activities-slider__btn--next",
        prevEl: ".activities-slider__btn--prev",
      },
      breakpoints: {
        769: {
          slidesPerView: 3,
          spaceBetween: 24,
          centeredSlides: false,
        },
      },
    });
  }

  // Text and Image Panel's image slider — looped over so multiple panel
  // instances on the same page each get their own independent Swiper,
  // scoped to that instance's own arrows/dots/counter rather than a
  // single global selector (which would only ever wire up the first one).
  document
    .querySelectorAll(".text-and-image-slider__slides-wrapper")
    .forEach((wrapperEl) => {
      const root = wrapperEl.closest(".text-and-image-panel__image");
      if (!root) return;

      const counterCurrent = root.querySelector(
        ".text-and-image-slider__counter-current",
      );

      new Swiper(wrapperEl, {
        modules: [Navigation, Pagination, EffectFade],
        slidesPerView: 1,
        loop: true,
        effect: "fade",
        fadeEffect: {
          crossFade: true,
        },
        wrapperClass: "text-and-image-slider__slides",
        slideClass: "text-and-image-slider__slide",
        navigation: {
          nextEl: root.querySelector(".text-and-image-slider__arrow--next"),
          prevEl: root.querySelector(".text-and-image-slider__arrow--prev"),
        },
        pagination: {
          el: root.querySelector(".text-and-image-slider__pagination"),
          clickable: true,
          type: "bullets",
          bulletActiveClass: "text-and-image-slider__pagination__bullet--active",
          bulletClass: "text-and-image-slider__pagination__bullet",
          bulletElement: "button",
        },
        on: {
          slideChange(swiper) {
            if (counterCurrent) {
              counterCurrent.textContent = swiper.realIndex + 1;
            }
          },
        },
      });
    });

  const heroSlider = new Swiper(".hero-slider__slides-wrapper", {
    modules: [EffectFade, Autoplay],
    slidesPerView: 1,
    loop: true,
    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },
    autoplay: {
      delay: 4000,
      disableOnInteraction: false,
    },
    speed: 1500,
  });
}
