import { initRouter, navigateTo } from "./mvp/core/Router.js";
import { initLoansView } from "./mvp/loans/LoansView.js";
import { LoansPresenter } from "./mvp/loans/LoansPresenter.js";
import { initInventoryView } from "./mvp/inventory/InventoryView.js";
import { InventoryPresenter } from "./mvp/inventory/InventoryPresenter.js";
import { initClientsView } from "./mvp/clients/ClientsView.js";
import { ClientsPresenter } from "./mvp/clients/ClientsPresenter.js";
import { initMaintenanceView } from "./mvp/maintenance/MaintenanceView.js";
import { MaintenancePresenter } from "./mvp/maintenance/MaintenancePresenter.js";
import { searchManager } from "./utils/SearchManager.js";

document.addEventListener("DOMContentLoaded", () => {
  const routes = {
    "#/loans": "loans-view",
    "#/inventory": "inventory-view",
    "#/clients": "clients-view",
    "#/maintenance": "maintenance-view",
  };

  const newLoanBtn = document.getElementById("new-loan-btn");
  const newItemBtn = document.getElementById("new-item-btn");
  const newClientBtn = document.getElementById("new-client-btn");

  // Route change callback
  function handleRouteChange(viewName, fullRoute) {
    // Update toolbar visibility
    const isLoansRoute = viewName === "loans";
    const isInventoryRoute = viewName === "inventory";
    const isClientsRoute = viewName === "clients";

    if (newLoanBtn) newLoanBtn.classList.toggle("hidden", !isLoansRoute);
    if (newItemBtn) newItemBtn.classList.toggle("hidden", !isInventoryRoute);
    if (newClientBtn) newClientBtn.classList.toggle("hidden", !isClientsRoute);

    // Update search manager
    searchManager.setActiveView(viewName);
    searchManager.clearSearch();
  }

  document.querySelectorAll("[data-route]").forEach((btn) => {
    btn.addEventListener("click", () => navigateTo(btn.dataset.route));
  });

  initRouter(routes, handleRouteChange, "#/loans");

  const loansPresenter = new LoansPresenter();
  const inventoryPresenter = new InventoryPresenter();
  const clientsPresenter = new ClientsPresenter();
  const maintenancePresenter = new MaintenancePresenter(inventoryPresenter);
  initInventoryView(inventoryPresenter);
  initClientsView(clientsPresenter);
  initLoansView(loansPresenter);
  initMaintenanceView(maintenancePresenter);
});
