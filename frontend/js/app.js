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

  // New Loan Modal
  const newLoanModal = document.getElementById("new-loan-modal");
  const newLoanBtn = document.querySelector(".btn-primary"); // First btn-primary is "New Loan"
  const cancelLoanBtn = document.getElementById("cancel-loan-btn");
  const modalCloseBtn = document.querySelector(".modal-close");
  const newLoanForm = document.getElementById("new-loan-form");

  function openModal() {
    newLoanModal.classList.add("open");
  }

  function closeModal() {
    newLoanModal.classList.remove("open");
    newLoanForm.reset();
  }

  newLoanBtn.addEventListener("click", openModal);
  cancelLoanBtn.addEventListener("click", closeModal);
  modalCloseBtn.addEventListener("click", closeModal);

  newLoanForm.addEventListener("submit", (e) => {
    e.preventDefault();
    // Mock submit: just log and close for now
    console.log("New loan created (mock)");
    closeModal();
  });

  // Close modal on overlay click
  newLoanModal.addEventListener("click", (e) => {
    if (e.target === newLoanModal) {
      closeModal();
    }
  });
});
