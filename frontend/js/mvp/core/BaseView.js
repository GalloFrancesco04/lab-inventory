export class BaseView {
  constructor(presenter, mediator) {
    this.presenter = presenter;
    this.mediator = mediator;
    this.modalIds = [];
    this.searchQuery = "";
    this.sortColumn = null;
    this.sortDirection = null;
    this.activeFilters = {};
  }

  // Template method for view initialization
  initializeView(config = {}) {
    const {
      viewSelector = null,
      searchViewName = null,
      renderMethod = "render",
    } = config;

    // Standard initialization sequence
    if (this.setupElements) this.setupElements();
    if (this.setupModals) this.setupModals();
    if (this.setupEventListeners) this.setupEventListeners();

    // Setup sorting if viewSelector provided
    if (viewSelector) {
      this.setupSorting(viewSelector);
    }

    // Register search callback if searchViewName provided
    if (searchViewName && this.mediator?.searchManager) {
      this.mediator.searchManager.registerView(searchViewName, (query) =>
        this.handleSearch(query),
      );
    }

    // Setup filter callback if filterManager provided
    if (this.mediator?.filterManager) {
      this.mediator.filterManager.onApply((filters) =>
        this.handleFilters(filters),
      );
    }

    // Setup auto-render
    if (this[renderMethod]) {
      this.setupAutoRender(() => this[renderMethod]());
      this[renderMethod]();
    }
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

  // Register multiple modals with their close buttons in one call
  registerModals(modalsConfig) {
    Object.entries(modalsConfig).forEach(([modalId, config]) => {
      if (
        config.checkIfRegistered &&
        this.mediator?.modalManager?.modals?.has(modalId)
      ) {
        return; // Skip if already registered
      }
      this.registerModal(modalId, config.options || {});
      if (config.closeButtons?.length > 0) {
        this.setupModalCloseButtons(modalId, config.closeButtons);
      }
    });
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

  // Generic filter handler
  handleFilters(filters) {
    this.activeFilters = filters;
    this.render();
  }

  // Apply advanced filters to data
  applyAdvancedFilters(data) {
    if (!this.activeFilters || Object.keys(this.activeFilters).length === 0) {
      return data;
    }

    return data.filter((item) => {
      return Object.entries(this.activeFilters).every(([key, value]) => {
        if (!value) return true;

        // Handle different filter types
        if (key === "status") {
          return item.status?.toLowerCase() === value.toLowerCase();
        }

        if (key === "type") {
          return item.type?.toLowerCase() === value.toLowerCase();
        }

        if (key === "location") {
          return item.location?.toLowerCase().includes(value.toLowerCase());
        }

        if (key === "client") {
          return item.client?.toLowerCase().includes(value.toLowerCase());
        }

        if (key === "dateFrom") {
          const itemDate = this.parseDate(item.returnDate || item.startDate);
          const filterDate = new Date(value);
          return itemDate >= filterDate;
        }

        if (key === "dateTo") {
          const itemDate = this.parseDate(item.returnDate || item.endDate);
          const filterDate = new Date(value);
          return itemDate <= filterDate;
        }

        return true;
      });
    });
  }

  // Helper to parse dates in different formats
  parseDate(dateStr) {
    if (!dateStr) return new Date(0);

    // Try DD/MM/YYYY format
    if (dateStr.includes("/")) {
      const [day, month, year] = dateStr.split("/");
      return new Date(year, month - 1, day);
    }

    // Try ISO format
    return new Date(dateStr);
  }

  // Generic error handler - logs and toasts errors
  handleError(message, error) {
    if (this.mediator?.logger?.error) {
      this.mediator.logger.error(message, error);
    } else {
      console.error(message, error);
    }
    this.mediator?.toaster?.error(message);
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
