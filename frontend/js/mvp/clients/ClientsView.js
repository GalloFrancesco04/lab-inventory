import { modalManager } from "../../utils/ModalManager.js";

export function initClientsView(presenter) {
  const tbody = document.getElementById("clients-tbody");
  const newClientBtn = document.getElementById("new-client-btn");
  const newClientModal = document.getElementById("new-client-modal");
  const newClientClose = document.getElementById("new-client-close");
  const newClientCancel = document.getElementById("new-client-cancel");
  const newClientForm = document.getElementById("new-client-form");
  const firstInput = document.getElementById("new-client-first");
  const lastInput = document.getElementById("new-client-last");
  const emailInput = document.getElementById("new-client-email");

  const clientDetailModal = document.getElementById("client-detail-modal");
  const clientDetailClose = document.getElementById("client-detail-close");
  const clientDetailTitle = document.getElementById("client-detail-title");
  const clientDetailId = document.getElementById("client-detail-id");
  const clientDetailEmail = document.getElementById("client-detail-email");
  const clientDetailStatus = document.getElementById("client-detail-status");
  const clientDetailActive = document.getElementById("client-detail-active");
  const clientDetailTotal = document.getElementById("client-detail-total");
  const clientDetailLast = document.getElementById("client-detail-last");
  const clientBlockBtn = document.getElementById("client-block-btn");
  const clientDeleteBtn = document.getElementById("client-delete-btn");

  const blockClientModal = document.getElementById("block-client-modal");
  const blockClientClose = document.getElementById("block-client-close");
  const blockClientCancel = document.getElementById("block-client-cancel");
  const blockClientForm = document.getElementById("block-client-form");
  const blockDaysInput = document.getElementById("block-days");
  const blockReasonInput = document.getElementById("block-reason");

  const deleteClientModal = document.getElementById("delete-client-modal");
  const deleteClientClose = document.getElementById("delete-client-close");
  const deleteClientCancel = document.getElementById("delete-client-cancel");
  const deleteClientConfirm = document.getElementById("delete-client-confirm");

  let currentClientId = null;
  if (!tbody) return;

  // Register all modals
  modalManager.register("new-client-modal", {
    onOpen: () => {
      newClientForm?.reset();
      if (firstInput) firstInput.focus();
    },
  });

  modalManager.register("client-detail-modal", {
    onClose: () => {
      currentClientId = null;
    },
  });

  modalManager.register("block-client-modal");
  modalManager.register("delete-client-modal");

  // Setup close buttons
  modalManager.setupCloseButtons("new-client-modal", [
    "new-client-close",
    "new-client-cancel",
  ]);
  modalManager.setupCloseButtons("client-detail-modal", [
    "client-detail-close",
  ]);
  modalManager.setupCloseButtons("block-client-modal", [
    "block-client-close",
    "block-client-cancel",
  ]);
  modalManager.setupCloseButtons("delete-client-modal", [
    "delete-client-close",
    "delete-client-cancel",
  ]);

  function render() {
    const clients = presenter.getClients();
    tbody.innerHTML = "";

    clients.forEach((client) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";
      tr.dataset.clientId = client.id;
      tr.style.cursor = "pointer";

      tr.innerHTML = `
        <td>${client.id}</td>
        <td>${client.name}</td>
        <td>${client.email}</td>
        <td>
          <span class="badge ${client.activeLoans > 0 ? "badge-warning" : "badge-success"}">
            ${client.activeLoans}
          </span>
        </td>
        <td>${client.totalLoans}</td>
        <td>${client.lastLoan}</td>
      `;

      tr.addEventListener("click", () => openClientDetail(client.id));
      tbody.appendChild(tr);
    });
  }

  function openClientDetail(clientId) {
    const client = presenter.getClientById(clientId);
    if (!client || !clientDetailModal) return;
    currentClientId = clientId;
    clientDetailTitle.textContent = client.name;
    clientDetailId.textContent = client.id;
    clientDetailEmail.textContent = client.email;
    clientDetailActive.textContent = client.activeLoans;
    clientDetailTotal.textContent = client.totalLoans;
    clientDetailLast.textContent = client.lastLoan;
    if (client.blocked) {
      clientDetailStatus.textContent = `Blocked until ${client.blockedUntil}`;
      clientDetailStatus.style.color = "var(--danger)";
      clientBlockBtn.style.display = "none";
    } else {
      clientDetailStatus.textContent = "Active";
      clientDetailStatus.style.color = "";
      clientBlockBtn.style.display = "";
    }
    modalManager.open("client-detail-modal");
  }

  newClientBtn?.addEventListener("click", () =>
    modalManager.open("new-client-modal"),
  );

  newClientForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    const payload = {
      firstName: firstInput?.value?.trim() || "",
      lastName: lastInput?.value?.trim() || "",
      email: emailInput?.value?.trim() || "",
    };
    if (!payload.firstName || !payload.email) return;
    presenter.addClient(payload);
    render();
    modalManager.close("new-client-modal");
  });

  clientDeleteBtn?.addEventListener("click", () => {
    if (!currentClientId) return;
    modalManager.open("delete-client-modal");
  });

  clientBlockBtn?.addEventListener("click", () => {
    if (!currentClientId) return;
    modalManager.open("block-client-modal");
  });

  blockClientForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!currentClientId) return;
    const days = Number(blockDaysInput?.value || 30);
    const reason = blockReasonInput?.value?.trim() || "";
    presenter.blockClient(currentClientId, days, reason);
    modalManager.close("block-client-modal");
    render();
    openClientDetail(currentClientId);
  });

  deleteClientConfirm?.addEventListener("click", () => {
    if (!currentClientId) return;
    presenter.deleteClient(currentClientId);
    modalManager.close("delete-client-modal");
    modalManager.close("client-detail-modal");
    render();
  });

  render();
}
