import { BaseView } from "../core/BaseView.js";

class MaintenanceView extends BaseView {
  constructor(presenter) {
    super(presenter);
    this.setupElements();
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

  render() {
    if (!this.tbody) return;
    this.tbody.innerHTML = "";

    const history = this.presenter.getMaintenanceHistory();

    if (history.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML =
        '<td colspan="5" style="text-align: center; color: var(--text-muted);">No maintenance records</td>';
      this.tbody.appendChild(tr);
      return;
    }

    history.forEach((entry) => {
      const tr = document.createElement("tr");

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

export function initMaintenanceView(presenter) {
  return new MaintenanceView(presenter);
}
