let routes = {};
let defaultRoute = "#\/loans";

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
}

export function initRouter(routeMap, fallbackRoute = "#\/loans") {
  routes = routeMap;
  defaultRoute = fallbackRoute;
  window.addEventListener("hashchange", renderRoute);
  renderRoute();
}

export function navigateTo(route) {
  window.location.hash = route;
}
