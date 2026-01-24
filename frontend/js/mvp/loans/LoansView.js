import { BaseView } from "../core/BaseView.js";

class LoansView extends BaseView {
  constructor(presenter) {
    super(presenter);
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    this.renderLoansTable();
  }

  setupElements() {
    // Table
    this.loansTbody = this.getElement("loans-tbody");
    this.newLoanBtn = this.getElement("new-loan-btn");

    // New loan modal
    this.newLoanForm = this.getElement("new-loan-form");

    // Loan detail modal
    this.detailTitle = this.getElement("detail-loan-title");
    this.detailStatus = this.getElement("detail-loan-status");
    this.detailClient = this.getElement("detail-loan-client");
    this.detailReturn = this.getElement("detail-loan-return");
    this.detailId = this.getElement("detail-loan-id");
    this.detailItems = this.getElement("detail-loan-items");
    this.closeLoanBtn = this.getElement("detail-close-loan");

    // Close loan modal
    this.closeLoanForm = this.getElement("close-loan-form");
    this.closeLoanNotes = this.getElement("close-loan-notes");
    this.closeLoanDamaged = this.getElement("close-loan-damaged");
  }

  setupModals() {
    this.registerModal("new-loan-modal", {
      onOpen: () => this.newLoanForm?.reset(),
      onClose: () => this.newLoanForm?.reset(),
    });

    this.registerModal("loan-detail-modal");
    this.registerModal("close-loan-modal", {
      onClose: () => {
        this.closeLoanForm?.reset();
      },
    });

    this.setupModalCloseButtons("new-loan-modal", [
      "cancel-loan-btn",
      "new-loan-modal .modal-close",
    ]);
    this.setupModalCloseButtons("loan-detail-modal", ["loan-detail-close"]);
    this.setupModalCloseButtons("close-loan-modal", [
      "close-loan-notes-close",
      "close-loan-cancel",
    ]);
  }

  setupEventListeners() {
    this.newLoanBtn?.addEventListener("click", () =>
      this.openModal("new-loan-modal"),
    );

    this.newLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("New loan created (mock)");
      this.closeModal("new-loan-modal");
    });

    this.closeLoanBtn?.addEventListener("click", () => {
      this.openModal("close-loan-modal");
    });

    this.closeLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Close loan notes (mock)", {
        notes: this.closeLoanNotes?.value || "",
        damaged: this.closeLoanDamaged?.checked || false,
      });
      this.closeModal("close-loan-modal");
      this.closeModal("loan-detail-modal");
    });
  }

  showLoanDetails(loanId) {
    const loan = this.presenter.getLoanById(loanId);
    if (!loan) return;

    this.detailTitle.textContent = `Loan #${loan.id}`;
    this.detailId.textContent = loan.id;
    this.detailClient.textContent = loan.client;
    this.detailReturn.textContent = loan.returnDate || "-";
    this.setStatusBadge(this.detailStatus, loan.status);
    this.renderItems(this.detailItems, loan.items);
    this.openModal("loan-detail-modal");
  }

  setStatusBadge(el, statusText) {
    el.textContent = statusText;
    el.className =
      "badge " + (statusText === "active" ? "badge-success" : "badge-neutral");
  }

  renderItems(container, items) {
    container.innerHTML = "";
    items.forEach((item) => {
      const span = document.createElement("span");
      span.className = "chip";
      span.textContent = item;
      container.appendChild(span);
    });
  }

  getStatusBadgeClass(status) {
    if (status === "active") return "badge badge-success";
    if (status === "closed") return "badge badge-neutral";
    return "badge";
  }

  renderLoansTable() {
    if (!this.loansTbody) return;
    this.loansTbody.innerHTML = "";
    this.presenter.getLoans().forEach((loan) => {
      const row = document.createElement("tr");
      row.className = "table-row";
      row.dataset.loanId = loan.id;

      const idCell = document.createElement("td");
      idCell.textContent = loan.id;

      const clientCell = document.createElement("td");
      clientCell.textContent = loan.client;

      const itemCell = document.createElement("td");
      itemCell.textContent = loan.items?.join(", ") || "-";

      const returnDateCell = document.createElement("td");
      returnDateCell.textContent = loan.returnDate || "-";

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = this.getStatusBadgeClass(loan.status);
      statusBadge.textContent = loan.status;
      statusCell.appendChild(statusBadge);

      row.append(idCell, clientCell, itemCell, returnDateCell, statusCell);
      row.addEventListener("click", () => this.showLoanDetails(loan.id));

      this.loansTbody.appendChild(row);
    });
  }
}

export function initLoansView(presenter) {
  return new LoansView(presenter);
}
