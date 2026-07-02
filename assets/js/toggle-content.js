class ToggleContent {
  constructor(el) {
    // Store the <details> element
    this.el = el;

    this.contentPanel =
      this.el.parentElement.parentElement.parentElement.querySelector(
        ".hover-content-container",
      );

    this.closeButton = this.contentPanel.querySelector(".close-button");
    this.el.addEventListener("click", (e) => this.onClick(e));
    this.closeButton.addEventListener("click", (e) => this.onCloseClick(e));
  }

  onClick(e) {
    // Stop default behaviour from the browser
    e.preventDefault();
    this.contentPanel.classList.add("open");
  }

  onCloseClick(e) {
    // Stop default behaviour from the browser
    e.preventDefault();
    // console.log("close");
    this.contentPanel.classList.remove("open");
  }
}

export default function initToggleContent() {
  document.querySelectorAll(".toggle-content").forEach((el) => {
    new ToggleContent(el);
  });
}
