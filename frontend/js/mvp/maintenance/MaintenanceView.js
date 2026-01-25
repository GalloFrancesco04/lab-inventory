import { BaseView } from "../core/BaseView.js";

class MaintenanceView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.searchQuery = "";
    this.sortColumn = null;
    this.sortDirection = null;
    this.setupElements();
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

      const dateCell = document.createElement("td");
      dateCell.textContent = entry.date;

      const itemCell = document.createElement("td");
      itemCell.textContent = entry.itemName;

      const typeCell = document.createElement("td");
      const typeBadge = document.createElement("span");
      typeBadge.className = this.typeBadgeClass(entry.type);
      typeBadge.textContent = entry.type;
      typeCell.appendChild(typeBadge);

      const techCell = document.createElement("td");
      techCell.textContent = entry.tech || "-";

      const descCell = document.createElement("td");
      descCell.textContent = entry.desc || "-";

      tr.append(dateCell, itemCell, typeCell, techCell, descCell);
      this.tbody.appendChild(tr);
    });
  }
}

export function initMaintenanceView(presenter, mediator) {
  return new MaintenanceView(presenter, mediator);
}
