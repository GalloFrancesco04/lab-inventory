import { BaseView } from "../core/BaseView.js";

class MaintenanceView extends BaseView {
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
    this.mediator.searchManager.registerView("maintenance", (query) =>
      this.handleSearch(query),
    );
    this.setupAutoRender(() => this.render());
    this.render();
  }

  setupElements() {
    this.tbody = this.getElement("maintenance-tbody");

    // Detail modal elements
    this.detailModalTitle = this.getElement("maintenance-modal-title");
    this.detailId = this.getElement("maintenance-detail-id");
    this.detailStart = this.getElement("maintenance-detail-start");
    this.detailEnd = this.getElement("maintenance-detail-end");
    this.detailItem = this.getElement("maintenance-detail-item");
    this.detailTech = this.getElement("maintenance-detail-tech");
    this.detailDesc = this.getElement("maintenance-detail-desc");
    this.detailTotal = this.getElement("maintenance-detail-total");
    this.operationsList = this.getElement("operations-list");
    this.addOperationBtn = this.getElement("add-operation-btn");

    this.currentMaintenanceId = null;
  }

  setupModals() {
    this.registerModal("maintenance-detail-modal");
    this.setupModalCloseButtons("maintenance-detail-modal", [
      "maintenance-detail-close",
    ]);
  }

  setupEventListeners() {
    this.addOperationBtn?.addEventListener("click", () => {
      this.addOperation();
    });
  }

  typeBadgeClass(type) {
    if (type === "repair") return "badge badge-danger";
    if (type === "replacement") return "badge badge-warning";
    if (type === "preventive") return "badge badge-success";
    return "badge";
  }

  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.render();
  }

  getFilteredHistory() {
    const history = this.presenter.getMaintenanceHistory();
    if (!this.searchQuery) return history;

    return history.filter((entry) => {
      const searchableText = [
        entry.date,
        entry.itemName,
        entry.type,
        entry.tech || "",
        entry.desc || "",
      ]
        .join(" ")
        .toLowerCase();

      return searchableText.includes(this.searchQuery);
    });
  }

  getSortedHistory() {
    let history = this.getFilteredHistory();

    if (!this.sortColumn) return history;

    return [...history].sort((a, b) => {
      let aVal, bVal;

      switch (this.sortColumn) {
        case "date":
          aVal = a.date;
          bVal = b.date;
          break;
        case "objectName":
          aVal = a.itemName.toLowerCase();
          bVal = b.itemName.toLowerCase();
          break;
        case "type":
          aVal = a.type.toLowerCase();
          bVal = b.type.toLowerCase();
          break;
        case "tech":
          aVal = (a.tech || "").toLowerCase();
          bVal = (b.tech || "").toLowerCase();
          break;
        case "desc":
          aVal = (a.desc || "").toLowerCase();
          bVal = (b.desc || "").toLowerCase();
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
      "#maintenance-view th[data-sortable]",
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
      "#maintenance-view th[data-sortable]",
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
    if (!this.tbody) return;
    this.tbody.innerHTML = "";

    const history = this.getSortedHistory();

    if (history.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML =
        '<td colspan="5" style="text-align: center; color: var(--text-muted);">No maintenance records</td>';
      this.tbody.appendChild(tr);
      return;
    }

    history.forEach((entry) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";
      tr.dataset.maintenanceId = entry.id;

      const idCell = document.createElement("td");
      idCell.textContent = entry.id;

      const startCell = document.createElement("td");
      startCell.textContent = entry.startDate;

      const endCell = document.createElement("td");
      endCell.textContent = entry.endDate;

      const itemCell = document.createElement("td");
      itemCell.textContent = entry.itemName;

      const techCell = document.createElement("td");
      techCell.textContent = entry.tech || "-";

      const descCell = document.createElement("td");
      descCell.textContent = entry.desc || "-";

      const costCell = document.createElement("td");
      costCell.style.fontWeight = "600";
      costCell.style.color = "var(--primary)";
      costCell.textContent = `€${entry.totalCost.toFixed(2)}`;

      tr.append(
        idCell,
        startCell,
        endCell,
        itemCell,
        techCell,
        descCell,
        costCell,
      );
      tr.addEventListener("click", () => this.showMaintenanceDetails(entry.id));
      this.tbody.appendChild(tr);
    });
  }

  showMaintenanceDetails(id) {
    const maintenance = this.presenter.getMaintenanceById(id);
    if (!maintenance) return;

    this.currentMaintenanceId = id;
    this.detailModalTitle.textContent = `Maintenance #${id}`;
    this.detailId.textContent = id;
    this.detailStart.textContent = maintenance.startDate;
    this.detailEnd.textContent = maintenance.endDate;
    this.detailItem.textContent = maintenance.itemName;

    this.detailTech.textContent = maintenance.tech || "-";
    this.detailDesc.textContent = maintenance.desc || "-";
    this.detailTotal.textContent = `€${maintenance.totalCost.toFixed(2)}`;

    this.renderOperations(maintenance.operations || []);
    this.openModal("maintenance-detail-modal");
  }

  renderOperations(operations) {
    if (!this.operationsList) return;
    this.operationsList.innerHTML = "";

    if (operations.length === 0) {
      const empty = document.createElement("div");
      empty.style.padding = "var(--space-4)";
      empty.style.textAlign = "center";
      empty.style.color = "var(--text-muted)";
      empty.textContent = "No operations recorded yet";
      this.operationsList.appendChild(empty);
      return;
    }

    operations.forEach((op, index) => {
      const card = document.createElement("div");
      card.style.padding = "var(--space-4)";
      card.style.background = "#f8f9fa";
      card.style.border = "1px solid #dee2e6";
      card.style.borderRadius = "var(--radius-sm)";
      card.style.boxShadow = "0 1px 3px rgba(0, 0, 0, 0.05)";

      const descLabel = document.createElement("div");
      descLabel.textContent = "DESCRIPTION";
      descLabel.style.fontSize = "0.75rem";
      descLabel.style.fontWeight = "600";
      descLabel.style.color = "var(--text-muted)";
      descLabel.style.marginBottom = "var(--space-1)";
      descLabel.style.letterSpacing = "0.05em";

      const descValue = document.createElement("div");
      descValue.textContent = op.desc;
      descValue.style.fontSize = "0.875rem";
      descValue.style.color = "var(--text-primary)";

      const costLabel = document.createElement("div");
      costLabel.textContent = "COST";
      costLabel.style.fontSize = "0.75rem";
      costLabel.style.fontWeight = "600";
      costLabel.style.color = "var(--text-muted)";
      costLabel.style.marginBottom = "var(--space-1)";
      costLabel.style.letterSpacing = "0.05em";

      const costValue = document.createElement("div");
      costValue.textContent = `€${op.cost.toFixed(2)}`;
      costValue.style.fontSize = "1.125rem";
      costValue.style.fontWeight = "600";
      costValue.style.color = "var(--primary)";

      // Create a container for description and cost side by side
      const descCostContainer = document.createElement("div");
      descCostContainer.style.display = "grid";
      descCostContainer.style.gridTemplateColumns = "1fr 1fr";
      descCostContainer.style.gap = "var(--space-3)";
      descCostContainer.style.marginBottom = "var(--space-3)";

      const descDiv = document.createElement("div");
      descDiv.appendChild(descLabel);
      descDiv.appendChild(descValue);

      const costDiv = document.createElement("div");
      costDiv.appendChild(costLabel);
      costDiv.appendChild(costValue);

      descCostContainer.appendChild(descDiv);
      descCostContainer.appendChild(costDiv);

      // Create a container for dates side by side
      const datesContainer = document.createElement("div");
      datesContainer.style.display = "grid";
      datesContainer.style.gridTemplateColumns = "1fr 1fr";
      datesContainer.style.gap = "var(--space-3)";

      const startDateDiv = document.createElement("div");
      const startLabel = document.createElement("div");
      startLabel.textContent = "START DATE";
      startLabel.style.fontSize = "0.75rem";
      startLabel.style.fontWeight = "600";
      startLabel.style.color = "var(--text-muted)";
      startLabel.style.marginBottom = "var(--space-1)";
      startLabel.style.letterSpacing = "0.05em";

      const startValue = document.createElement("div");
      startValue.textContent = op.startDate || "-";
      startValue.style.fontSize = "0.875rem";
      startValue.style.color = "var(--text-primary)";

      startDateDiv.appendChild(startLabel);
      startDateDiv.appendChild(startValue);

      const endDateDiv = document.createElement("div");
      const endLabel = document.createElement("div");
      endLabel.textContent = "END DATE";
      endLabel.style.fontSize = "0.75rem";
      endLabel.style.fontWeight = "600";
      endLabel.style.color = "var(--text-muted)";
      endLabel.style.marginBottom = "var(--space-1)";
      endLabel.style.letterSpacing = "0.05em";

      const endValue = document.createElement("div");
      endValue.textContent = op.endDate || "-";
      endValue.style.fontSize = "0.875rem";
      endValue.style.color = "var(--text-primary)";

      endDateDiv.appendChild(endLabel);
      endDateDiv.appendChild(endValue);

      datesContainer.appendChild(startDateDiv);
      datesContainer.appendChild(endDateDiv);

      card.appendChild(descCostContainer);
      card.appendChild(datesContainer);

      this.operationsList.appendChild(card);
    });
  }

  addOperation() {
    this.mediator.toaster.info("Add operation feature coming soon!");
  }
}

export function initMaintenanceView(presenter, mediator) {
  return new MaintenanceView(presenter, mediator);
}
