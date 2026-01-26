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
    items.forEach((item) => {
      const span = document.createElement("span");
      span.className = "chip";
      span.textContent = item.name || item;
      container.appendChild(span);
    });
  }

  show(loan) {
    if (!loan) return;
    const { title, client, returnDate, id } = this.fields;
    if (title) title.textContent = `Loan #${loan.id}`;
    if (id) id.textContent = loan.id;
    if (client) client.textContent = loan.client;
    if (returnDate) returnDate.textContent = loan.returnDate || "-";
    this.setStatusBadge(loan.status);
    this.renderItems(loan.items);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }
}
