export default function animationsV2() {
  const scrollElements = document.querySelectorAll(".animate");

  if (!scrollElements.length) return;

  if (
    window.matchMedia("(prefers-reduced-motion: reduce)").matches ||
    !("IntersectionObserver" in window)
  ) {
    scrollElements.forEach((el) => el.classList.add("scrolled"));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        entry.target.classList.toggle("scrolled", entry.isIntersecting);
      });
    },
    { threshold: 0 },
  );

  scrollElements.forEach((el) => observer.observe(el));
}

export { animationsV2 };
