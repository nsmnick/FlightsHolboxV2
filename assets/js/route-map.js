function renderFareRows(fare) {
  const rows = [];

  if (fare.one_way_ex) {
    rows.push(`
      <div class="route-map__fare route-map__fare--one-way">
        <div class="route-map__fare-header">
          <span class="route-map__fare-label">One Way</span>
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
          <span class="route-map__fare-label">Return</span>
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
}

// Renders prices for a single traveler-count tier. `showChangeLink` is
// false when the route only has one tier to begin with — nothing to
// "change" back to, so the group-size step is skipped entirely for those.
function renderFareInfo(infoEl, data, fareIndex, showChangeLink) {
  const fare = data.fares[fareIndex];

  const placeInfo = fare.place_info
    ? `
      <details class="route-map__place-info">
        <summary class="route-map__place-info-toggle">Plane Info</summary>
        <div class="route-map__place-info-content">${fare.place_info.replace(/\n/g, "<br>")}</div>
      </details>
    `
    : "";

  infoEl.innerHTML = `
    <p class="route-map__info-route">${data.from} <span aria-hidden="true">&rarr;</span> ${data.to}</p>
    ${
      showChangeLink
        ? `
          <p class="route-map__info-people">
            <span>${fare.people}</span>
            <button type="button" class="route-map__change-people">Change group size</button>
          </p>
        `
        : ""
    }
    <div class="route-map__fares">${renderFareRows(fare)}</div>
    ${placeInfo}
    <a class="route-map__book-btn" href="${fare.url}">See full details &amp; book</a>
  `;

  if (showChangeLink) {
    const changeBtn = infoEl.querySelector(".route-map__change-people");
    if (changeBtn) {
      changeBtn.addEventListener("click", () => renderPeopleSelect(infoEl, data));
    }
  }
}

// Quick-select step: ask which group size before showing a price, so a
// route with several traveler-count tiers doesn't dump every price on
// screen at once.
function renderPeopleSelect(infoEl, data) {
  const options = data.fares
    .map(
      (fare, index) => `
        <button type="button" class="route-map__people-btn" data-fare-index="${index}">${fare.people || "Select"}</button>
      `,
    )
    .join("");

  infoEl.innerHTML = `
    <p class="route-map__info-route">${data.from} <span aria-hidden="true">&rarr;</span> ${data.to}</p>
    <p class="route-map__info-prompt route-map__info-prompt--people">How many people are traveling?</p>
    <div class="route-map__people-select">${options}</div>
  `;

  infoEl.querySelectorAll("[data-fare-index]").forEach((btn) => {
    btn.addEventListener("click", () => renderFareInfo(infoEl, data, Number(btn.dataset.fareIndex), true));
  });
}

function renderRouteInfo(infoEl, data) {
  if (data.fares.length <= 1) {
    renderFareInfo(infoEl, data, 0, false);
    return;
  }

  renderPeopleSelect(infoEl, data);
}

export default function initRouteMap() {
  document.querySelectorAll(".route-map").forEach((mapEl) => {
    const routes = Array.from(mapEl.querySelectorAll(".route-map__route"));
    const departurePins = Array.from(mapEl.querySelectorAll(".route-map__pin--departure"));
    const info = mapEl.querySelector(".route-map__info");
    const defaultPrompt = mapEl.querySelector("[data-prompt-default]");
    const routesPrompt = mapEl.querySelector("[data-prompt-routes]");

    if (!info || !routes.length) return;

    function resetInfoPrompt() {
      if (!info) return;
      info.innerHTML = "";
      if (defaultPrompt) info.appendChild(defaultPrompt);
      if (routesPrompt) info.appendChild(routesPrompt);
    }

    function selectAirport(airportId) {
      departurePins.forEach((pin) => {
        const isSelected = pin.dataset.airport === airportId;
        pin.classList.toggle("is-selected", isSelected);
        pin.setAttribute("aria-pressed", String(isSelected));
      });

      routes.forEach((routeEl) => {
        // `.hidden = …` reflects unreliably on SVG elements in some
        // browsers (the property can appear to update while the actual
        // attribute — and therefore the `[hidden]` CSS rule — never
        // changes). toggleAttribute works on any Element regardless.
        routeEl.toggleAttribute("hidden", routeEl.dataset.from !== airportId);
        routeEl.classList.remove("is-active");
      });

      // A new airport means any previously shown fare details no longer
      // apply — back to the "pick a route" prompt rather than leaving a
      // stale route's prices on screen.
      resetInfoPrompt();
      if (defaultPrompt) defaultPrompt.hidden = true;
      if (routesPrompt) routesPrompt.hidden = false;
    }

    departurePins.forEach((pin) => {
      pin.addEventListener("click", () => selectAirport(pin.dataset.airport));
    });

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
