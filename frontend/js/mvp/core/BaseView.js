export class BaseView {
  constructor(presenter, mediator) {
    this.presenter = presenter;
    this.mediator = mediator;
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
    this.mediator.modalManager.register(modalId, options);
    this.modalIds.push(modalId);
  }

  // Setup close buttons for a modal
  setupModalCloseButtons(modalId, buttonIds) {
    this.mediator.modalManager.setupCloseButtons(modalId, buttonIds);
  }

  // Open a modal
  openModal(modalId) {
    this.mediator.modalManager.open(modalId);
  }

  // Close a modal
  closeModal(modalId) {
    this.mediator.modalManager.close(modalId);
  }

  // Setup auto-rerender on presenter data changes
  setupAutoRender(renderFn) {
    this.presenter.subscribe(() => renderFn());
  }

  // Cleanup - override in subclasses if needed
  destroy() {
    // Close all registered modals
    this.modalIds.forEach((modalId) => {
      if (this.mediator?.modalManager?.isOpen(modalId)) {
        this.mediator.modalManager.close(modalId);
      }
    });
  }
}
