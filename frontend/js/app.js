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
  const newLoanBtn = document.getElementById("new-loan-btn");
  const cancelLoanBtn = document.getElementById("cancel-loan-btn");
  const modalCloseBtn = document.querySelector("#new-loan-modal .modal-close");
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

  // Loan detail panel
  const loans = [
    {
      id: "10001",
      client: "Mario Sale",
      status: "Active",
      returnDate: "12/12/2024",
      items: ["Laptop", "Mouse"],
    },
    {
      id: "10002",
      client: "Sale Mario",
      status: "Closed",
      returnDate: "02/10/2024",
      items: ["Tablet"],
    },
    {
      id: "10003",
      client: "Marius Salis",
      status: "Active",
      returnDate: "20/11/2024",
      items: ["Monitor", "HDMI Cable"],
    },
  ];

  const loanDetailModal = document.getElementById("loan-detail-modal");
  const loanDetailClose = document.getElementById("loan-detail-close");
  const detailTitle = document.getElementById("detail-loan-title");
  const detailStatus = document.getElementById("detail-loan-status");
  const detailClient = document.getElementById("detail-loan-client");
  const detailReturn = document.getElementById("detail-loan-return");
  const detailId = document.getElementById("detail-loan-id");
  const detailItems = document.getElementById("detail-loan-items");
  const closeLoanBtn = document.getElementById("detail-close-loan");

  function setStatusBadge(statusText) {
    detailStatus.textContent = statusText;
    detailStatus.className =
      "badge " + (statusText === "Active" ? "badge-success" : "badge-neutral");
  }

  function renderItems(items) {
    detailItems.innerHTML = "";
    items.forEach((item) => {
      const span = document.createElement("span");
      span.className = "chip";
      span.textContent = item;
      detailItems.appendChild(span);
    });
  }

  function showLoanDetails(loanId) {
    const loan = loans.find((l) => l.id === loanId);
    if (!loan) return;

    detailTitle.textContent = `Loan #${loan.id}`;
    detailId.textContent = loan.id;
    detailClient.textContent = loan.client;
    detailReturn.textContent = loan.returnDate || "-";
    setStatusBadge(loan.status);
    renderItems(loan.items);
    loanDetailModal.classList.add("open");
  }

  document.querySelectorAll("[data-loan-id]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const loanId = e.currentTarget.dataset.loanId;
      showLoanDetails(loanId);
    });
  });

  function closeLoanModal() {
    loanDetailModal.classList.remove("open");
  }

  // Mock action buttons
  closeLoanBtn.addEventListener("click", () =>
    console.log("Close loan (mock)"),
  );

  loanDetailClose.addEventListener("click", closeLoanModal);
  loanDetailModal.addEventListener("click", (e) => {
    if (e.target === loanDetailModal) closeLoanModal();
  });
});
