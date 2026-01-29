export class ClientFilter {
  constructor({
    container,
    searchInput,
    selectedContainer,
    toaster,
    onSelect,
  }) {
    this.container = container;
    this.searchInput = searchInput;
    this.selectedContainer = selectedContainer;
    this.toaster = toaster;
    this.onSelect = onSelect;

    this.clients = [];
    this.selectedClient = null;
    this.dropdown = null;

    this.setupEventListeners();
  }

  setClients(clients) {
    this.clients = clients || [];
  }

  setupEventListeners() {
    this.searchInput?.addEventListener("focus", () => {
      this.openDropdown();
    });
    this.searchInput?.addEventListener("input", () => this.filterDropdown());

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!this.container?.contains(e.target)) {
        this.closeDropdown();
      }
    });
  }

  openDropdown() {
    if (this.dropdown) this.closeDropdown();

    this.dropdown = document.createElement("div");
    this.dropdown.className = "client-filter-dropdown";

    const filteredClients = this.filterClients();

    if (filteredClients.length === 0) {
      this.dropdown.innerHTML =
        '<div class="dropdown-empty">No clients found</div>';
    } else {
      this.dropdown.innerHTML = filteredClients
        .map((client) => {
          // Support both string and object format
          const clientName = typeof client === "string" ? client : client.name;
          const clientId = typeof client === "string" ? client : client.id;
          return `<div class="dropdown-item" data-client-id="${clientId || ""}" data-client-name="${clientName || "Unknown"}">
              ${clientName || "Unknown Client"}
            </div>`;
        })
        .join("");

      this.dropdown.querySelectorAll(".dropdown-item").forEach((item) => {
        item.addEventListener("click", (e) => {
          const clientId = item.dataset.clientId;
          const clientName = item.dataset.clientName;
          this.selectClient(clientId, clientName);
        });
      });
    }

    this.container?.appendChild(this.dropdown);
  }

  closeDropdown() {
    if (this.dropdown) {
      this.dropdown.remove();
      this.dropdown = null;
    }
  }

  filterDropdown() {
    if (!this.dropdown) return;

    const query = this.searchInput?.value.toLowerCase() || "";
    const items = this.dropdown.querySelectorAll(".dropdown-item");

    items.forEach((item) => {
      const name = item.textContent.toLowerCase();
      item.style.display = name.includes(query) ? "block" : "none";
    });
  }

  filterClients() {
    const query = this.searchInput?.value.toLowerCase() || "";
    if (!query) return this.clients;

    return this.clients.filter((client) => {
      const clientName = typeof client === "string" ? client : client.name;
      return clientName.toLowerCase().includes(query);
    });
  }

  selectClient(clientId, clientName) {
    this.selectedClient = { id: clientId, name: clientName };
    this.searchInput.value = clientName;
    this.closeDropdown();

    if (this.onSelect) {
      this.onSelect({ id: clientId, name: clientName });
    }
  }

  getSelectedClient() {
    return this.selectedClient;
  }

  reset() {
    this.selectedClient = null;
    this.searchInput.value = "";
    this.closeDropdown();
  }

  destroy() {
    this.closeDropdown();
    this.clients = [];
    this.selectedClient = null;
  }
}
