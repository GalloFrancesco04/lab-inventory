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
  if (!tbody) return;

  function render() {
    const clients = presenter.getClients();
    tbody.innerHTML = "";

    clients.forEach((client) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";

      tr.innerHTML = `
				<td>${client.id}</td>
				<td>${client.name}</td>
				<td>${client.email}</td>
				<td>${client.phone}</td>
				<td>
					<span class="badge ${client.activeLoans > 0 ? "badge-warning" : "badge-success"}">
						${client.activeLoans}
					</span>
				</td>
				<td>${client.totalLoans}</td>
				<td>${client.lastLoan}</td>
			`;

      tbody.appendChild(tr);
    });
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

  render();
}
