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

  // Generic sorting setup for table headers
  setupSorting(viewSelector) {
    this.sortColumn = null;
    this.sortDirection = null;
    const headers = document.querySelectorAll(
      `${viewSelector} th[data-sortable]`,
    );
    headers.forEach((header) => {
      header.addEventListener("click", () => {
        const sortKey = header.dataset.sortKey;
        this.handleSort(sortKey, header, viewSelector);
      });
    });
  }

  // Generic sort handler
  handleSort(column, headerElement, viewSelector) {
    // Toggle sort direction
    if (this.sortColumn === column) {
      if (this.sortDirection === "asc") {
        this.sortDirection = "desc";
      } else if (this.sortDirection === "desc") {
        this.sortColumn = null;
        this.sortDirection = null;
      } else {
        this.sortDirection = "asc";
      }
    } else {
      this.sortColumn = column;
      this.sortDirection = "asc";
    }

    // Update header classes
    const allHeaders = document.querySelectorAll(
      `${viewSelector} th[data-sortable]`,
    );
    allHeaders.forEach((h) => {
      h.classList.remove("sorted-asc", "sorted-desc");
    });

    if (this.sortDirection) {
      headerElement.classList.add(`sorted-${this.sortDirection}`);
    }

    // Trigger render - subclass should implement
    this.render();
  }

  // Generic sorting helper - sorts array by column and direction
  applySorting(data, sortConfig) {
    if (!this.sortColumn || !sortConfig[this.sortColumn]) return data;

    const getValue = sortConfig[this.sortColumn];

    return [...data].sort((a, b) => {
      const aVal = getValue(a);
      const bVal = getValue(b);

      let result = 0;
      if (aVal < bVal) result = -1;
      else if (aVal > bVal) result = 1;

      return this.sortDirection === "desc" ? -result : result;
    });
  }

  // Generic filtering helper - filters data by search query
  applyFilter(data, searchableFields, searchQuery) {
    if (!searchQuery) return data;

    const query = searchQuery.toLowerCase();
    return data.filter((item) => {
      const searchableText = searchableFields(item).join(" ").toLowerCase();
      return searchableText.includes(query);
    });
  }

  // Generic search handler
  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.render();
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
