import { modalManager } from "../../utils/ModalManager.js";

function setStatusBadge(el, statusText) {
  el.textContent = statusText;
  el.className =
    "badge " + (statusText === "Active" ? "badge-success" : "badge-neutral");
}

function renderItems(container, items) {
  container.innerHTML = "";
  items.forEach((item) => {
    const span = document.createElement("span");
    span.className = "chip";
    span.textContent = item;
    container.appendChild(span);
  });
}

export function initLoansView(presenter) {
  // New Loan modal elements
  const newLoanModal = document.getElementById("new-loan-modal");
  const newLoanBtn = document.getElementById("new-loan-btn");
  const cancelLoanBtn = document.getElementById("cancel-loan-btn");
  const modalCloseBtn = document.querySelector("#new-loan-modal .modal-close");
  const newLoanForm = document.getElementById("new-loan-form");
  const loansTbody = document.getElementById("loans-tbody");

  const loanDetailModal = document.getElementById("loan-detail-modal");
  const loanDetailClose = document.getElementById("loan-detail-close");
  const detailTitle = document.getElementById("detail-loan-title");
  const detailStatus = document.getElementById("detail-loan-status");
  const detailClient = document.getElementById("detail-loan-client");
  const detailReturn = document.getElementById("detail-loan-return");
  const detailId = document.getElementById("detail-loan-id");
  const detailItems = document.getElementById("detail-loan-items");
  const closeLoanBtn = document.getElementById("detail-close-loan");

  const closeLoanModal = document.getElementById("close-loan-modal");
  const closeLoanCloseBtn = document.getElementById("close-loan-notes-close");
  const closeLoanCancelBtn = document.getElementById("close-loan-cancel");
  const closeLoanForm = document.getElementById("close-loan-form");
  const closeLoanNotes = document.getElementById("close-loan-notes");
  const closeLoanDamaged = document.getElementById("close-loan-damaged");

  // Register all modals
  modalManager.register("new-loan-modal", {
    onClose: () => {
      newLoanForm?.reset();
    },
  });

  modalManager.register("loan-detail-modal");
  modalManager.register("close-loan-modal", {
    onClose: () => {
      closeLoanForm?.reset();
    },
  });

  // Setup close buttons
  modalManager.setupCloseButtons("new-loan-modal", [
    "cancel-loan-btn",
    "new-loan-modal .modal-close",
  ]);
  modalManager.setupCloseButtons("loan-detail-modal", ["loan-detail-close"]);
  modalManager.setupCloseButtons("close-loan-modal", [
    "close-loan-notes-close",
    "close-loan-cancel",
  ]);

  function showLoanDetails(loanId) {
    const loan = presenter.getLoanById(loanId);
    if (!loan) return;

    detailTitle.textContent = `Loan #${loan.id}`;
    detailId.textContent = loan.id;
    detailClient.textContent = loan.client;
    detailReturn.textContent = loan.returnDate || "-";
    setStatusBadge(detailStatus, loan.status);
    renderItems(detailItems, loan.items);
    modalManager.open("loan-detail-modal");
  }

  function getStatusBadgeClass(status) {
    if (status === "Active") return "badge badge-success";
    if (status === "Closed") return "badge badge-neutral";
    return "badge";
  }

  function renderLoansTable() {
    if (!loansTbody) return;
    loansTbody.innerHTML = "";
    presenter.getLoans().forEach((loan) => {
      const row = document.createElement("tr");
      row.className = "table-row";
      row.dataset.loanId = loan.id;

      const idCell = document.createElement("td");
      idCell.textContent = loan.id;

      const clientCell = document.createElement("td");
      clientCell.textContent = loan.client;

      const itemCell = document.createElement("td");
      itemCell.textContent = loan.items?.join(", ") || "-";

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = getStatusBadgeClass(loan.status);
      statusBadge.textContent = loan.status;
      statusCell.appendChild(statusBadge);

      row.append(idCell, clientCell, itemCell, statusCell);
      row.addEventListener("click", () => showLoanDetails(loan.id));

      loansTbody.appendChild(row);
    });
  }

  // New Loan modal events
  newLoanBtn?.addEventListener("click", () =>
    modalManager.open("new-loan-modal"),
  );

  newLoanForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("New loan created (mock)");
    modalManager.close("new-loan-modal");
  });

  // Loan detail modal events
  closeLoanBtn?.addEventListener("click", () => {
    modalManager.open("close-loan-modal");
  });

  closeLoanForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Close loan notes (mock)", {
      notes: closeLoanNotes?.value || "",
      damaged: closeLoanDamaged?.checked || false,
    });
    modalManager.close("close-loan-modal");
    modalManager.close("loan-detail-modal");
  });

  renderLoansTable();
}
