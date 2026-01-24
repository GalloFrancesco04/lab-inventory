import { initRouter, navigateTo } from "./mvp/core/Router.js";
import { initLoansView } from "./mvp/loans/LoansView.js";
import { LoansPresenter } from "./mvp/loans/LoansPresenter.js";
import { initInventoryView } from "./mvp/inventory/InventoryView.js";
import { InventoryPresenter } from "./mvp/inventory/InventoryPresenter.js";
import { initClientsView } from "./mvp/clients/ClientsView.js";
import { ClientsPresenter } from "./mvp/clients/ClientsPresenter.js";

document.addEventListener("DOMContentLoaded", () => {
  const routes = {
    "#/loans": "loans-view",
    "#/inventory": "inventory-view",
    "#/clients": "clients-view",
    "#/maintenance": "maintenance-view",
  };

  document.querySelectorAll("[data-route]").forEach((btn) => {
    btn.addEventListener("click", () => navigateTo(btn.dataset.route));
  });

  const newLoanBtn = document.getElementById("new-loan-btn");
  const newItemBtn = document.getElementById("new-item-btn");

  function updateToolbarVisibility() {
    const current = window.location.hash || "#/loans";
    const isLoansRoute = current === "#/loans";
    const isInventoryRoute = current === "#/inventory";

    if (newLoanBtn) newLoanBtn.classList.toggle("hidden", !isLoansRoute);
    if (newItemBtn) newItemBtn.classList.toggle("hidden", !isInventoryRoute);
  }

  initRouter(routes, "#/loans");

  window.addEventListener("hashchange", updateToolbarVisibility);
  updateToolbarVisibility();

  const loansPresenter = new LoansPresenter();
  const inventoryPresenter = new InventoryPresenter();
  const clientsPresenter = new ClientsPresenter();
  initInventoryView(inventoryPresenter);
  initClientsView(clientsPresenter);
  initLoansView(loansPresenter);
});
