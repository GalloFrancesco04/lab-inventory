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
    clientDetailModal.classList.add("open");
  }

  function closeClientDetail() {
    clientDetailModal?.classList.remove("open");
    currentClientId = null;
  }

  function openModal() {
    if (!newClientModal) return;
    newClientModal.classList.add("open");
    newClientForm?.reset();
    if (firstInput) firstInput.focus();
  }

  function closeModal() {
    newClientModal?.classList.remove("open");
  }

  newClientBtn?.addEventListener("click", openModal);
  newClientClose?.addEventListener("click", closeModal);
  newClientCancel?.addEventListener("click", closeModal);
  newClientModal?.addEventListener("click", (e) => {
    if (e.target === newClientModal) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && newClientModal?.classList.contains("open")) {
      closeModal();
    }
  });

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
    closeModal();
  });

  clientDetailClose?.addEventListener("click", closeClientDetail);
  clientDetailModal?.addEventListener("click", (e) => {
    if (e.target === clientDetailModal) closeClientDetail();
  });

  clientDeleteBtn?.addEventListener("click", () => {
    if (!currentClientId) return;
    deleteClientModal?.classList.add("open");
  });

  clientBlockBtn?.addEventListener("click", () => {
    if (!currentClientId) return;
    blockClientModal?.classList.add("open");
  });

  blockClientClose?.addEventListener("click", () => {
    blockClientModal?.classList.remove("open");
  });
  blockClientCancel?.addEventListener("click", () => {
    blockClientModal?.classList.remove("open");
  });
  blockClientModal?.addEventListener("click", (e) => {
    if (e.target === blockClientModal)
      blockClientModal.classList.remove("open");
  });

  blockClientForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!currentClientId) return;
    const days = Number(blockDaysInput?.value || 30);
    const reason = blockReasonInput?.value?.trim() || "";
    presenter.blockClient(currentClientId, days, reason);
    blockClientModal?.classList.remove("open");
    render();
    openClientDetail(currentClientId);
  });

  deleteClientClose?.addEventListener("click", () => {
    deleteClientModal?.classList.remove("open");
  });
  deleteClientCancel?.addEventListener("click", () => {
    deleteClientModal?.classList.remove("open");
  });
  deleteClientModal?.addEventListener("click", (e) => {
    if (e.target === deleteClientModal)
      deleteClientModal.classList.remove("open");
  });
  deleteClientConfirm?.addEventListener("click", () => {
    if (!currentClientId) return;
    presenter.deleteClient(currentClientId);
    deleteClientModal?.classList.remove("open");
    closeClientDetail();
    render();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (deleteClientModal?.classList.contains("open")) {
        deleteClientModal.classList.remove("open");
      } else if (blockClientModal?.classList.contains("open")) {
        blockClientModal.classList.remove("open");
      } else if (clientDetailModal?.classList.contains("open")) {
        closeClientDetail();
      } else if (newClientModal?.classList.contains("open")) {
        closeModal();
      }
    }
  });

  render();
}
