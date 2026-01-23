import { initRouter, navigateTo } from "./mvp/core/Router.js";

document.addEventListener("DOMContentLoaded", () => {
  const routes = {
    "#/loans": "loans-view",
    "#/inventory": "inventory-view",
    "#/clients": "clients-view",
    "#/tech": "tech-view",
  };

  document.querySelectorAll("[data-route]").forEach((btn) => {
    btn.addEventListener("click", () => navigateTo(btn.dataset.route));
  });

  initRouter(routes, "#/loans");
});
