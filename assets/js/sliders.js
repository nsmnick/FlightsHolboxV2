/* eslint-disable no-unused-vars */
import Swiper from "swiper";
import {
  Navigation,
  Pagination,
  EffectFade,
  Autoplay,
  Thumbs,
} from "swiper/modules";
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

  const activitiesSliderEl = document.querySelector(".activities-swiper");
  if (activitiesSliderEl) {
    new Swiper(".activities-swiper", {
      modules: [Navigation],
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      navigation: {
        nextEl: ".activities-slider__btn--next",
        prevEl: ".activities-slider__btn--prev",
      },
      breakpoints: {
        769: {
          slidesPerView: 3,
          spaceBetween: 24,
        },
      },
    });
  }

  const imageAndTextSlider = new Swiper(
    ".text-and-image-slider__slides-wrapper",
    {
      modules: [Autoplay, Pagination],
      slidesPerView: 1,
      spaceBetween: 50,
      loop: true,
      wrapperClass: "text-and-image-slider__slides",
      slideClass: "text-and-image-slider__slide",

      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".text-and-image-slider__pagination",
        clickable: true,
        type: "bullets",
        bulletActiveClass: "text-and-image-slider__pagination__bullet--active",
        bulletClass: "text-and-image-slider__pagination__bullet",
        bulletElement: "div",
      },
      navigation: false,
    },
  );

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
