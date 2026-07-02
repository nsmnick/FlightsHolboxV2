/* eslint-disable no-unused-vars */
import Swiper from "swiper";
import {
  Navigation,
  Pagination,
  EffectFade,
  Autoplay,
  Thumbs,
} from "swiper/modules";
// import Swiper and modules styles

export default function initSliders() {
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
    modules: [EffectFade, Autoplay, Pagination],
    slidesPerView: 1,
    spaceBetween: 0,
    loop: true,
    wrapperClass: "hero-slider__slides",
    slideClass: "hero-slider__slide",
    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    speed: 1200,
    pagination: false,
    navigation: false,
  });
}
