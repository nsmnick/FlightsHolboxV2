export default function pageHeaderResize() {
  const breakPoint = 1240;
  const pageHeader = document.querySelector(".page-header");

  if (!pageHeader) return;

  const items = document.querySelectorAll(".menu-item-has-children");
  const submenus = document.querySelectorAll(
    ".menu-item-has-children .sub-menu",
  );

  const menuMegaOverviews = document.querySelectorAll(".menu-mega__overview");

  const hideSubmenus = () =>
    submenus.forEach((submenuEl) => {
      submenuEl.style.display = "none";
      submenuEl.setAttribute("aria-hidden", "true");

      const parent = submenuEl.parentElement;
      if (parent) {
        const expandedLink = parent.querySelector("a[aria-expanded]");
        if (expandedLink) {
          expandedLink.setAttribute("aria-expanded", "false");
        }
      }
    });

  function hidePanels() {
    for (let i = 0; i < menuMegaOverviews.length; i++) {
      const menuMega = menuMegaOverviews[i];
      menuMega.hidden = true;
    }
  }

  const expand = () => {
    pageHeader.classList.add("expanded");
    pageHeader.classList.remove("collapsed");
  };

  const collapse = () => {
    pageHeader.classList.add("collapsed");
    pageHeader.classList.remove("expanded");
  };

  const closeAll = () => {
    hideSubmenus();
    hidePanels();
    collapse();
  };

  const openFor = (li, id) => {
    if (window.innerWidth < breakPoint) return;

    hideSubmenus();
    hidePanels();

    const submenuEl = li.querySelector(".sub-menu");
    if (submenuEl) {
      submenuEl.style.display = "block";
      submenuEl.setAttribute("aria-hidden", "false");
    }

    const linkEl = li.querySelector("a");
    if (linkEl) {
      linkEl.setAttribute("aria-expanded", "true");
    }

    expand();

    const panel = document.getElementById("panel-" + id);
    if (panel) {
      panel.hidden = false;
    }
  };

  function closeMenusOnBlur(e) {
    if (window.innerWidth >= breakPoint) {
      const target = e.relatedTarget;
      if (!target || !pageHeader.contains(target)) {
        closeAll();
      }
    }
  }

  items.forEach((li) => {
    const link = li.querySelector(".js-menu-link[data-panel-id]");
    const submenu = li.querySelector(".sub-menu");

    if (!link) return;

    link.setAttribute("aria-haspopup", "true");
    link.setAttribute("aria-expanded", "false");

    if (submenu) {
      submenu.setAttribute("aria-hidden", "true");
      submenu.style.display = "none";
    }

    const id = link.dataset.panelId;

    link.addEventListener("mouseenter", () => openFor(li, id));
    link.addEventListener("focus", () => openFor(li, id));

    link.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        const isExpanded = link.getAttribute("aria-expanded") === "true";
        if (isExpanded) {
          closeAll();
        } else {
          openFor(li, id);
        }
      }
    });
  });

  pageHeader.addEventListener("mouseleave", closeMenusOnBlur);
  submenus.forEach((el) => el.addEventListener("mouseleave", closeMenusOnBlur));

  pageHeader.addEventListener("focusout", (e) => {
    const relatedTarget = e.relatedTarget;
    if (!relatedTarget || !pageHeader.contains(relatedTarget)) {
      closeAll();
    }
  });

  // Sticky header
  // const stickyTrigger = document.createElement("div");
  // stickyTrigger.className = "sticky-trigger";
  // document.body.prepend(stickyTrigger);

  // const observer = new IntersectionObserver(
  //   (entries) => {
  //     if (!entries[0].isIntersecting) {
  //       pageHeader.classList.add("is-stuck");
  //     } else {
  //       pageHeader.classList.remove("is-stuck");
  //     }
  //   },
  //   { threshold: 0 },
  // );

  //observer.observe(stickyTrigger);
}
