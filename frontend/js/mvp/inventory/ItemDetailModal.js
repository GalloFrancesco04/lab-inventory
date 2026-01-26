export class ItemDetailModal {
  constructor({
    modalId,
    openModal,
    fields,
    renderMaintenance,
    onSendToMaintenance,
  }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.fields = fields;
    this.renderMaintenance = renderMaintenance;
    this.onSendToMaintenance = onSendToMaintenance;
  }

  destroy() {
    // No event listeners to clean up
  }

  setStatusBadge(statusText) {
    const el = this.fields.status;
    if (!el) return;
    el.textContent = statusText;
    const classes = this.getStatusBadgeClass(statusText);
    el.className = classes;
  }

  getStatusBadgeClass(status) {
    if (status === "available") return "badge badge-success";
    if (status === "loaned") return "badge badge-warning";
    if (status === "maintenance") return "badge badge-warning";
    if (status === "outofservice") return "badge badge-danger";
    return "badge";
  }

  show(item) {
    if (!item) return;
    const { title, name, type, location, status } = this.fields;
    if (title) title.textContent = `Item #${item.id}`;
    if (name) name.textContent = item.name;
    if (type) type.textContent = item.type;
    if (location) location.textContent = item.location;
    this.setStatusBadge(item.status);
    if (this.renderMaintenance) this.renderMaintenance(item.maintenance);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }
}
