export class ModalManager {
  constructor() {
    this.modals = new Map();
    this.initGlobalListeners();
  }

  register(id, options = {}) {
    const element = document.getElementById(id);
    if (!element) {
      console.warn(`Modal with id "${id}" not found`);
      return;
    }

    this.modals.set(id, {
      element,
      onOpen: options.onOpen || null,
      onClose: options.onClose || null,
    });

    // Click outside to close
    element.addEventListener("click", (e) => {
      if (e.target === element) {
        this.close(id);
      }
    });
  }

  open(id) {
    const modal = this.modals.get(id);
    if (!modal) return;

    modal.element.classList.add("open");
    if (modal.onOpen) modal.onOpen();
  }

  close(id) {
    const modal = this.modals.get(id);
    if (!modal) return;

    modal.element.classList.remove("open");
    if (modal.onClose) modal.onClose();
  }

  isOpen(id) {
    const modal = this.modals.get(id);
    return modal ? modal.element.classList.contains("open") : false;
  }

  getTopmostModal() {
    for (const [id, modal] of this.modals) {
      if (modal.element.classList.contains("open")) {
        return id;
      }
    }
    return null;
  }

  initGlobalListeners() {
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        // Find and close the last opened modal
        const openModals = Array.from(this.modals.entries())
          .filter(([_, modal]) => modal.element.classList.contains("open"))
          .reverse();

        if (openModals.length > 0) {
          const [id] = openModals[0];
          this.close(id);
        }
      }
    });
  }

  setupCloseButtons(id, buttonIds = []) {
    buttonIds.forEach((btnId) => {
      const btn = document.getElementById(btnId);
      btn?.addEventListener("click", () => this.close(id));
    });
  }
}

export const modalManager = new ModalManager();
