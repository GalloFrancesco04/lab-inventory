import { BaseView } from "../core/BaseView.js";
import { logger } from "../../utils/logger.js";
import { toaster } from "../../utils/toaster.js";
import { searchManager } from "../../utils/SearchManager.js";

class LoansView extends BaseView {
  constructor(presenter) {
    super(presenter);
    this.searchQuery = "";
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    // Register search callback
    searchManager.registerView("loans", (query) => this.handleSearch(query));
    // Auto re-render on presenter data changes
    this.setupAutoRender(() => this.renderLoansTable());
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
      logger.mock("New loan created");
      toaster.success("New loan created successfully");
      this.closeModal("new-loan-modal");
    });

    this.closeLoanBtn?.addEventListener("click", () => {
      this.openModal("close-loan-modal");
    });

    this.closeLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      logger.mock("Close loan", {
        notes: this.closeLoanNotes?.value || "",
        damaged: this.closeLoanDamaged?.checked || false,
      });
      toaster.info("Loan closed successfully");
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
      span.textContent = item.name || item;
      container.appendChild(span);
    });
  }

  getStatusBadgeClass(status) {
    if (status === "active") return "badge badge-success";
    if (status === "closed") return "badge badge-neutral";
    return "badge";
  }

  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.renderLoansTable();
  }

  getFilteredLoans() {
    const loans = this.presenter.getLoans();
    if (!this.searchQuery) return loans;

    return loans.filter((loan) => {
      const searchableText = [
        loan.id,
        loan.client,
        loan.status,
        loan.returnDate || "",
        ...(loan.items?.map((item) => item.name) || []),
      ]
        .join(" ")
        .toLowerCase();

      return searchableText.includes(this.searchQuery);
    });
  }

  renderLoansTable() {
    if (!this.loansTbody) return;
    this.loansTbody.innerHTML = "";
    this.getFilteredLoans().forEach((loan) => {
      const row = document.createElement("tr");
      row.className = "table-row";
      row.dataset.loanId = loan.id;

      const idCell = document.createElement("td");
      idCell.textContent = loan.id;

      const clientCell = document.createElement("td");
      clientCell.textContent = loan.client;

      const itemCell = document.createElement("td");
      itemCell.textContent =
        loan.items?.map((item) => item.name).join(", ") || "-";

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
