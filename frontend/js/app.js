import { initRouter, navigateTo } from "./mvp/core/Router.js";
import { initLoansView } from "./mvp/loans/LoansView.js";
import { LoansPresenter } from "./mvp/loans/LoansPresenter.js";
import { initInventoryView } from "./mvp/inventory/InventoryView.js";
import { InventoryPresenter } from "./mvp/inventory/InventoryPresenter.js";
import { initClientsView } from "./mvp/clients/ClientsView.js";
import { ClientsPresenter } from "./mvp/clients/ClientsPresenter.js";
import { initMaintenanceView } from "./mvp/maintenance/MaintenanceView.js";
import { MaintenancePresenter } from "./mvp/maintenance/MaintenancePresenter.js";
import { initLoginView } from "./mvp/auth/LoginView.js";
import { AuthPresenter } from "./mvp/auth/LoginPresenter.js";
import { Mediator } from "./utils/Mediator.js";

document.addEventListener("DOMContentLoaded", () => {
  // Create mediator instance
  const mediator = new Mediator();

  // Initialize auth
  const authPresenter = new AuthPresenter();
  const loginView = initLoginView(authPresenter, mediator);

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
    // Check if user is logged in
    if (!authPresenter.isLoggedIn()) {
      loginView.showLoginView();
      return;
    }

    // Update toolbar visibility
    const isLoansRoute = viewName === "loans";
    const isInventoryRoute = viewName === "inventory";
    const isClientsRoute = viewName === "clients";

    if (newLoanBtn) newLoanBtn.classList.toggle("hidden", !isLoansRoute);
    if (newItemBtn) newItemBtn.classList.toggle("hidden", !isInventoryRoute);
    if (newClientBtn) newClientBtn.classList.toggle("hidden", !isClientsRoute);

    // Update search manager via mediator
    mediator.searchManager.setActiveView(viewName);
    mediator.searchManager.clearSearch();
  }

  document.querySelectorAll("[data-route]").forEach((btn) => {
    btn.addEventListener("click", () => navigateTo(btn.dataset.route));
  });

  // Check authentication BEFORE initializing router
  const isLoggedIn = authPresenter.isLoggedIn();

  if (!isLoggedIn) {
    loginView.showLoginView();
  } else {
    const user = authPresenter.getCurrentUser();
    loginView.showMainApp(user);
  }

  initRouter(routes, handleRouteChange, "#/loans");

  const loansPresenter = new LoansPresenter();
  const inventoryPresenter = new InventoryPresenter();
  const clientsPresenter = new ClientsPresenter();
  const maintenancePresenter = new MaintenancePresenter(
    inventoryPresenter,
    authPresenter,
  );

  // Set maintenance presenter on login view for refresh on login
  loginView.setMaintenancePresenter(maintenancePresenter);

  initInventoryView(inventoryPresenter, mediator);
  initClientsView(clientsPresenter, mediator);
  initLoansView(loansPresenter, mediator);
  initMaintenanceView(maintenancePresenter, mediator);
});
