import { BaseView } from "../core/BaseView.js";

function statusBadgeClass(status) {
  if (status === "Available") return "badge badge-success";
  if (status === "Loaned") return "badge badge-warning";
  return "badge";
}

class InventoryView extends BaseView {
  constructor(presenter) {
    super(presenter);
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
  }

  setupElements() {
    // Modal
    this.itemTitle = this.getElement("item-modal-title");
    this.itemName = this.getElement("item-name");
    this.itemType = this.getElement("item-type");
    this.itemStatus = this.getElement("item-status");
    this.itemLocation = this.getElement("item-location");
    this.maintTbody = this.getElement("item-maintenance-tbody");

    // Buttons
    this.sendToMaintBtn = this.getElement("send-to-maintenance-btn");

    // Table rows (dynamic)
    this.rows = document.querySelectorAll("#inventory-view [data-item-id]");
  }

  setupModals() {
    this.registerModal("item-detail-modal");
    this.setupModalCloseButtons("item-detail-modal", ["item-detail-close"]);
  }

  setupEventListeners() {
    this.rows.forEach((row) => {
      row.addEventListener("click", () => {
        this.openItemModal(row.dataset.itemId);
      });
    });

    this.sendToMaintBtn?.addEventListener("click", () => {
      console.log("Send to maintenance (mock)");
    });
  }

  typeBadgeClass(type) {
    if (type === "Repair") return "badge badge-danger";
    if (type === "Replacement") return "badge badge-warning";
    if (type === "Preventive") return "badge badge-success";
    return "badge";
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

export function initInventoryView(presenter) {
  return new InventoryView(presenter);
}
