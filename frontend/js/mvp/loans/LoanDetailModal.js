export class LoanDetailModal {
  constructor({ modalId, openModal, fields }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.fields = fields;
  }

  destroy() {
    // No event listeners to clean up
  }

  setStatusBadge(statusText) {
    const el = this.fields.status;
    if (!el) return;
    el.textContent = statusText;
    const base = "badge";
    const variant = statusText === "active" ? "badge-success" : "badge-neutral";
    el.className = `${base} ${variant}`;
  }

  renderItems(items = []) {
    const container = this.fields.items;
    if (!container) return;
    container.innerHTML = "";
    if (!items || items.length === 0) {
      const card = document.createElement("div");
      card.className = "loaned-item-card";
      card.innerHTML = '<span class="loaned-item-name">No items</span>';
      container.appendChild(card);
      return;
    }
    items.forEach((item) => {
      const card = document.createElement("div");
      card.className = "loaned-item-card";

      const name = document.createElement("span");
      name.className = "loaned-item-name";
      name.textContent = item.name || item;

      const meta = document.createElement("span");
      meta.className = "loaned-item-meta";
      const metaParts = [];
      if (item.type) metaParts.push(item.type);
      if (item.id) metaParts.push(`ID: ${item.id}`);
      meta.textContent = metaParts.join(" • ") || "Item details";

      card.appendChild(name);
      card.appendChild(meta);
      container.appendChild(card);
    });
  }

  show(loan) {
    if (!loan) return;
    const { title, client, startDate, returnDate, id } = this.fields;
    if (title) title.textContent = `Loan #${loan.id}`;
    if (id) id.textContent = loan.id;
    if (client) client.textContent = loan.client;
    if (startDate) startDate.textContent = loan.startDate || "-";
    if (returnDate) returnDate.textContent = loan.returnDate || "-";
    this.setStatusBadge(loan.status);
    this.renderItems(loan.items);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }
}
