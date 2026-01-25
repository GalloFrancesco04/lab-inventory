class SearchManager {
  constructor() {
    this.currentView = null;
    this.searchInput = null;
    this.searchForm = null;
    this.init();
  }

  init() {
    this.searchInput = document.getElementById("search-input");
    this.searchForm = document.getElementById("search-form");

    if (this.searchForm) {
      this.searchForm.addEventListener("submit", (e) => {
        e.preventDefault();
        this.performSearch();
      });
    }
  }

  //Register a view with its search callback
  registerView(viewName, searchCallback) {
    if (!this[viewName]) {
      this[viewName] = searchCallback;
    }
  }

  // Set the currently active view
  setActiveView(viewName) {
    this.currentView = viewName;
  }

  // Perform search on the current active view
  performSearch() {
    const query = this.searchInput?.value?.trim() || "";

    if (this.currentView && this[this.currentView]) {
      this[this.currentView](query);
    }
  }

  // Clear search input
  clearSearch() {
    if (this.searchInput) {
      this.searchInput.value = "";
    }
  }

  // Get current search query
  getQuery() {
    return this.searchInput?.value?.trim() || "";
  }
}

export { SearchManager };
