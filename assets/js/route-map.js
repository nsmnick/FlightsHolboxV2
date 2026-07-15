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
