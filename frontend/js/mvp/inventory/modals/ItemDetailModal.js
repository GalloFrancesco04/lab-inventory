export class ItemDetailModal {
  constructor({
    modalId,
    openModal,
    fields,
    renderMaintenance,
    onSendToMaintenance,
    sendToMaintenanceBtn,
    editItemBtn,
    toaster,
  }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.fields = fields;
    this.renderMaintenance = renderMaintenance;
    this.onSendToMaintenance = onSendToMaintenance;
    this.sendToMaintenanceBtn = sendToMaintenanceBtn;
    this.editItemBtn = editItemBtn;
    this.toaster = toaster;
    this.disabledReason = "";
    this.isMaintenanceDisabled = false;
    this.isEditDisabled = false;

    this.handleMaintenanceClick = this.handleMaintenanceClick.bind(this);
    this.handleEditClick = this.handleEditClick.bind(this);
    if (this.sendToMaintenanceBtn) {
      this.sendToMaintenanceBtn.addEventListener(
        "click",
        this.handleMaintenanceClick,
        true,
      );
    }

    if (this.editItemBtn) {
      this.editItemBtn.addEventListener("click", this.handleEditClick, true);
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

    if (this.editItemBtn) {
      this.editItemBtn.removeEventListener("click", this.handleEditClick, true);
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

  handleEditClick(e) {
    if (this.isEditDisabled && this.disabledReason) {
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
    this.updateActionButtons(item.status);
    if (this.renderMaintenance) this.renderMaintenance(item.maintenance);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }

  getDisabledReason(status) {
    if (status === "maintenance") return "Item is already in maintenance";
    if (status === "loaned") return "Item is currently loaned";
    if (status === "outofservice") return "Item is out of service";
    return "Item is not available";
  }

  applyButtonState(button, isDisabled, reason) {
    if (!button) return;
    if (isDisabled) {
      button.title = reason;
      button.className = "btn btn-ghost";
      button.style.cursor = "not-allowed";
      button.style.opacity = "0.6";
    } else {
      button.title = "";
      button.className = "btn btn-primary";
      button.style.cursor = "pointer";
      button.style.opacity = "1";
    }
  }

  updateActionButtons(status) {
    // Allow available or damaged items to perform actions
    const canPerformActions = status === "available" || status === "damaged";
    this.isMaintenanceDisabled = !canPerformActions;
    this.isEditDisabled = !canPerformActions;
    this.disabledReason = canPerformActions
      ? ""
      : this.getDisabledReason(status);

    this.applyButtonState(
      this.sendToMaintenanceBtn,
      this.isMaintenanceDisabled,
      this.disabledReason,
    );
    this.applyButtonState(
      this.editItemBtn,
      this.isEditDisabled,
      this.disabledReason,
    );
  }
}
