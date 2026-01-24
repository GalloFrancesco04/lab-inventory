import { BaseView } from "../core/BaseView.js";
import { toaster } from "../../utils/toaster.js";

class ClientsView extends BaseView {
  constructor(presenter) {
    super(presenter);
    this.currentClientId = null;
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    // Auto re-render on presenter data changes
    this.setupAutoRender(() => this.render());
    this.render();
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
    this.clientBlockBtn = this.getElement("client-block-btn");
    this.clientDeleteBtn = this.getElement("client-delete-btn");

    // Block client modal
    this.blockClientForm = this.getElement("block-client-form");
    this.blockDaysInput = this.getElement("block-days");
    this.blockReasonInput = this.getElement("block-reason");

    // Delete client modal
    this.deleteClientConfirm = this.getElement("delete-client-confirm");
  }

  setupModals() {
    this.registerModal("new-client-modal", {
      onOpen: () => {
        this.newClientForm?.reset();
        this.clearFormErrors(this.newClientForm);
        if (this.firstInput) this.firstInput.focus();
      },
    });

    this.registerModal("client-detail-modal", {
      onClose: () => {
        this.currentClientId = null;
      },
    });

    this.registerModal("block-client-modal");
    this.registerModal("delete-client-modal");

    this.setupModalCloseButtons("new-client-modal", [
      "new-client-close",
      "new-client-cancel",
    ]);
    this.setupModalCloseButtons("client-detail-modal", ["client-detail-close"]);
    this.setupModalCloseButtons("block-client-modal", [
      "block-client-close",
      "block-client-cancel",
    ]);
    this.setupModalCloseButtons("delete-client-modal", [
      "delete-client-close",
      "delete-client-cancel",
    ]);
  }

  setupEventListeners() {
    this.newClientBtn?.addEventListener("click", () =>
      this.openModal("new-client-modal"),
    );

    this.newClientForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.clearFormErrors(this.newClientForm);

      const payload = {
        firstName: this.firstInput?.value?.trim() || "",
        lastName: this.lastInput?.value?.trim() || "",
        email: this.emailInput?.value?.trim() || "",
      };

      let hasError = false;

      if (!payload.firstName) {
        this.showFieldError(this.firstInput, "First name is required");
        hasError = true;
      }

      if (!payload.email) {
        this.showFieldError(this.emailInput, "Email is required");
        hasError = true;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.email)) {
        this.showFieldError(this.emailInput, "Please enter a valid email");
        hasError = true;
      }

      if (hasError) {
        const firstError = this.newClientForm.querySelector(".input-error");
        if (firstError) firstError.focus();
        return;
      }

      this.presenter.addClient(payload);
      toaster.success(
        `Client "${payload.firstName} ${payload.lastName}" added successfully`,
      );
      this.closeModal("new-client-modal");
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
      const days = Number(this.blockDaysInput?.value || 30);
      const reason = this.blockReasonInput?.value?.trim() || "";
      this.presenter.blockClient(this.currentClientId, days, reason);
      toaster.warning(`Client blocked for ${days} days`);
      this.closeModal("block-client-modal");
      this.openClientDetail(this.currentClientId);
    });

    this.deleteClientConfirm?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      this.presenter.deleteClient(this.currentClientId);
      toaster.error("Client deleted");
      this.closeModal("delete-client-modal");
      this.closeModal("client-detail-modal");
    });
  }

  render() {
    if (!this.tbody) return;

    const clients = this.presenter.getClients();
    this.tbody.innerHTML = "";

    clients.forEach((client) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";
      tr.dataset.clientId = client.id;
      tr.style.cursor = "pointer";

      tr.innerHTML = `
        <td>${client.id}</td>
        <td>
          ${client.name}
          ${client.blocked ? '<span class="badge badge-danger" style="margin-left: var(--space-2)">Blocked</span>' : ""}
        </td>
        <td>${client.email}</td>
        <td>
          <span class="badge ${client.activeLoans > 0 ? "badge-warning" : "badge-success"}">
            ${client.activeLoans}
          </span>
        </td>
        <td>${client.totalLoans}</td>
        <td>${client.lastLoan}</td>
      `;

      tr.addEventListener("click", () => this.openClientDetail(client.id));
      this.tbody.appendChild(tr);
    });
  }

  openClientDetail(clientId) {
    const client = this.presenter.getClientById(clientId);
    if (!client) return;

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
}

export function initClientsView(presenter) {
  return new ClientsView(presenter);
}
