import { BaseView } from "../core/BaseView.js";

class ClientsView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.currentClientId = null;
    this.searchQuery = "";
    this.sortColumn = null;
    this.sortDirection = null;
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    this.setupSorting();
    // Register search callback
    this.mediator.searchManager.registerView("clients", (query) =>
      this.handleSearch(query),
    );
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
      this.mediator.toaster.success(
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
      this.mediator.toaster.warning(`Client blocked for ${days} days`);
      this.closeModal("block-client-modal");
      this.openClientDetail(this.currentClientId);
    });

    this.deleteClientConfirm?.addEventListener("click", () => {
      if (!this.currentClientId) return;
      this.presenter.deleteClient(this.currentClientId);
      this.mediator.toaster.error("Client deleted");
      this.closeModal("delete-client-modal");
      this.closeModal("client-detail-modal");
    });
  }

  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.render();
  }

  getFilteredClients() {
    const clients = this.presenter.getClients();
    if (!this.searchQuery) return clients;

    return clients.filter((client) => {
      const searchableText = [
        client.id,
        client.name,
        client.email,
        client.blocked ? "blocked" : "",
      ]
        .join(" ")
        .toLowerCase();

      return searchableText.includes(this.searchQuery);
    });
  }

  getSortedClients() {
    let clients = this.getFilteredClients();

    if (!this.sortColumn) return clients;

    return [...clients].sort((a, b) => {
      let aVal, bVal;

      switch (this.sortColumn) {
        case "id":
          aVal = a.id;
          bVal = b.id;
          break;
        case "name":
          aVal = a.name.toLowerCase();
          bVal = b.name.toLowerCase();
          break;
        case "email":
          aVal = a.email.toLowerCase();
          bVal = b.email.toLowerCase();
          break;
        case "activeLoans":
          aVal = a.activeLoans || 0;
          bVal = b.activeLoans || 0;
          break;
        case "totalLoans":
          aVal = a.totalLoans || 0;
          bVal = b.totalLoans || 0;
          break;
        case "lastLoan":
          aVal = a.lastLoan || "";
          bVal = b.lastLoan || "";
          break;
        default:
          return 0;
      }

      if (aVal < bVal) return this.sortDirection === "asc" ? -1 : 1;
      if (aVal > bVal) return this.sortDirection === "asc" ? 1 : -1;
      return 0;
    });
  }

  setupSorting() {
    const headers = document.querySelectorAll(
      "#clients-view th[data-sortable]",
    );
    headers.forEach((header) => {
      header.addEventListener("click", () => {
        const sortKey = header.dataset.sortKey;
        this.handleSort(sortKey, header);
      });
    });
  }

  handleSort(column, headerElement) {
    if (this.sortColumn === column) {
      if (this.sortDirection === "asc") {
        this.sortDirection = "desc";
      } else if (this.sortDirection === "desc") {
        this.sortColumn = null;
        this.sortDirection = null;
      } else {
        this.sortDirection = "asc";
      }
    } else {
      this.sortColumn = column;
      this.sortDirection = "asc";
    }

    const allHeaders = document.querySelectorAll(
      "#clients-view th[data-sortable]",
    );
    allHeaders.forEach((h) => {
      h.classList.remove("sorted-asc", "sorted-desc");
    });

    if (this.sortDirection) {
      headerElement.classList.add(`sorted-${this.sortDirection}`);
    }

    this.render();
  }

  render() {
    if (!this.tbody) return;

    const clients = this.getSortedClients();
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

export function initClientsView(presenter, mediator) {
  return new ClientsView(presenter, mediator);
}
