import { BaseView } from "../core/BaseView.js";
import { FormValidator } from "../../utils/Validators.js";
import {
  VALIDATION_ERRORS,
  SUCCESS_MESSAGES,
  ERROR_MESSAGES,
} from "../../utils/Messages.js";

class ClientsView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.currentClientId = null;
    this.initializeView({
      viewSelector: "#clients-view",
      searchViewName: "clients",
      renderMethod: "render",
    });
  }

  setupElements() {
    // Table
    this.tbody = this.getElement("clients-tbody");
    this.newClientBtn = this.getElement("new-client-btn");

    // New client modal
    this.newClientForm = this.getElement("new-client-form");
    this.firstInput = this.getElement("new-client-first");
    this.lastInput = this.getElement("new-client-last");
    this.emailInput = this.getElement("new-client-email");

    // Client detail modal
    this.clientDetailTitle = this.getElement("client-detail-title");
    this.clientDetailId = this.getElement("client-detail-id");
    this.clientDetailEmail = this.getElement("client-detail-email");
    this.clientDetailStatus = this.getElement("client-detail-status");
    this.clientDetailActive = this.getElement("client-detail-active");
    this.clientDetailTotal = this.getElement("client-detail-total");
    this.clientDetailLast = this.getElement("client-detail-last");
    this.clientLoansHistory = this.getElement("client-loans-history");
    this.clientEditBtn = this.getElement("client-edit-btn");
    this.clientBlockBtn = this.getElement("client-block-btn");

    // Edit client modal
    this.editClientForm = this.getElement("edit-client-form");
    this.editFirstInput = this.getElement("edit-client-first");
    this.editLastInput = this.getElement("edit-client-last");

    // Block client modal
    this.blockClientForm = this.getElement("block-client-form");

    this.newClientValidator = new FormValidator(this.newClientForm);
    this.editClientValidator = new FormValidator(this.editClientForm);
  }

  setupModals() {
    this.registerModals({
      "new-client-modal": {
        options: {
          onOpen: () => {
            this.newClientForm?.reset();
            this.newClientValidator?.clearAll();
            if (this.firstInput) this.firstInput.focus();
          },
        },
        closeButtons: ["new-client-close", "new-client-cancel"],
      },
      "client-detail-modal": {
        options: {
          onClose: () => {
            this.currentClientId = null;
          },
        },
        closeButtons: ["client-detail-close"],
      },
      "edit-client-modal": {
        options: {
          onOpen: () => {
            this.editClientForm?.reset();
            this.editClientValidator?.clearAll();
            this.populateEditForm();
          },
        },
        closeButtons: ["edit-client-close", "edit-client-cancel"],
      },
      "block-client-modal": {
        closeButtons: ["block-client-close", "block-client-cancel"],
      },
    });
  }

  setupEventListeners() {
    this.newClientBtn?.addEventListener("click", () =>
      this.openModal("new-client-modal"),
    );

    this.newClientForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      // The new-client form is shared with the loans view: only handle it on this page
      if (this.getElement("clients-view")?.classList.contains("hidden")) return;
      this.newClientValidator.clearAll();

      const payload = {
        firstName: this.firstInput?.value?.trim() || "",
        lastName: this.lastInput?.value?.trim() || "",
        email: this.emailInput?.value?.trim() || "",
      };

      const firstNameOk = this.newClientValidator.required(
        this.firstInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const lastNameOk = this.newClientValidator.required(
        this.lastInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const emailRequiredOk = this.newClientValidator.required(
        this.emailInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const emailFormatOk = emailRequiredOk
        ? this.newClientValidator.email(
            this.emailInput,
            VALIDATION_ERRORS.EMAIL,
          )
        : false;

      if (!firstNameOk || !lastNameOk || !emailRequiredOk || !emailFormatOk) {
        this.newClientValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.newClientValidator.focusFirstError();
        return;
      }

      try {
        await this.presenter.addClient(payload);
        this.mediator.toaster.success(SUCCESS_MESSAGES.CLIENT_ADDED);
        this.closeModal("new-client-modal");
      } catch (error) {
        this.handleError(ERROR_MESSAGES.UNABLE_ADD_CLIENT, error);
      }
    });

    this.editClientForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      this.editClientValidator.clearAll();

      const payload = {
        firstName: this.editFirstInput?.value?.trim() || "",
        lastName: this.editLastInput?.value?.trim() || "",
      };

      const firstNameOk = this.editClientValidator.required(
        this.editFirstInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const lastNameOk = this.editClientValidator.required(
        this.editLastInput,
        VALIDATION_ERRORS.REQUIRED,
      );

      if (!firstNameOk || !lastNameOk) {
        this.editClientValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.editClientValidator.focusFirstError();
        return;
      }

      try {
        await this.presenter.updateClient(this.currentClientId, payload);
        this.mediator.toaster.success(SUCCESS_MESSAGES.CLIENT_UPDATED);
        this.closeModal("edit-client-modal");
        this.openClientDetail(this.currentClientId);
      } catch (error) {
        this.handleError(ERROR_MESSAGES.FAILED_UPDATE_CLIENT, error);
      }
    });

    this.clientEditBtn?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      this.openModal("edit-client-modal");
    });

    this.clientDeleteBtn?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      this.openModal("delete-client-modal");
    });

    this.clientBlockBtn?.addEventListener("click", async () => {
      if (!this.currentClientId) return;
      const client = this.presenter.getClientById(this.currentClientId);
      if (!client?.blocked) {
        this.openModal("block-client-modal");
        return;
      }
      try {
        await this.presenter.setBlocked(this.currentClientId, false);
        this.mediator.toaster.success(SUCCESS_MESSAGES.CLIENT_UNBLOCKED);
        this.openClientDetail(this.currentClientId);
      } catch (error) {
        this.handleError("Unable to unblock client", error);
      }
    });

    this.blockClientForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!this.currentClientId) return;
      try {
        await this.presenter.setBlocked(this.currentClientId, true);
        this.mediator.toaster.warning(SUCCESS_MESSAGES.CLIENT_BLOCKED);
        this.closeModal("block-client-modal");
        this.openClientDetail(this.currentClientId);
      } catch (error) {
        this.handleError("Unable to block client", error);
      }
    });
  }

  getFilteredClients() {
    let clients = [];
    try {
      clients = this.presenter.getClients();
    } catch (error) {
      this.handleError("Unable to load clients", error);
      return [];
    }

    // Apply search filter
    const searchFiltered = this.applyFilter(
      clients,
      (client) => [
        client.id,
        client.name,
        client.email,
        client.blocked ? "blocked" : "",
      ],
      this.searchQuery,
    );

    // Apply advanced filters
    let filtered = searchFiltered;
    if (this.activeFilters.status) {
      if (this.activeFilters.status === "blocked") {
        filtered = filtered.filter((client) => client.blocked);
      } else if (this.activeFilters.status === "active") {
        filtered = filtered.filter((client) => !client.blocked);
      }
    }

    return filtered;
  }

  getSortedClients() {
    const clients = this.getFilteredClients();
    return this.applySorting(clients, {
      id: (client) => client.id,
      name: (client) => client.name.toLowerCase(),
      email: (client) => client.email.toLowerCase(),
      activeLoans: (client) => client.activeLoans || 0,
      totalLoans: (client) => client.totalLoans || 0,
      lastLoan: (client) => client.lastLoan || "",
    });
  }

  render() {
    if (!this.tbody) return;

    const clients = this.getSortedClients();
    this.tbody.innerHTML = "";

    clients.forEach((client) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";
      tr.dataset.clientId = client.id;

      const idCell = document.createElement("td");
      idCell.textContent = client.id;

      const nameCell = document.createElement("td");
      const nameSpan = document.createElement("span");
      nameSpan.textContent = client.name;
      nameCell.appendChild(nameSpan);

      if (client.blocked) {
        const badge = document.createElement("span");
        badge.className = "badge badge-danger";
        badge.style.marginLeft = "var(--space-2)";
        badge.textContent = "Blocked";
        nameCell.appendChild(badge);
      }

      const emailCell = document.createElement("td");
      emailCell.textContent = client.email;

      const activeLoansCell = document.createElement("td");
      const activeBadge = document.createElement("span");
      activeBadge.className = `badge ${client.activeLoans > 0 ? "badge-warning" : "badge-success"}`;
      activeBadge.textContent = client.activeLoans;
      activeLoansCell.appendChild(activeBadge);

      const totalLoansCell = document.createElement("td");
      totalLoansCell.textContent = client.totalLoans;

      const lastLoanCell = document.createElement("td");
      lastLoanCell.textContent = client.lastLoan;

      tr.append(
        idCell,
        nameCell,
        emailCell,
        activeLoansCell,
        totalLoansCell,
        lastLoanCell,
      );
      tr.addEventListener("click", () => this.openClientDetail(client.id));
      this.tbody.appendChild(tr);
    });
  }

  openClientDetail(clientId) {
    let client = null;
    try {
      client = this.presenter.getClientById(clientId);
    } catch (error) {
      this.handleError("Unable to load client details", error);
      return;
    }
    if (!client) {
      this.mediator.toaster.error(ERROR_MESSAGES.CLIENT_NOT_FOUND);
      return;
    }

    this.currentClientId = clientId;
    this.clientDetailTitle.textContent = client.name;
    this.clientDetailId.textContent = client.id;
    this.clientDetailEmail.textContent = client.email;
    this.clientDetailActive.textContent = client.activeLoans;
    this.clientDetailTotal.textContent = client.totalLoans;
    this.clientDetailLast.textContent = client.lastLoan;

    if (client.blocked) {
      this.clientDetailStatus.textContent = "Blocked";
      this.clientDetailStatus.style.color = "var(--danger)";
      this.clientBlockBtn.textContent = "Unblock Client";
    } else {
      this.clientDetailStatus.textContent = "Active";
      this.clientDetailStatus.style.color = "";
      this.clientBlockBtn.textContent = "Block Client";
    }

    this.renderClientLoans(clientId);
    this.openModal("client-detail-modal");
  }

  renderClientLoans(clientId) {
    if (!this.clientLoansHistory) return;

    let loans = [];
    try {
      loans = this.presenter.getLoansByClientId(clientId);
    } catch (error) {
      this.clientLoansHistory.innerHTML = `<p style="color: var(--text-muted); text-align: center; padding: var(--space-4);">Unable to load loans</p>`;
      return;
    }

    if (!loans || loans.length === 0) {
      this.clientLoansHistory.innerHTML = `<p style="color: var(--text-muted); text-align: center; padding: var(--space-4);">No loans</p>`;
      return;
    }

    // Show last 5 loans
    const recentLoans = loans.slice(-5).reverse();

    this.clientLoansHistory.innerHTML = "";

    recentLoans.forEach((loan) => {
      const loanDiv = document.createElement("div");
      loanDiv.style.cssText = `
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: var(--space-3);
        background: rgba(17, 24, 39, 0.01);
        margin-bottom: var(--space-2);
      `;

      const loanHeader = document.createElement("div");
      loanHeader.style.cssText = `
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-2);
      `;

      const loanId = document.createElement("span");
      loanId.style.cssText = "font-weight: 700; color: var(--text);";
      loanId.textContent = `Loan #${loan.id}`;

      const statusBadge = document.createElement("span");
      statusBadge.className =
        loan.status === "closed" ? "badge badge-danger" : "badge badge-success";
      statusBadge.textContent = loan.status === "closed" ? "Closed" : "Active";

      loanHeader.appendChild(loanId);
      loanHeader.appendChild(statusBadge);

      const loanDetails = document.createElement("div");
      loanDetails.style.cssText = `
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-2);
        font-size: 0.9rem;
      `;

      const details = [
        { label: "Start Date", value: loan.startDate || "-" },
        { label: "Return Date", value: loan.endDate || "-" },
        { label: "Items", value: loan.items?.length || loan.quantity || 0 },
        {
          label: "Return Status",
          value: loan.returnDate ? "Returned" : "Pending",
        },
      ];

      details.forEach(({ label, value }) => {
        const detailRow = document.createElement("div");
        detailRow.style.cssText =
          "display: flex; justify-content: space-between;";

        const labelEl = document.createElement("span");
        labelEl.style.cssText = "color: var(--text-muted); font-size: 0.85rem;";
        labelEl.textContent = label;

        const valueEl = document.createElement("span");
        valueEl.style.cssText = "color: var(--text); font-weight: 600;";
        valueEl.textContent = value;

        detailRow.appendChild(labelEl);
        detailRow.appendChild(valueEl);
        loanDetails.appendChild(detailRow);
      });

      loanDiv.appendChild(loanHeader);
      loanDiv.appendChild(loanDetails);
      this.clientLoansHistory.appendChild(loanDiv);
    });
  }

  populateEditForm() {
    if (!this.currentClientId) return;

    let client = null;
    try {
      client = this.presenter.getClientById(this.currentClientId);
    } catch (error) {
      this.handleError("Unable to load client data", error);
      return;
    }

    if (!client) {
      this.mediator.toaster.error(ERROR_MESSAGES.CLIENT_NOT_FOUND);
      return;
    }

    const nameParts = client.name.split(" ");
    const firstName = nameParts[0] || "";
    const lastName = nameParts.slice(1).join(" ") || "";

    if (this.editFirstInput) this.editFirstInput.value = firstName;
    if (this.editLastInput) this.editLastInput.value = lastName;
  }
}

export function initClientsView(presenter, mediator) {
  return new ClientsView(presenter, mediator);
}
