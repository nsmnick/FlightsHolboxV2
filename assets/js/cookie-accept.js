const defaultConfig = {
  classCookieAccept: "cookie-accept",
  classPrefacePanel: "cookie-accept__preface",
  classManagePanel: "cookie-accept__manage",
  classAcceptAllCookiesButton: "cookie-accept__accept-cookies-button",
  classRejectAllCookiesButton: "cookie-accept__reject-cookies-button",
  classManageCookiesButton: "cookie-accept__manage-button",
  classSaveCookieSelectionButton: "cookie-accept__confirm-selection-button",
  inputNameAnalytics: "cookie-accept-enable-analytics",
  inputNameMarketing: "cookie-accept-enable-marketing",
  cookies: {
    analytics: {
      name: "analyticsConsent",
      expiryDays: 365,
      domain: "",
      path: "/",
    },
    marketing: {
      name: "marketingConsent",
      expiryDays: 365,
      domain: "",
      path: "/",
    },
  },
  showCookieAccept() {
    const [elCookieAccept] = document.getElementsByClassName(
      this.classCookieAccept,
    );
    // elCookieAccept.classList.add(`${this.classCookieAccept}--show`);
    console.log(elCookieAccept);
    if (!elCookieAccept.open) {
      elCookieAccept.showModal();
    }
  },
  hideCookieAccept() {
    const [elCookieAccept] = document.getElementsByClassName(
      this.classCookieAccept,
    );
    // elCookieAccept.classList.remove(`${this.classCookieAccept}--show`);
    if (elCookieAccept.open) {
      elCookieAccept.close();
    }
  },
  showPreface() {
    const [elPrefacePanel] = document.getElementsByClassName(
      this.classPrefacePanel,
    );
    elPrefacePanel.classList.add(`${this.classPrefacePanel}--show`);
  },
  hidePreface() {
    const [elPrefacePanel] = document.getElementsByClassName(
      this.classPrefacePanel,
    );
    elPrefacePanel.classList.remove(`${this.classPrefacePanel}--show`);
  },
  showManage() {
    const [elManagePanel] = document.getElementsByClassName(
      this.classManagePanel,
    );
    elManagePanel.classList.add(`${this.classManagePanel}--show`);
  },
  hideManage() {
    const [elManagePanel] = document.getElementsByClassName(
      this.classManagePanel,
    );
    elManagePanel.classList.add(`${this.classManagePanel}--show`);
  },
};

let config = {};
window.dataLayer = window.dataLayer || [];

function gtag() {
  // eslint-disable-next-line no-undef
  dataLayer.push(arguments);
}

// Set Google Consent Mode defaults.
function setGCMDefaults() {
  gtag("consent", "default", {
    ad_user_data: "denied",
    ad_personalization: "denied",
    ad_storage: "denied",
    analytics_storage: "denied",
    functionality_storage: "denied",
    personalization_storage: "denied",
    security_storage: "denied",
  });
}

function enableGCMAnalytics() {
  gtag("consent", "update", {
    analytics_storage: "granted",
  });
}

function enableGCMMarketing() {
  gtag("consent", "update", {
    ad_user_data: "granted",
    ad_personalization: "granted",
    ad_storage: "granted",
  });
}

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  return parts.length != 2 ? undefined : parts.pop().split(";").shift();
}

function setCookie({ name, expiryDays, domain, path }, value) {
  const cookie = [`${name}=${value}`, `path=${path || "/"}`];

  if (expiryDays !== 0) {
    var expiryDate = new Date();
    expiryDate.setDate(expiryDate.getDate() + (expiryDays || 365));
    expiryDate = expiryDate.toUTCString();
  } else {
    expiryDate = 0;
  }

  cookie.push(`expires=${expiryDate}`);

  if (domain) {
    cookie.push(`domain=${domain}`);
  }

  document.cookie = cookie.join(";");
}

function enableScripts(scriptType) {
  const scripts = document.querySelectorAll(
    `script[type="text/plain"][cookie-accept=${scriptType}]`,
  );

  for (let i = 0; i < scripts.length; i++) {
    const elScript = scripts[i];

    const srcPath = elScript.getAttribute("src");
    const elNewSrc = document.createElement("script");
    elNewSrc.setAttribute("type", "text/javascript");

    if (srcPath !== null && srcPath !== "") {
      elNewSrc.setAttribute("src", srcPath);
      elScript.parentNode.insertBefore(elNewSrc, elScript.nextSibling);
    } else {
      elNewSrc.innerHTML = elScript.innerHTML;
      scripts[i].parentNode.insertBefore(elNewSrc, scripts[i].nextSibling);
    }

    elScript.parentNode.removeChild(elScript);
  }
}

function allowAllCookies() {
  for (const cookie in config.cookies) {
    setCookie(config.cookies[cookie], "true");
  }

  enableGCMAnalytics();
  enableGCMMarketing();
  enableScripts("analytics");
  enableScripts("marketing");
}

function rejectAllCookies() {
  for (const cookie in config.cookies) {
    setCookie(config.cookies[cookie], "false");
  }
}

function setSelectedCookieOptions() {
  const elInputAnalyticsCheckBox = document.querySelector(
    `[name=${config.inputNameAnalytics}]`,
  );
  const elInputMarketingCheckBox = document.querySelector(
    `[name=${config.inputNameMarketing}]`,
  );

  console.log(elInputAnalyticsCheckBox);
  console.log(elInputMarketingCheckBox);

  if (
    elInputAnalyticsCheckBox !== undefined &&
    elInputAnalyticsCheckBox.checked === true
  ) {
    setCookie(config.cookies.analytics, "true");
    enableGCMAnalytics();
    enableScripts("analytics");
  } else {
    setCookie(config.cookies.analytics, "false");
  }

  if (
    elInputMarketingCheckBox !== undefined &&
    elInputMarketingCheckBox.checked === true
  ) {
    setCookie(config.cookies.marketing, "true");
    enableGCMMarketing();
    enableScripts("marketing");
  } else {
    setCookie(config.cookies.marketing, "false");
  }
}

function bindControls() {
  const elAllowAllButtons = document.getElementsByClassName(
    config.classAcceptAllCookiesButton,
  );
  const elRejectButtons = document.getElementsByClassName(
    config.classRejectAllCookiesButton,
  );
  const elManageButtons = document.getElementsByClassName(
    config.classManageCookiesButton,
  );
  const elConfirmButtons = document.getElementsByClassName(
    config.classSaveCookieSelectionButton,
  );

  for (const button of elAllowAllButtons) {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      allowAllCookies();
      config.hideCookieAccept();
    });
  }

  for (const button of elRejectButtons) {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      rejectAllCookies();
      config.hideCookieAccept();
    });
  }

  for (const button of elManageButtons) {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      config.hidePreface();
      config.showManage();
    });
  }

  for (const button of elConfirmButtons) {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      setSelectedCookieOptions();
      config.hideCookieAccept();
    });
  }
}

export default function initCookieAccept(customConfig) {
  config = { ...defaultConfig, ...customConfig };

  setGCMDefaults();

  const [elCookieAccept] = document.getElementsByClassName(
    config.classCookieAccept,
  );

  if (elCookieAccept === undefined) {
    console.error("Cookie accept element not found!");
    return;
  }

  const cookieAnalytics = getCookie(config.cookies.analytics.name);
  const cookieMarketing = getCookie(config.cookies.marketing.name);

  if (cookieAnalytics === undefined && cookieMarketing === undefined) {
    bindControls(config);
    config.showCookieAccept();
    config.showPreface();
  } else {
    if (cookieAnalytics === "true") {
      enableGCMAnalytics();
      enableScripts("analytics");
    }

    if (cookieMarketing === "true") {
      enableGCMMarketing();
      enableScripts("marketing");
    }
  }
}
