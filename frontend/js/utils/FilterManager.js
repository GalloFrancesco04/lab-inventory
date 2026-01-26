import { ClientFilter } from "./ClientFilter.js";

export class FilterManager {
  constructor({
    panel,
    content,
    filtersBtn,
    applyBtn,
    clearBtn,
    cancelBtn,
    presenter = null,
  }) {
    this.panel = panel;
    this.content = content;
    this.filtersBtn = filtersBtn;
    this.applyBtn = applyBtn;
    this.clearBtn = clearBtn;
    this.cancelBtn = cancelBtn;
    this.filterBadge = document.getElementById("filter-badge");
    this.presenter = presenter;
    this.presenters = {};

    if (presenter) {
      this.presenters.loans = presenter;
    }

    this.activeFilters = {};
    this.currentView = null;
    this.onApplyCallback = null;
    this.clientFilter = null;
    this.locationFilter = null;

    this.setupEventListeners();
  }

  setPresenter(presenter) {
    this.presenter = presenter;
    this.presenters.loans = presenter;

    // If current view already rendered, wire dropdowns
    if (this.currentView === "loans") {
      this.setupClientFilter();
    }
  }

  setPresenterForView(viewName, presenter) {
    if (!viewName) return;
    this.presenters[viewName] = presenter;

    if (this.currentView === viewName) {
      this.presenter = presenter;
      if (viewName === "loans") {
        this.setupClientFilter();
      }
      if (viewName === "inventory") {
        this.setupLocationFilter();
      }
    }
  }

  setupEventListeners() {
    this.filtersBtn?.addEventListener("click", () => this.togglePanel());
    this.applyBtn?.addEventListener("click", () => this.applyFilters());
    this.clearBtn?.addEventListener("click", () => this.clearAllFilters());

    // Close panel when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !this.panel?.contains(e.target) &&
        !this.filtersBtn?.contains(e.target) &&
        !this.panel?.classList.contains("hidden")
      ) {
        this.closePanel();
      }
    });
  }

  togglePanel() {
    this.panel?.classList.toggle("hidden");
  }

  openPanel() {
    this.panel?.classList.remove("hidden");
  }

  closePanel() {
    this.panel?.classList.add("hidden");
  }

  setView(viewName) {
    this.currentView = viewName;
    this.presenter = this.presenters[viewName] || this.presenter;
    this.renderFilters();
  }

  renderFilters() {
    if (!this.content) return;

    const filterConfigs = {
      inventory: [
        {
          id: "status",
          label: "Status",
          type: "select",
          options: [
            { value: "", label: "All" },
            { value: "available", label: "Available" },
            { value: "loaned", label: "Loaned" },
            { value: "maintenance", label: "In Maintenance" },
            { value: "broken", label: "Broken" },
            { value: "outOfService", label: "Out of Service" },
          ],
        },
        {
          id: "type",
          label: "Type",
          type: "select",
          options: [
            { value: "", label: "All" },
            { value: "PC", label: "PC" },
            { value: "Hardware", label: "Hardware" },
            { value: "Peripherals", label: "Peripherals" },
            { value: "Other", label: "Other" },
          ],
        },
        {
          id: "location",
          label: "Location",
          type: "location",
          placeholder: "Filter by location",
        },
      ],
      loans: [
        {
          id: "status",
          label: "Status",
          type: "select",
          options: [
            { value: "", label: "All" },
            { value: "active", label: "Active" },
            { value: "closed", label: "Closed" },
          ],
        },
        {
          id: "client",
          label: "Client",
          type: "client",
          placeholder: "Filter by client name",
        },
        {
          id: "dateFrom",
          label: "Date From",
          type: "date",
        },
        {
          id: "dateTo",
          label: "Date To",
          type: "date",
        },
      ],
      maintenance: [
        {
          id: "status",
          label: "Status",
          type: "select",
          options: [
            { value: "", label: "All" },
            { value: "open", label: "Open" },
            { value: "closed", label: "Closed" },
          ],
        },
        {
          id: "dateFrom",
          label: "Date From",
          type: "date",
        },
        {
          id: "dateTo",
          label: "Date To",
          type: "date",
        },
      ],
      clients: [
        {
          id: "status",
          label: "Status",
          type: "select",
          options: [
            { value: "", label: "All" },
            { value: "active", label: "Active" },
            { value: "blocked", label: "Blocked" },
          ],
        },
      ],
    };

    const filters = filterConfigs[this.currentView] || [];
    this.content.innerHTML = filters
      .map((filter) => this.renderFilterGroup(filter))
      .join("");

    // Setup client filter if present in current view
    if (this.currentView === "loans" && this.presenter) {
      this.setupClientFilter();
    }

    // Setup location filter if present in current view
    if (this.currentView === "inventory" && this.presenter) {
      this.setupLocationFilter();
    }

    // Restore saved filters
    this.restoreFilterValues();
  }

  setupClientFilter() {
    const filterGroup = this.content?.querySelector("#filter-group-client");
    const clientInput = this.content?.querySelector("#filter-client");

    if (!filterGroup || !clientInput || !this.presenter) {
      return;
    }

    // Get clients from presenter
    const clients = this.presenter.getClients?.() || [];

    this.clientFilter = new ClientFilter({
      container: filterGroup,
      searchInput: clientInput,
      selectedContainer: null,
      toaster: null,
      onSelect: (client) => {
        clientInput.value = client.name;
        this.activeFilters.client = client.name;
      },
    });

    this.clientFilter.setClients(clients);
  }

  setupLocationFilter() {
    const filterGroup = this.content?.querySelector("#filter-group-location");
    const locationInput = this.content?.querySelector("#filter-location");

    if (!filterGroup || !locationInput || !this.presenter) {
      return;
    }

    const locations = this.presenter.getLocations?.() || [];

    this.locationFilter = new ClientFilter({
      container: filterGroup,
      searchInput: locationInput,
      selectedContainer: null,
      toaster: null,
      onSelect: (location) => {
        const value = location.name || location;
        locationInput.value = value;
        this.activeFilters.location = value;
      },
    });

    this.locationFilter.setClients(locations);
  }

  renderFilterGroup(filter) {
    let inputHTML = "";

    if (filter.type === "select") {
      inputHTML = `
        <select id="filter-${filter.id}" data-filter="${filter.id}">
          ${filter.options.map((opt) => `<option value="${opt.value}">${opt.label}</option>`).join("")}
        </select>
      `;
    } else if (filter.type === "client") {
      inputHTML = `
        <input 
          type="text" 
          id="filter-${filter.id}" 
          data-filter="${filter.id}"
          placeholder="${filter.placeholder || ""}"
          autocomplete="off"
        />
      `;
    } else if (filter.type === "location") {
      inputHTML = `
        <input 
          type="text" 
          id="filter-${filter.id}" 
          data-filter="${filter.id}"
          placeholder="${filter.placeholder || ""}"
          autocomplete="off"
        />
      `;
    } else if (filter.type === "text") {
      inputHTML = `
        <input 
          type="text" 
          id="filter-${filter.id}" 
          data-filter="${filter.id}"
          placeholder="${filter.placeholder || ""}"
        />
      `;
    } else if (filter.type === "date") {
      inputHTML = `
        <input 
          type="date" 
          id="filter-${filter.id}" 
          data-filter="${filter.id}"
        />
      `;
    }

    return `
      <div class="filter-group" id="filter-group-${filter.id}">
        <label for="filter-${filter.id}">${filter.label}</label>
        ${inputHTML}
      </div>
    `;
  }

  restoreFilterValues() {
    Object.entries(this.activeFilters).forEach(([key, value]) => {
      const input = this.content?.querySelector(`[data-filter="${key}"]`);
      if (input) {
        input.value = value;
      }
    });
  }

  applyFilters() {
    const inputs = this.content?.querySelectorAll("[data-filter]");
    this.activeFilters = {};

    inputs?.forEach((input) => {
      const filterKey = input.dataset.filter;
      const value = input.value.trim();
      if (value) {
        this.activeFilters[filterKey] = value;
      }
    });

    this.updateBadge();
    this.closePanel();

    if (this.onApplyCallback) {
      this.onApplyCallback(this.activeFilters);
    }
  }

  clearAllFilters() {
    this.activeFilters = {};
    const inputs = this.content?.querySelectorAll("[data-filter]");
    inputs?.forEach((input) => {
      input.value = "";
    });

    this.updateBadge();

    if (this.onApplyCallback) {
      this.onApplyCallback({});
    }
  }

  updateBadge() {
    const count = Object.keys(this.activeFilters).length;
    if (this.filterBadge) {
      if (count > 0) {
        this.filterBadge.textContent = count;
        this.filterBadge.classList.remove("hidden");
      } else {
        this.filterBadge.classList.add("hidden");
      }
    }
  }

  getActiveFilters() {
    return this.activeFilters;
  }

  onApply(callback) {
    this.onApplyCallback = callback;
  }

  destroy() {
    this.activeFilters = {};
    this.onApplyCallback = null;
  }
}
