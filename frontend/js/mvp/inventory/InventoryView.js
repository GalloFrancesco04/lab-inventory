import { BaseView } from "../core/BaseView.js";

function statusBadgeClass(status) {
  if (status === "available") return "badge badge-success";
  if (status === "loaned") return "badge badge-warning";
  if (status === "maintenance") return "badge badge-warning";
  if (status === "outofservice") return "badge badge-danger";
  return "badge";
}

class InventoryView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.searchQuery = "";
    this.sortColumn = null;
    this.sortDirection = null;
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    this.setupSorting();
    // Register search callback
    this.mediator.searchManager.registerView("inventory", (query) =>
      this.handleSearch(query),
    );
    // Auto re-render on presenter data changes
    this.setupAutoRender(() => this.render());
    this.render();
  }

  setupElements() {
    // New Item Modal
    this.newItemForm = this.getElement("new-item-form");
    this.newItemType = this.getElement("new-item-type");
    this.newItemName = this.getElement("new-item-name");
    this.newItemLocation = this.getElement("new-item-location");
    this.newItemStatus = this.getElement("new-item-status");
    this.newItemBtn = this.getElement("new-item-btn");

    // Detail Modal
    this.itemTitle = this.getElement("item-modal-title");
    this.itemName = this.getElement("item-name");
    this.itemType = this.getElement("item-type");
    this.itemStatus = this.getElement("item-status");
    this.itemLocation = this.getElement("item-location");
    this.maintTbody = this.getElement("item-maintenance-tbody");

    // Buttons
    this.sendToMaintBtn = this.getElement("send-to-maintenance-btn");
  }

  setupModals() {
    this.registerModal("new-item-modal", {
      onOpen: () => {
        this.populateObjectTypes();
        this.clearFormErrors(this.newItemForm);
      },
      onClose: () => this.resetNewItemForm(),
    });
    this.setupModalCloseButtons("new-item-modal", [
      "new-item-close",
      "new-item-cancel",
    ]);

    this.registerModal("item-detail-modal");
    this.setupModalCloseButtons("item-detail-modal", ["item-detail-close"]);
  }

  populateObjectTypes() {
    const types = this.presenter.getItemTypes();
    this.newItemType.innerHTML = '<option value="">Select a type...</option>';
    types.forEach((type) => {
      const option = document.createElement("option");
      option.value = type.code;
      option.textContent = type.name;
      this.newItemType.appendChild(option);
    });
  }

  setupEventListeners() {
    // New Item Button
    this.newItemBtn?.addEventListener("click", () => {
      this.openModal("new-item-modal");
    });

    // New Item Form Submit
    this.newItemForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.clearFormErrors(this.newItemForm);

      const itemData = {
        typeCode: this.newItemType.value.trim(),
        name: this.newItemName.value.trim(),
        location: this.newItemLocation.value.trim(),
        status: this.newItemStatus.value,
      };

      let hasError = false;

      if (!itemData.typeCode) {
        this.showFieldError(this.newItemType, "Please select an object type");
        hasError = true;
      }

      if (!itemData.name) {
        this.showFieldError(this.newItemName, "Name is required");
        hasError = true;
      }

      if (!itemData.location) {
        this.showFieldError(
          this.newItemLocation,
          "Location is required (e.g., Lab 1)",
        );
        hasError = true;
      }

      if (hasError) {
        // Focus first error field
        const firstError = this.newItemForm.querySelector(".input-error");
        if (firstError) firstError.focus();
        return;
      }

      const newItem = this.presenter.addItem(itemData);
      if (newItem) {
        this.mediator.toaster.success(
          `Item "${newItem.name}" added successfully`,
        );
      }
      this.closeModal("new-item-modal");
    });

    // Event delegation for table rows
    const tbody = document.querySelector("#inventory-view tbody");
    tbody?.addEventListener("click", (e) => {
      const row = e.target.closest("[data-item-id]");
      if (row) {
        this.openItemModal(row.dataset.itemId);
      }
    });

    this.sendToMaintBtn?.addEventListener("click", () => {
      this.mediator.logger.mock("Send to maintenance");
    });
  }

  typeBadgeClass(type) {
    if (type === "repair") return "badge badge-danger";
    if (type === "replacement") return "badge badge-warning";
    if (type === "preventive") return "badge badge-success";
    return "badge";
  }

  resetNewItemForm() {
    this.newItemForm.reset();
    this.newItemType.focus();
  }

  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.render();
  }

  getFilteredItems() {
    const items = this.presenter.getItems();
    if (!this.searchQuery) return items;

    return items.filter((item) => {
      const searchableText = [
        item.id,
        item.name,
        item.type,
        item.status,
        item.location,
      ]
        .join(" ")
        .toLowerCase();

      return searchableText.includes(this.searchQuery);
    });
  }

  getSortedItems() {
    let items = this.getFilteredItems();

    if (!this.sortColumn) return items;

    return [...items].sort((a, b) => {
      let aVal, bVal;

      switch (this.sortColumn) {
        case "id":
          aVal = a.id;
          bVal = b.id;
          break;
        case "name":
          aVal = a.name.toLowerCase();
          bVal = b.name.toLowerCase();
          break;
        case "type":
          aVal = a.type.toLowerCase();
          bVal = b.type.toLowerCase();
          break;
        case "status":
          aVal = a.status.toLowerCase();
          bVal = b.status.toLowerCase();
          break;
        case "location":
          aVal = a.location.toLowerCase();
          bVal = b.location.toLowerCase();
          break;
        default:
          return 0;
      }

      if (aVal < bVal) return this.sortDirection === "asc" ? -1 : 1;
      if (aVal > bVal) return this.sortDirection === "asc" ? 1 : -1;
      return 0;
    });
  }

  setupSorting() {
    const headers = document.querySelectorAll(
      "#inventory-view th[data-sortable]",
    );
    headers.forEach((header) => {
      header.addEventListener("click", () => {
        const sortKey = header.dataset.sortKey;
        this.handleSort(sortKey, header);
      });
    });
  }

  handleSort(column, headerElement) {
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

    const allHeaders = document.querySelectorAll(
      "#inventory-view th[data-sortable]",
    );
    allHeaders.forEach((h) => {
      h.classList.remove("sorted-asc", "sorted-desc");
    });

    if (this.sortDirection) {
      headerElement.classList.add(`sorted-${this.sortDirection}`);
    }

    this.render();
  }

  render() {
    const tbody = document.querySelector("#inventory-view tbody");
    tbody.innerHTML = "";
    this.getSortedItems().forEach((item) => {
      const tr = document.createElement("tr");
      tr.setAttribute("data-item-id", item.id);

      const idCell = document.createElement("td");
      idCell.textContent = item.id;

      const nameCell = document.createElement("td");
      nameCell.textContent = item.name;

      const typeCell = document.createElement("td");
      typeCell.textContent = item.type;

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = statusBadgeClass(item.status);
      statusBadge.textContent = item.status;
      statusCell.appendChild(statusBadge);

      const locationCell = document.createElement("td");
      locationCell.textContent = item.location;

      tr.append(idCell, nameCell, typeCell, statusCell, locationCell);
      tr.style.cursor = "pointer";
      tbody.appendChild(tr);
    });
  }

  renderMaintenance(list) {
    this.maintTbody.innerHTML = "";
    if (!list || list.length === 0) {
      const div = document.createElement("div");
      div.style.color = "var(--text-muted)";
      div.style.padding = "var(--space-3)";
      div.textContent = "No maintenance yet";
      this.maintTbody.appendChild(div);
      return;
    }
    list.forEach((m) => {
      const div = document.createElement("div");
      div.className = "maintenance-entry";
      div.innerHTML = `
        <div class="maint-row">
          <div class="maint-label">Date</div>
          <div class="maint-value">${m.date}</div>
        </div>
        <div class="maint-row">
          <div class="maint-label">Technician</div>
          <div class="maint-value">${m.tech || "-"}</div>
        </div>
        <div class="maint-row">
          <div class="maint-label">Description</div>
          <div class="maint-value">${m.desc || "-"}</div>
        </div>
      `;
      this.maintTbody.appendChild(div);
    });
  }

  openItemModal(itemId) {
    const item = this.presenter.getItemById(itemId);
    if (!item) return;
    this.itemTitle.textContent = `Item #${item.id}`;
    this.itemName.textContent = item.name;
    this.itemType.textContent = item.type;
    this.itemLocation.textContent = item.location;
    this.itemStatus.className = statusBadgeClass(item.status);
    this.itemStatus.textContent = item.status;
    this.renderMaintenance(item.maintenance);
    this.openModal("item-detail-modal");
  }
}

export function initInventoryView(presenter, mediator) {
  return new InventoryView(presenter, mediator);
}
