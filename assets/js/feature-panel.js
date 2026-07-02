export default function featurePanel() {
  const featurePanel = document.querySelector(".features-panel");
  if (!featurePanel) return;

  const items = featurePanel.querySelectorAll(".feature-item");

  featurePanel.addEventListener("mouseenter", () => {
    featurePanel.classList.add("is-hovering");
  });

  featurePanel.addEventListener("mouseleave", () => {
    featurePanel.classList.remove("is-hovering");

    setTimeout(() => {
      items.forEach((item) =>
        item.classList.remove("is-active", "show-content"),
      );
    }, 200);
  });

  items.forEach((item) => {
    item.addEventListener("mouseenter", () => {
      // Reset all items
      items.forEach((i) => i.classList.remove("is-active", "show-content"));

      item.classList.add("is-active");

      // Delay content until hover state is fully expanded
      setTimeout(() => {
        if (item.classList.contains("is-active")) {
          item.classList.add("show-content");
        }
      }, 100);
    });
  });
}
