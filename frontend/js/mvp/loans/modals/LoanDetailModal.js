export class LoanDetailModal {
  constructor({ modalId, openModal, fields }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.fields = fields;
    this.disabledReason = "";
  }

  parseDate(dateStr) {
    if (!dateStr) return null;
    if (dateStr.includes("/")) {
      const [d, m, y] = dateStr.split("/");
      return new Date(y, m - 1, d);
    }
    const parsed = new Date(dateStr);
    return isNaN(parsed) ? null : parsed;
  }

  formatDate(dateStr) {
    const date = this.parseDate(dateStr);
    if (!date || isNaN(date.getTime())) return "-";
    const dd = String(date.getDate()).padStart(2, "0");
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const yyyy = date.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
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
    const { title, client, startDate, endDate, actualReturnDate, id } =
      this.fields;
    if (title) title.textContent = `Loan #${loan.id}`;
    if (id) id.textContent = loan.id;
    if (client) client.textContent = loan.client;
    if (startDate) startDate.textContent = this.formatDate(loan.startDate);
    if (endDate) endDate.textContent = this.formatDate(loan.endDate);
    if (actualReturnDate) {
      actualReturnDate.textContent = this.formatDate(loan.actualReturnDate);
      const end = this.parseDate(loan.endDate);
      const actual = this.parseDate(loan.actualReturnDate);
      const isLate = end && actual && actual > end;
      actualReturnDate.style.color = isLate ? "var(--danger)" : "";
    }
    this.setStatusBadge(loan.status);
    this.renderItems(loan.items);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }
}
