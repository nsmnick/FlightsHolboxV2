export default function initVideoFacade() {
  document.querySelectorAll(".video-panel__facade").forEach((button) => {
    button.addEventListener(
      "click",
      () => {
        const iframe = document.createElement("iframe");
        iframe.src = button.dataset.embedUrl;
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute(
          "allow",
          "autoplay; fullscreen; picture-in-picture",
        );
        iframe.setAttribute("allowfullscreen", "");
        iframe.setAttribute("title", "Video");
        button.replaceWith(iframe);
      },
      { once: true },
    );
  });
}
