import { modalManager } from "../../utils/ModalManager.js";

export class BaseView {
  constructor(presenter) {
    this.presenter = presenter;
    this.modalIds = [];
  }

  // Get a DOM element by id
  getElement(id) {
    return document.getElementById(id);
  }

  // Get multiple DOM elements by ids
  getElements(ids) {
    const elements = {};
    ids.forEach((id) => {
      elements[id] = this.getElement(id);
    });
    return elements;
  }

  // Register a modal for this view
  registerModal(modalId, options = {}) {
    modalManager.register(modalId, options);
    this.modalIds.push(modalId);
  }

  // Setup close buttons for a modal
  setupModalCloseButtons(modalId, buttonIds) {
    modalManager.setupCloseButtons(modalId, buttonIds);
  }

  // Open a modal
  openModal(modalId) {
    modalManager.open(modalId);
  }

  // Close a modal
  closeModal(modalId) {
    modalManager.close(modalId);
  }

  // Setup auto-rerender on presenter data changes
  setupAutoRender(renderFn) {
    this.presenter.subscribe(() => renderFn());
  }

  // Cleanup - override in subclasses if needed
  destroy() {
    // Close all registered modals
    this.modalIds.forEach((modalId) => {
      if (modalManager.isOpen(modalId)) {
        modalManager.close(modalId);
      }
    });
  }
}
