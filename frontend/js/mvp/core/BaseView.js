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

  // Show inline validation error for a field
  showFieldError(inputElement, message) {
    if (!inputElement) return;

    // Add error class to input
    inputElement.classList.add("input-error");

    // Find or create error message element
    let errorEl = inputElement.parentElement.querySelector(".field-error");
    if (!errorEl) {
      errorEl = document.createElement("div");
      errorEl.className = "field-error";
      inputElement.parentElement.appendChild(errorEl);
    }

    errorEl.textContent = message;
    errorEl.style.display = "block";
  }

  // Clear inline validation error for a field
  clearFieldError(inputElement) {
    if (!inputElement) return;

    inputElement.classList.remove("input-error");

    const errorEl = inputElement.parentElement.querySelector(".field-error");
    if (errorEl) {
      errorEl.style.display = "none";
      errorEl.textContent = "";
    }
  }

  // Clear all errors in a form
  clearFormErrors(formElement) {
    if (!formElement) return;

    formElement.querySelectorAll(".input-error").forEach((input) => {
      input.classList.remove("input-error");
    });

    formElement.querySelectorAll(".field-error").forEach((error) => {
      error.style.display = "none";
      error.textContent = "";
    });
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
