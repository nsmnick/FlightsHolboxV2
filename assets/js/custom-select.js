// Replaces native <select> elements with a custom button + listbox.
//
// Why: Safari has a long-standing bug where a <select> styled with
// -webkit-appearance:none plus heavy custom CSS (custom arrow, padding,
// border-radius — needed to match this site's design) mispositions its
// native dropdown popover, sometimes rendering it far away from the field.
// Chrome doesn't have this issue, but Safari/iOS does, and it isn't fixable
// with CSS alone. Building our own dropdown gives full control over
// positioning on every browser.
//
// The original <select> stays in the DOM (hidden, not disabled) so form
// submission still reads its value normally, and it remains the visible,
// fully-functional control if JS fails to load or run.
class CustomSelect {
  constructor(select) {
    this.select = select;
    this.options = Array.from(select.options);
    this.optionEls = [];
    this.isOpen = false;

    this.wrapper = document.createElement("div");
    this.wrapper.className = "custom-select";

    this.toggle = document.createElement("button");
    this.toggle.type = "button";
    this.toggle.className = "custom-select__toggle";
    this.toggle.setAttribute("aria-haspopup", "listbox");
    this.toggle.setAttribute("aria-expanded", "false");

    this.list = document.createElement("ul");
    this.list.className = "custom-select__list";
    this.list.setAttribute("role", "listbox");
    this.list.hidden = true;

    this.options.forEach((option, index) => {
      const item = document.createElement("li");
      item.className = "custom-select__option";
      item.textContent = option.textContent;
      item.setAttribute("role", "option");
      item.tabIndex = -1;
      item.addEventListener("click", () => this.selectIndex(index, true));
      this.list.appendChild(item);
      this.optionEls.push(item);
    });

    this.toggle.addEventListener("click", () => this.toggleOpen());
    this.toggle.addEventListener("keydown", (e) => this.onToggleKeydown(e));
    this.list.addEventListener("keydown", (e) => this.onListKeydown(e));
    document.addEventListener("click", (e) => {
      if (!this.wrapper.contains(e.target)) this.close();
    });

    select.classList.add("custom-select__native");
    select.parentNode.insertBefore(this.wrapper, select);
    this.wrapper.append(select, this.toggle, this.list);

    this.selectIndex(select.selectedIndex, false);
  }

  toggleOpen() {
    if (this.isOpen) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    document.querySelectorAll(".custom-select--open").forEach((wrapper) => {
      if (wrapper !== this.wrapper) wrapper.dispatchEvent(new Event("customselectclose"));
    });

    this.list.hidden = false;
    this.isOpen = true;
    this.wrapper.classList.add("custom-select--open");
    this.toggle.setAttribute("aria-expanded", "true");
    (this.optionEls[this.select.selectedIndex] || this.optionEls[0])?.focus();
  }

  close() {
    if (!this.isOpen) return;
    this.list.hidden = true;
    this.isOpen = false;
    this.wrapper.classList.remove("custom-select--open");
    this.toggle.setAttribute("aria-expanded", "false");
  }

  selectIndex(index, fireChange) {
    this.select.selectedIndex = index;
    this.toggle.textContent = this.options[index]?.textContent ?? "";
    this.optionEls.forEach((el, i) => {
      el.setAttribute("aria-selected", i === index ? "true" : "false");
    });
    if (fireChange) this.select.dispatchEvent(new Event("change", { bubbles: true }));
  }

  onToggleKeydown(e) {
    if (e.key === "ArrowDown" || e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      this.open();
    }
  }

  onListKeydown(e) {
    const current = this.optionEls.indexOf(document.activeElement);

    if (e.key === "ArrowDown") {
      e.preventDefault();
      (this.optionEls[current + 1] || this.optionEls[0]).focus();
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      (this.optionEls[current - 1] || this.optionEls[this.optionEls.length - 1]).focus();
    } else if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      this.selectIndex(current, true);
      this.close();
      this.toggle.focus();
    } else if (e.key === "Escape") {
      e.preventDefault();
      this.close();
      this.toggle.focus();
    } else if (e.key === "Tab") {
      this.close();
    }
  }
}

export default function initCustomSelects() {
  document.querySelectorAll(".search-form__select").forEach((select) => {
    const instance = new CustomSelect(select);
    instance.wrapper.addEventListener("customselectclose", () => instance.close());
  });
}
