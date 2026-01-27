export class ModalManager {
  constructor() {
    this.modals = new Map();
    this.baseZIndex = 1000;
    this.currentZIndex = this.baseZIndex;
    this.initGlobalListeners();
  }

  register(id, options = {}) {
    const element = document.getElementById(id);
    if (!element) {
      console.warn(`ModalManager: Modal with id "${id}" not found in DOM`);
      return false;
    }

    // Prevent double registration
    if (this.modals.has(id)) {
      console.warn(`ModalManager: Modal "${id}" is already registered`);
      return false;
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

    return true;
  }

  open(id) {
    const modal = this.modals.get(id);
    if (!modal) {
      console.warn(`ModalManager: Cannot open unregistered modal "${id}"`);
      return false;
    }

    // Increment z-index for new modal
    this.currentZIndex += 1;
    modal.element.style.zIndex = this.currentZIndex;

    modal.element.classList.add("open");
    if (modal.onOpen) {
      try {
        modal.onOpen();
      } catch (error) {
        console.error(
          `ModalManager: Error in onOpen callback for "${id}":`,
          error,
        );
      }
    }
    return true;
  }

  close(id) {
    const modal = this.modals.get(id);
    if (!modal) {
      console.warn(`ModalManager: Cannot close unregistered modal "${id}"`);
      return false;
    }

    modal.element.classList.remove("open");
    if (modal.onClose) {
      try {
        modal.onClose();
      } catch (error) {
        console.error(
          `ModalManager: Error in onClose callback for "${id}":`,
          error,
        );
      }
    }
    return true;
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
    const modal = this.modals.get(id);
    if (!modal) {
      console.warn(
        `ModalManager: Cannot setup buttons for unregistered modal "${id}"`,
      );
      return;
    }

    buttonIds.forEach((btnId) => {
      const btn = document.getElementById(btnId);
      if (!btn) {
        console.warn(
          `ModalManager: Close button "${btnId}" not found for modal "${id}"`,
        );
        return;
      }
      btn.addEventListener("click", () => this.close(id));
    });
  }
}
