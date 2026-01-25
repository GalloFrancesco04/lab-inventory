let routes = {};
let defaultRoute = "#/loans";
let onRouteChangeCallback = null;

function setActiveNav(route) {
  document.querySelectorAll("[data-route]").forEach((btn) => {
    btn.classList.toggle("active", btn.dataset.route === route);
  });
}

function hideAllViews() {
  document.querySelectorAll(".view").forEach((el) => {
    el.classList.add("hidden");
  });
}

function showView(viewId) {
  hideAllViews();
  const target = document.getElementById(viewId);
  if (target) {
    target.classList.remove("hidden");
  }
}

function renderRoute() {
  const current = window.location.hash || defaultRoute;
  const viewId = routes[current] || routes[defaultRoute] || "";
  showView(viewId);
  setActiveNav(current in routes ? current : defaultRoute);

  // Call route change callback
  if (onRouteChangeCallback) {
    const routeName = current.replace("#/", "");
    onRouteChangeCallback(routeName, current);
  }
}

export function initRouter(
  routeMap,
  onRouteChange = null,
  fallbackRoute = "#/loans",
) {
  routes = routeMap;
  defaultRoute = fallbackRoute;
  onRouteChangeCallback = onRouteChange;
  window.addEventListener("hashchange", renderRoute);
  renderRoute();
}

export function navigateTo(route) {
  window.location.hash = route;
}
