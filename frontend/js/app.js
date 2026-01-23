import { showView } from "./mvp/core/Router.js";

document.addEventListener("DOMContentLoaded", () => {
  // Loans button
  document.getElementById("button-loans").onclick = () =>
    showView("loans-view");
  // Inventory button
  document.getElementById("button-inventory").onclick = () =>
    showView("inventory-view");
});
