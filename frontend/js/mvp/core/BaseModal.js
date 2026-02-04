export class BaseModal {
  constructor(config = {}) {
    this.modalId = config.modalId;
    this.openModal = config.openModal;
    this.closeModal = config.closeModal;
    this.mediator = config.mediator;
    this.data = config.data || {};
    this.isOpen = false;
  }

  open(data = null) {
    if (data) {
      this.setData(data);
    }
    if (this.openModal) {
      this.openModal(this.modalId);
    }
    this.isOpen = true;
    this.onOpen?.();
  }

  close() {
    if (this.closeModal) {
      this.closeModal(this.modalId);
    } else if (this.mediator?.modalManager) {
      this.mediator.modalManager.close(this.modalId);
    }
    this.isOpen = false;
    this.onClose?.();
  }

  setData(data) {
    this.data = { ...this.data, ...data };
    this.render();
  }

  getData() {
    return { ...this.data };
  }

  clearData() {
    this.data = {};
    this.render();
  }

  render() {
    // Override in subclasses
  }

  onOpen() {
    // Override in subclasses
  }

  onClose() {
    // Override in subclasses
  }

  setupEventListeners() {
    // Override in subclasses
  }

  destroy() {
    this.clearData();
    this.isOpen = false;
  }

  showError(message, field = null) {
    this.mediator?.handleError(message, new Error(message), {
      context: "modal",
      modalId: this.modalId,
      field,
    });
  }

  showSuccess(message) {
    this.mediator?.toaster?.success(message);
  }

  showWarning(message) {
    this.mediator?.toaster?.warning(message);
  }
}
