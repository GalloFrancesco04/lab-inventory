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

    // Clear existing items but keep search input
    const searchBox = this.searchInput;
    this.labelsMap.clear();

    // Remove all labels (keep search input)
    const allLabels = this.panel.querySelectorAll("label");
    allLabels.forEach((label) => label.remove());

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
      label.style.backgroundColor = "transparent";
      label.style.transition = "background-color 0.2s";
      label.dataset.itemId = item.id;
      label.dataset.itemSearch =
        `${item.name} ${item.type} ${item.id}`.toLowerCase();

      label.addEventListener("mouseenter", () => {
        label.style.backgroundColor = "#f5f5f5";
      });
      label.addEventListener("mouseleave", () => {
        label.style.backgroundColor = "transparent";
      });

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = item.id;
      checkbox.dataset.itemId = item.id;
      checkbox.dataset.itemName = item.name;
      checkbox.dataset.itemType = item.type;
      checkbox.style.cursor = "pointer";
      checkbox.style.flexShrink = "0";
      checkbox.style.width = "18px";
      checkbox.style.height = "18px";

      const text = document.createElement("span");
      text.textContent = `${item.name} (${item.type}) - ID: ${item.id}`;
      text.style.flex = "1";
      text.style.fontSize = "14px";

      label.append(checkbox, text);
      this.panel.appendChild(label);
      this.labelsMap.set(item.id, label);
    });
  }

  handleToggle(e) {
    e.stopPropagation();
    if (!this.panel) return;
    const isOpen = this.panel.style.display !== "none";
    this.panel.style.display = isOpen ? "none" : "flex";
    this.panel.style.flexDirection = "column";
    this.panel.style.minWidth = "300px";
    if (!isOpen && this.searchInput) {
      setTimeout(() => this.searchInput?.focus(), 0);
    }
  }

  handleSearch(e) {
    const query = e.target.value.toLowerCase().trim();
    if (query === "") {
      // Show all items if search is empty
      this.labelsMap.forEach((label) => {
        label.style.display = "flex";
      });
    } else {
      // Filter items based on search query
      this.labelsMap.forEach((label) => {
        const matches = label.dataset.itemSearch.includes(query);
        label.style.display = matches ? "flex" : "none";
      });
    }
  }

  handleDocumentClick(e) {
    if (!this.panel || this.panel.style.display === "none") return;
    const clickedInsidePanel = this.panel.contains(e.target);
    const clickedButton = this.toggleButton?.contains(e.target);
    if (!clickedInsidePanel && !clickedButton) {
      this.panel.style.display = "none";
    }
  }

  handlePanelChange(e) {
    if (!e.target || e.target.type !== "checkbox") return;

    const itemId = e.target.dataset.itemId;

    if (e.target.checked) {
      // Check if we can add more items
      if (this.selectedIds.size >= this.maxItems) {
        e.target.checked = false;
        if (this.toaster) {
          this.toaster.warning(`Max ${this.maxItems} items per loan`);
        }
        return;
      }
      this.selectedIds.add(itemId);
    } else {
      this.selectedIds.delete(itemId);
    }

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
    this.panel
      .querySelectorAll("input[type='checkbox']:checked")
      .forEach((cb) => {
        selected.push({
          id: cb.dataset.itemId,
          name: cb.dataset.itemName,
          type: cb.dataset.itemType,
        });
      });
    return selected;
  }

  getSelectedItems() {
    const selected = [];
    this.selectedIds.forEach((id) => {
      const item = this.items.find((i) => String(i.id) === String(id));
      if (item) {
        selected.push(item);
      }
    });
    return selected;
  }

  renderSelectedChips(selected) {
    if (!this.selectedContainer) return;
    this.selectedContainer.innerHTML = "";

    selected.forEach((item) => {
      const chip = document.createElement("div");
      chip.className = "chip";
      chip.textContent = `${item.name} (${item.type})`;
      chip.style.display = "flex";
      chip.style.alignItems = "center";
      chip.style.gap = "8px";
      chip.style.padding = "6px 10px";
      chip.style.backgroundColor = "#e3f2fd";
      chip.style.borderRadius = "4px";
      chip.style.fontSize = "14px";

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.dataset.removeId = item.id;
      removeBtn.textContent = "×";
      removeBtn.className = "chip-remove";
      removeBtn.style.background = "none";
      removeBtn.style.border = "none";
      removeBtn.style.cursor = "pointer";
      removeBtn.style.padding = "0";
      removeBtn.style.color = "#666";
      removeBtn.style.fontSize = "18px";
      removeBtn.style.lineHeight = "1";
      removeBtn.addEventListener("click", this.handleChipClick.bind(this));

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
