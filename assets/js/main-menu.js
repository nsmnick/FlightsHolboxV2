// https://css-tricks.com/using-css-transitions-auto-dimensions/#aa-technique-3-javascript
function collapseMenu(element) {
  const menu = element;
  const sectionHeight = menu.scrollHeight;

  // Temporarily disable all css transitions.
  const elementTransition = menu.style.transition;
  menu.style.transition = "";

  // On the next frame (as soon as the previous style change has taken effect),
  // explicitly set the element's height to its current pixel height, so we
  // aren't transitioning out of 'auto'.
  requestAnimationFrame(() => {
    menu.style.height = `${sectionHeight}px`;
    menu.style.transition = elementTransition;

    // On the next frame (as soon as the previous style change has taken effect),
    // have the element transition to height: 0.
    requestAnimationFrame(() => {
      menu.style.removeProperty("height");
    });
  });
  //menu.style.display = "none";
  menu.setAttribute("data-collapsed", "true");
}

function expandMenu(element) {
  const menu = element;
  menu.style.display = "block";
  const sectionHeight = menu.scrollHeight;
  menu.style.height = `${sectionHeight}px`;

  //menu.style.height = "auto";

  // Reset to initial height.
  menu.addEventListener("transitionend", function eventHandler() {
    menu.style.display = "block";
    menu.removeEventListener("transitionend", eventHandler);
    menu.style.height = "auto";
  });

  menu.setAttribute("data-collapsed", "false");
}

function clickMobileSubMenu(e) {
  const currMenu = e.currentTarget;
  document.location.href = currMenu.value;
}
// Must match $mobile-menu-snap in assets/styles/setup/_global.scss —
// that's the breakpoint where CSS switches from the mobile overlay
// nav to the desktop horizontal/hover nav.
const MOBILE_MENU_SNAP = 1250;

function toggleSubMenu(e) {
  const currMenu = e.currentTarget;

  if (window.innerWidth > MOBILE_MENU_SNAP) {
    return;
  }

  if (!currMenu.parentNode.classList.contains("menu-item-has-children--open")) {
    e.preventDefault();
    // Collapse all other menus.
    document
      .getElementById("main-menu")
      .querySelectorAll(".menu-item-has-children--open")
      .forEach((menuEl) => {
        collapseMenu(menuEl.querySelector(".sub-menu"));
        menuEl.classList.remove("menu-item-has-children--open");
      });

    expandMenu(currMenu.nextElementSibling);
    currMenu.parentNode.classList.add("menu-item-has-children--open");
  } else {
    //   if (currMenu.hasAttribute("href") === false) {
    //   collapseMenu(currMenu.nextElementSibling);
    //   currMenu.parentNode.classList.remove("menu-item-has-children--open");
    // } else {
    collapseMenu(currMenu.nextElementSibling);
    currMenu.parentNode.classList.remove("menu-item-has-children--open");
  }
}

export default function initMenu() {
  const pageHeader = document.getElementById("page-header");
  const mobileMenuButton = document.getElementById("mobile-menu-toggle");
  const mainMenu = document.getElementById("main-menu");
  const subMenu = document.getElementById("sub-menu");

  if (!mobileMenuButton) return;

  function scrolledState() {
    if (document.documentElement.scrollTop > 100) {
      pageHeader.classList.add("page-header--scrolled");
      mainMenu.classList.remove("main-menu--unscrolled");

      if (subMenu) {
        subMenu.classList.add("content__sub-menu-panel--scrolled");
      }
    } else if (pageHeader.classList.contains("page-header--scrolled")) {
      pageHeader.classList.remove("page-header--scrolled");
      mainMenu.classList.add("main-menu--unscrolled");

      if (subMenu) {
        subMenu.classList.remove("content__sub-menu-panel--scrolled");
      }
    }
  }

  // Run once in case page is already scrolled.
  scrolledState(true);

  document.addEventListener(
    "scroll",
    () => {
      scrolledState();
    },
    { passive: true },
  );

  // Toggle mobile menu.
  mobileMenuButton.addEventListener("click", (e) => {
    e.preventDefault();
    mobileMenuButton.classList.toggle("mobile-menu-toggle--open");
    mainMenu.classList.toggle("main-menu--open");
    //   navOuter.classList.toggle("main-menu-outer--open");
    pageHeader.classList.toggle("page-header--open");

    //document.body.classList.toggle("fixed");
  });

  // Toggle sub-menus.
  mainMenu.querySelectorAll(".menu-item-has-children > a").forEach((el) => {
    el.addEventListener("click", toggleSubMenu);
  });

  if (subMenu) {
    subMenu.querySelectorAll("select").forEach((el) => {
      el.addEventListener("change", clickMobileSubMenu);
    });
  }
}
