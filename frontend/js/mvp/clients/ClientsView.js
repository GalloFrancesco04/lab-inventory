export function initClientsView(presenter) {
  const tbody = document.getElementById("clients-tbody");
  if (!tbody) return;

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
