export class ItemDetailModal {
  constructor({
    modalId,
    openModal,
    fields,
    renderMaintenance,
    onSendToMaintenance,
    sendToMaintenanceBtn,
    toaster,
  }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.fields = fields;
    this.renderMaintenance = renderMaintenance;
    this.onSendToMaintenance = onSendToMaintenance;
    this.sendToMaintenanceBtn = sendToMaintenanceBtn;
    this.toaster = toaster;
    this.disabledReason = "";
    this.isMaintenanceDisabled = false;

    this.handleMaintenanceClick = this.handleMaintenanceClick.bind(this);
    if (this.sendToMaintenanceBtn) {
      this.sendToMaintenanceBtn.addEventListener(
        "click",
        this.handleMaintenanceClick,
        true,
      );
    }
  }

  destroy() {
    if (this.sendToMaintenanceBtn) {
      this.sendToMaintenanceBtn.removeEventListener(
        "click",
        this.handleMaintenanceClick,
        true,
      );
    }
  }

  handleMaintenanceClick(e) {
    if (this.isMaintenanceDisabled && this.disabledReason) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      if (this.toaster) {
        this.toaster.warning(this.disabledReason);
      }
      return false;
    }
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
    const { title, name, type, location, status, id, brand, model, note } =
      this.fields;
    if (title) title.textContent = `Item #${item.id}`;
    if (name) name.textContent = item.name;
    if (type) type.textContent = item.type;
    if (location) location.textContent = item.location;
    if (id) id.textContent = item.id || "-";
    if (brand) brand.textContent = item.brand || "-";
    if (model) model.textContent = item.model || "-";
    if (note) note.textContent = item.note || "-";
    this.setStatusBadge(item.status);
    this.updateMaintenanceButton(item.status);
    if (this.renderMaintenance) this.renderMaintenance(item.maintenance);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }

  updateMaintenanceButton(status) {
    if (!this.sendToMaintenanceBtn) return;

    const isAvailable = status === "available";
    this.isMaintenanceDisabled = !isAvailable;

    if (!isAvailable) {
      let reason = "";
      if (status === "maintenance") {
        reason = "Item is already in maintenance";
      } else if (status === "loaned") {
        reason = "Item is currently loaned";
      } else if (status === "outofservice") {
        reason = "Item is out of service";
      } else {
        reason = "Item is not available";
      }
      this.disabledReason = reason;
      this.sendToMaintenanceBtn.title = reason;
      this.sendToMaintenanceBtn.className = "btn btn-ghost";
      this.sendToMaintenanceBtn.style.cursor = "not-allowed";
      this.sendToMaintenanceBtn.style.opacity = "0.6";
    } else {
      this.disabledReason = "";
      this.sendToMaintenanceBtn.title = "";
      this.sendToMaintenanceBtn.className = "btn btn-primary";
      this.sendToMaintenanceBtn.style.cursor = "pointer";
      this.sendToMaintenanceBtn.style.opacity = "1";
    }
  }
}
