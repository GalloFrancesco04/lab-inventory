export class ItemPicker {
  constructor({
    panel,
    searchInput,
    selectedContainer,
    toggleButton,
    toaster,
    maxItems = 5,
  }) {
    this.panel = panel;
    this.searchInput = searchInput;
    this.selectedContainer = selectedContainer;
    this.toggleButton = toggleButton;
    this.toaster = toaster;
    this.maxItems = maxItems;
    this.items = [];
    this.selectedIds = new Set();
    this.labelsMap = new Map();

    this.handleDocumentClick = this.handleDocumentClick.bind(this);
    this.handleToggle = this.handleToggle.bind(this);
    this.handleSearch = this.handleSearch.bind(this);
    this.handlePanelChange = this.handlePanelChange.bind(this);
    this.handleChipClick = this.handleChipClick.bind(this);

    this.initEvents();
  }

  initEvents() {
    this.toggleButton?.addEventListener("click", this.handleToggle);
    this.searchInput?.addEventListener("input", this.handleSearch);
    this.panel?.addEventListener("change", this.handlePanelChange);
    document.addEventListener("click", this.handleDocumentClick);
  }

  destroy() {
    this.toggleButton?.removeEventListener("click", this.handleToggle);
    this.searchInput?.removeEventListener("input", this.handleSearch);
    this.panel?.removeEventListener("change", this.handlePanelChange);
    document.removeEventListener("click", this.handleDocumentClick);
  }

  setItems(items = []) {
    this.items = items;
    if (!this.panel) return;

    this.panel.innerHTML = "";
    this.labelsMap.clear();

    const searchBox = this.searchInput;
    if (searchBox) {
      // keep search box at top if exists already
      if (!this.panel.contains(searchBox)) {
        this.panel.prepend(searchBox);
      }
    }

    items.forEach((item) => {
      const label = document.createElement("label");
      label.style.display = "flex";
      label.style.alignItems = "center";
      label.style.gap = "12px";
      label.style.padding = "10px 12px";
      label.style.borderBottom = "1px solid #f0f0f0";
      label.style.cursor = "pointer";
      label.style.userSelect = "none";
      label.style.width = "100%";
      label.style.boxSizing = "border-box";
      label.dataset.itemId = item.id;
      label.dataset.itemSearch =
        `${item.name} ${item.type} ${item.id}`.toLowerCase();

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = item.id;
      checkbox.dataset.itemId = item.id;
      checkbox.dataset.itemName = item.name;
      checkbox.dataset.itemType = item.type;
      checkbox.style.cursor = "pointer";
      checkbox.style.flexShrink = "0";

      const text = document.createElement("span");
      text.textContent = `${item.name} (${item.type}) - ID: ${item.id}`;
      text.style.flex = "1";

      label.append(checkbox, text);
      this.panel.appendChild(label);
      this.labelsMap.set(item.id, label);
    });
  }

  handleToggle(e) {
    e.stopPropagation();
    if (!this.panel) return;
    const isOpen = this.panel.style.display === "flex";
    this.panel.style.display = isOpen ? "none" : "flex";
    if (!isOpen && this.searchInput) {
      this.searchInput.focus();
    }
  }

  handleSearch(e) {
    const query = e.target.value.toLowerCase();
    this.labelsMap.forEach((label) => {
      const matches = label.dataset.itemSearch.includes(query);
      label.style.display = matches ? "flex" : "none";
    });
  }

  handleDocumentClick(e) {
    if (!this.panel || this.panel.style.display !== "flex") return;
    const clickedInsidePanel = this.panel.contains(e.target);
    const clickedButton = this.toggleButton?.contains(e.target);
    if (!clickedInsidePanel && !clickedButton) {
      this.panel.style.display = "none";
    }
  }

  handlePanelChange(e) {
    if (!e.target || e.target.type !== "checkbox") return;
    const selected = this.getSelectedItemsFromDOM();
    if (selected.length > this.maxItems) {
      e.target.checked = false;
      if (this.toaster)
        this.toaster.warning(`Max ${this.maxItems} items per loan`);
      return;
    }
    this.selectedIds = new Set(selected.map((i) => i.id));
    this.renderSelectedChips(this.getSelectedItems());
  }

  handleChipClick(e) {
    const btn = e.target.closest("button[data-remove-id]");
    if (!btn) return;
    const id = btn.dataset.removeId;
    this.uncheckItem(id);
  }

  getSelectedItemsFromDOM() {
    if (!this.panel) return [];
    const selected = [];
    this.panel.querySelectorAll("input[type='checkbox']").forEach((cb) => {
      if (cb.checked) {
        selected.push({
          id: cb.dataset.itemId,
          name: cb.dataset.itemName,
          type: cb.dataset.itemType,
        });
      }
    });
    return selected;
  }

  getSelectedItems() {
    return this.items.filter((item) => this.selectedIds.has(item.id));
  }

  renderSelectedChips(selected) {
    if (!this.selectedContainer) return;
    this.selectedContainer.innerHTML = "";
    selected.forEach((item) => {
      const chip = document.createElement("div");
      chip.className = "chip";
      chip.textContent = `${item.name} (${item.type})`;

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.dataset.removeId = item.id;
      removeBtn.textContent = "×";
      removeBtn.className = "chip-remove";
      removeBtn.addEventListener("click", this.handleChipClick);
      chip.appendChild(removeBtn);

      this.selectedContainer.appendChild(chip);
    });
  }

  reset() {
    this.selectedIds.clear();
    if (this.panel) {
      this.panel.querySelectorAll("input[type='checkbox']").forEach((cb) => {
        cb.checked = false;
      });
      this.panel.style.display = "none";
    }
    if (this.searchInput) this.searchInput.value = "";
    this.renderSelectedChips([]);
  }

  uncheckItem(id) {
    if (!id || !this.panel) return;
    const checkbox = this.panel.querySelector(
      `input[type='checkbox'][data-item-id='${id}']`,
    );
    if (checkbox) {
      checkbox.checked = false;
    }
    this.selectedIds.delete(id);
    this.renderSelectedChips(this.getSelectedItems());
  }
}
