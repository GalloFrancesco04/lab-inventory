import { BaseView } from "../core/BaseView.js";
import { FormValidator } from "../../utils/validators.js";
import {
  VALIDATION_ERRORS,
  SUCCESS_MESSAGES,
  ERROR_MESSAGES,
} from "../../utils/messages.js";

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
    this.clientEditBtn = this.getElement("client-edit-btn");
    this.clientBlockBtn = this.getElement("client-block-btn");
    this.clientDeleteBtn = this.getElement("client-delete-btn");

    // Edit client modal
    this.editClientForm = this.getElement("edit-client-form");
    this.editFirstInput = this.getElement("edit-client-first");
    this.editLastInput = this.getElement("edit-client-last");
    this.editEmailInput = this.getElement("edit-client-email");

    // Block client modal
    this.blockClientForm = this.getElement("block-client-form");
    this.blockDaysInput = this.getElement("block-days");
    this.blockReasonInput = this.getElement("block-reason");

    // Delete client modal
    this.deleteClientConfirm = this.getElement("delete-client-confirm");

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
      "delete-client-modal": {
        closeButtons: ["delete-client-close", "delete-client-cancel"],
      },
    });
  }

  setupEventListeners() {
    this.newClientBtn?.addEventListener("click", () =>
      this.openModal("new-client-modal"),
    );

    this.newClientForm?.addEventListener("submit", (e) => {
      e.preventDefault();
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

      if (!firstNameOk || !emailRequiredOk || !emailFormatOk) {
        this.newClientValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.newClientValidator.focusFirstError();
        return;
      }

      try {
        this.presenter.addClient(payload);
        this.mediator.toaster.success(SUCCESS_MESSAGES.CLIENT_ADDED);
        this.closeModal("new-client-modal");
      } catch (error) {
        this.handleError(ERROR_MESSAGES.UNABLE_ADD_CLIENT, error);
      }
    });

    this.editClientForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.editClientValidator.clearAll();

      const payload = {
        firstName: this.editFirstInput?.value?.trim() || "",
        lastName: this.editLastInput?.value?.trim() || "",
        email: this.editEmailInput?.value?.trim() || "",
      };

      const firstNameOk = this.editClientValidator.required(
        this.editFirstInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const emailRequiredOk = this.editClientValidator.required(
        this.editEmailInput,
        VALIDATION_ERRORS.REQUIRED,
      );
      const emailFormatOk = emailRequiredOk
        ? this.editClientValidator.email(
            this.editEmailInput,
            VALIDATION_ERRORS.EMAIL,
          )
        : false;

      if (!firstNameOk || !emailRequiredOk || !emailFormatOk) {
        this.editClientValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.editClientValidator.focusFirstError();
        return;
      }

      try {
        this.presenter.updateClient(this.currentClientId, payload);
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

    this.clientBlockBtn?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      this.openModal("block-client-modal");
    });

    this.blockClientForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!this.currentClientId) return;
      try {
        const days = Number(this.blockDaysInput?.value || 30);
        const reason = this.blockReasonInput?.value?.trim() || "";
        this.presenter.blockClient(this.currentClientId, days, reason);
        this.mediator.toaster.warning(SUCCESS_MESSAGES.CLIENT_BLOCKED(days));
        this.closeModal("block-client-modal");
        this.openClientDetail(this.currentClientId);
      } catch (error) {
        this.handleError("Unable to block client", error);
      }
    });

    this.deleteClientConfirm?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      try {
        this.presenter.deleteClient(this.currentClientId);
        this.mediator.toaster.success(SUCCESS_MESSAGES.CLIENT_DELETED);
        this.closeModal("delete-client-modal");
        this.closeModal("client-detail-modal");
      } catch (error) {
        this.handleError("Unable to delete client", error);
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
      this.clientDetailStatus.textContent = `Blocked until ${client.blockedUntil}`;
      this.clientDetailStatus.style.color = "var(--danger)";
      this.clientBlockBtn.style.display = "none";
    } else {
      this.clientDetailStatus.textContent = "Active";
      this.clientDetailStatus.style.color = "";
      this.clientBlockBtn.style.display = "";
    }

    this.openModal("client-detail-modal");
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
    if (this.editEmailInput) this.editEmailInput.value = client.email || "";
  }
}

export function initClientsView(presenter, mediator) {
  return new ClientsView(presenter, mediator);
}
