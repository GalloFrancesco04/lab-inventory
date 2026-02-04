import { getItemStatusBadgeClass } from "../../../utils/ui/Badges.js";

export class ObjectListModal {
  constructor({
    modalId,
    openModal,
    presenter,
    mediator,
    onObjectClick,
    onNewInstance,
  }) {
    this.modalId = modalId;
    this.openModal = openModal;
    this.presenter = presenter;
    this.mediator = mediator;
    this.onObjectClick = onObjectClick;
    this.onNewInstance = onNewInstance;
    this.currentTypeCode = null;
    this.currentTypeName = null;

    // Setup new instance button listener
    this.setupNewInstanceButton();

    // Subscribe to presenter changes to update the modal when data changes
    this.presenter?.subscribe(() => this.refreshCurrentView());
  }

  destroy() {
    // Remove event listeners
    const newInstanceBtn = document.getElementById("new-instance-btn");
    if (newInstanceBtn) {
      newInstanceBtn.removeEventListener("click", this.newInstanceHandler);
    }
  }

  setupNewInstanceButton() {
    const newInstanceBtn = document.getElementById("new-instance-btn");
    if (newInstanceBtn) {
      this.newInstanceHandler = () => {
        if (
          this.onNewInstance &&
          this.currentTypeCode &&
          this.currentTypeName
        ) {
          this.onNewInstance(this.currentTypeCode, this.currentTypeName);
        }
      };
      newInstanceBtn.addEventListener("click", this.newInstanceHandler);
    }
  }

  refreshCurrentView() {
    // Re-render the current type if modal is visible
    if (this.currentTypeCode) {
      // Find the current type name from types list
      const typeObj = this.presenter
        .getItemTypes()
        .find((t) => t.code === this.currentTypeCode);
      if (typeObj) {
        this.renderObjectList(this.currentTypeCode, typeObj.name);
      }
    }
  }

  show(typeCode, typeName) {
    this.currentTypeCode = typeCode;
    this.currentTypeName = typeName;
    this.renderObjectList(typeCode, typeName);
    if (this.openModal && this.modalId) this.openModal(this.modalId);
  }

  renderObjectList(typeCode, typeName) {
    const modal = document.getElementById(this.modalId);
    if (!modal) return;

    // Get all items and filter by type
    let allItems = [];
    try {
      allItems = this.presenter.getItems();
    } catch (error) {
      return;
    }

    const items = allItems.filter((item) => {
      const typeObj = this.presenter
        .getItemTypes()
        .find((t) => t.code === typeCode);
      return typeObj && item.type === typeObj.name;
    });

    // Update modal title
    const title = modal.querySelector(".modal-title");
    if (title) title.textContent = `${typeName} Items`;

    // Get or create content container
    let contentDiv = modal.querySelector(".object-list-content");
    if (!contentDiv) {
      contentDiv = document.createElement("div");
      contentDiv.className = "object-list-content";
      const modalContent = modal.querySelector(".modal-content");
      if (modalContent) modalContent.appendChild(contentDiv);
    }

    contentDiv.innerHTML = "";

    if (items.length === 0) {
      const empty = document.createElement("div");
      empty.style.color = "var(--text-muted)";
      empty.style.textAlign = "center";
      empty.style.padding = "var(--space-4)";
      empty.textContent = "No items in this category";
      contentDiv.appendChild(empty);
      return;
    }

    const table = document.createElement("table");
    table.className = "modal-table";

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");
    ["Item ID", "Location", "Status"].forEach((text) => {
      const th = document.createElement("th");
      th.textContent = text;
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement("tbody");
    items.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "modal-table-row";
      tr.dataset.itemId = item.id;

      const idCell = document.createElement("td");
      idCell.textContent = item.id;

      const locCell = document.createElement("td");
      locCell.textContent = item.location;

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = getItemStatusBadgeClass(item.status);
      statusBadge.textContent = item.status;
      statusCell.appendChild(statusBadge);

      tr.append(idCell, locCell, statusCell);
      tr.addEventListener("click", () => {
        if (this.onObjectClick) this.onObjectClick(item.id);
      });
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    contentDiv.appendChild(table);
  }
}
