function renderRouteInfo(infoEl, data) {
  const placeInfoBlocks = data.fares
    .filter((fare) => fare.place_info)
    .map(
      (fare) => `
        <details class="route-map__place-info">
          <summary class="route-map__place-info-toggle">Plane Info${fare.people ? ` &middot; ${fare.people}` : ""}</summary>
          <div class="route-map__place-info-content">${fare.place_info.replace(/\n/g, "<br>")}</div>
        </details>
      `,
    )
    .join("");

  const fareBlocks = data.fares
    .map((fare) => {
      const rows = [];

      if (fare.one_way_ex) {
        rows.push(`
          <div class="route-map__fare route-map__fare--one-way">
            <div class="route-map__fare-header">
              <span class="route-map__fare-label">One Way${fare.people ? ` &middot; ${fare.people}` : ""}</span>
            </div>
            <div class="route-map__fare-row">
              <span>Excluding tax</span>
              <span class="route-map__fare-amount">$${fare.one_way_ex}</span>
            </div>
            <div class="route-map__fare-row route-map__fare-row--inc">
              <span>Including tax (${fare.tax_rate}%)</span>
              <span class="route-map__fare-amount route-map__fare-amount--inc">$${fare.one_way_inc}</span>
            </div>
          </div>
        `);
      }

      if (fare.rt_ex) {
        rows.push(`
          <div class="route-map__fare route-map__fare--return">
            <div class="route-map__fare-header">
              <span class="route-map__fare-label">Return${fare.people ? ` &middot; ${fare.people}` : ""}</span>
            </div>
            <div class="route-map__fare-row">
              <span>Excluding tax</span>
              <span class="route-map__fare-amount">$${fare.rt_ex}</span>
            </div>
            <div class="route-map__fare-row route-map__fare-row--inc">
              <span>Including tax (${fare.tax_rate}%)</span>
              <span class="route-map__fare-amount route-map__fare-amount--inc">$${fare.rt_inc}</span>
            </div>
          </div>
        `);
      }

      return rows.join("");
    })
    .join("");

  infoEl.innerHTML = `
    <p class="route-map__info-route">${data.from} <span aria-hidden="true">&rarr;</span> ${data.to}</p>
    <div class="route-map__fares">${fareBlocks}</div>
    ${placeInfoBlocks}
    <a class="route-map__book-btn" href="${data.url}">See full details &amp; book</a>
  `;
}

export default function initRouteMap() {
  document.querySelectorAll(".route-map").forEach((mapEl) => {
    const routes = mapEl.querySelectorAll(".route-map__route");
    const info = mapEl.querySelector(".route-map__info");

    if (!info) return;

    routes.forEach((routeEl) => {
      const activate = () => {
        let data;
        try {
          data = JSON.parse(routeEl.dataset.route);
        } catch {
          return;
        }

        routes.forEach((r) => r.classList.remove("is-active"));
        routeEl.classList.add("is-active");
        renderRouteInfo(info, data);
      };

      routeEl.addEventListener("click", activate);
      routeEl.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          activate();
        }
      });
    });
  });
}

// Each plane flies one route at a time, server-rendered, then hands off
// here: on SMIL's endEvent (fired when repeatCount="1" finishes a lap) we
// swap the <mpath> to the plane's next assigned route and restart via
// beginElement(). A declarative-only version of this (chaining
// <animateMotion> elements via "id.end", looping the last back to the
// first) proved unreliable across browsers past the first lap, so the
// hand-off is driven from JS instead. Planes with only one assigned route
// have no data-flights attribute — those just loop indefinitely in the
// markup and need no help from here.
export function initRouteMapPlanes() {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  document.querySelectorAll(".route-map__plane[data-flights]").forEach((planeEl) => {
    let flights;
    try {
      flights = JSON.parse(planeEl.dataset.flights);
    } catch {
      return;
    }

    if (!Array.isArray(flights) || flights.length < 2) return;

    const animateMotionEl = planeEl.querySelector("animateMotion");
    const mpathEl = planeEl.querySelector("mpath");

    if (!animateMotionEl || !mpathEl) return;

    let index = 0;

    animateMotionEl.addEventListener("endEvent", () => {
      index = (index + 1) % flights.length;
      const next = flights[index];

      mpathEl.setAttribute("href", `#${next.path_id}`);
      mpathEl.setAttribute("xlink:href", `#${next.path_id}`);
      animateMotionEl.setAttribute("dur", `${next.duration}s`);
      animateMotionEl.beginElement();
    });
  });
}
