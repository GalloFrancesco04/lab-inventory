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
    this.setupSorting("#maintenance-view");
    // Register search callback
    this.mediator.searchManager.registerView("maintenance", (query) =>
      this.handleSearch(query),
    );
    this.setupAutoRender(() => this.render());
    this.render();
  }

  setupElements() {
    this.tbody = this.getElement("maintenance-tbody");
    this.endDateHeader = document.querySelector(
      '#maintenance-view th[data-sort-key="endDate"]',
    );
    this.statusHeader = document.querySelector(
      '#maintenance-view th[data-sort-key="status"]',
    );

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
    this.closeMaintenanceBtn = this.getElement("maintenance-close-btn");
    this.closeConfirmAccept = this.getElement(
      "maintenance-close-confirm-accept",
    );
    this.closeConfirmCancel = this.getElement(
      "maintenance-close-confirm-cancel",
    );
    this.closeConfirmClose = this.getElement("maintenance-close-confirm-close");
    this.closeNotesInput = this.getElement("maintenance-close-notes");
    this.closeStillBrokenCheckbox = this.getElement(
      "maintenance-close-still-broken",
    );

    // Add operation form elements
    this.operationForm = this.getElement("add-operation-form");
    this.operationDescInput = this.getElement("operation-desc");
    this.operationCostInput = this.getElement("operation-cost");
    this.operationStartInput = this.getElement("operation-start");
    this.operationEndInput = this.getElement("operation-end");
    this.addOperationCancelBtn = this.getElement("add-operation-cancel");

    this.currentMaintenanceId = null;
  }

  setupModals() {
    this.registerModal("maintenance-detail-modal");
    this.setupModalCloseButtons("maintenance-detail-modal", [
      "maintenance-detail-close",
    ]);

    this.registerModal("add-operation-modal");
    this.setupModalCloseButtons("add-operation-modal", ["add-operation-close"]);

    // Register confirmation modal used before closing a maintenance
    this.registerModal("maintenance-close-confirm-modal", {
      onOpen: () => {
        if (this.closeNotesInput) this.closeNotesInput.value = "";
        if (this.closeStillBrokenCheckbox)
          this.closeStillBrokenCheckbox.checked = false;
      },
    });
  }

  setupEventListeners() {
    this.addOperationBtn?.addEventListener("click", () => {
      this.addOperation();
    });

    this.closeMaintenanceBtn?.addEventListener("click", () => {
      this.openModal("maintenance-close-confirm-modal");
    });

    this.closeConfirmAccept?.addEventListener("click", () => {
      this.handleCloseMaintenance();
    });

    const closeConfirm = () =>
      this.closeModal("maintenance-close-confirm-modal");
    this.closeConfirmCancel?.addEventListener("click", closeConfirm);
    this.closeConfirmClose?.addEventListener("click", closeConfirm);

    this.addOperationCancelBtn?.addEventListener("click", () => {
      this.closeModal("add-operation-modal");
    });

    this.operationForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleAddOperation();
    });
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
    const history = this.getFilteredHistory();
    return this.applySorting(history, {
      date: (entry) => entry.date,
      objectName: (entry) => entry.itemName.toLowerCase(),
      type: (entry) => entry.type.toLowerCase(),
      tech: (entry) => (entry.tech || "").toLowerCase(),
      desc: (entry) => (entry.desc || "").toLowerCase(),
      status: (entry) => (entry.status || "").toLowerCase(),
    });
  }

  render() {
    if (!this.tbody) return;
    this.tbody.innerHTML = "";

    const currentUser = this.presenter.getCurrentUser?.();
    const hideEndDate = currentUser && currentUser.role === "technician";
    const hideStatus = currentUser && currentUser.role === "technician";

    if (this.endDateHeader) {
      this.endDateHeader.style.display = hideEndDate ? "none" : "table-cell";
    }
    if (this.statusHeader) {
      this.statusHeader.style.display = hideStatus ? "none" : "table-cell";
    }

    const history = this.getSortedHistory();

    if (history.length === 0) {
      const tr = document.createElement("tr");
      const totalColumns = 8;
      const hiddenCount = (hideEndDate ? 1 : 0) + (hideStatus ? 1 : 0);
      const colspan = totalColumns - hiddenCount;
      tr.innerHTML = `<td colspan="${colspan}" style="text-align: center; color: var(--text-muted);">No maintenance records</td>`;
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
      if (hideEndDate) endCell.style.display = "none";

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

      const statusCell = document.createElement("td");
      statusCell.textContent = entry.status || "-";
      statusCell.style.fontWeight = "600";
      statusCell.style.color =
        entry.status === "closed" ? "#16a34a" : "#d97706";

      tr.append(idCell, startCell);
      if (!hideEndDate) tr.append(endCell);
      tr.append(itemCell, techCell, descCell, costCell);
      if (!hideStatus) tr.append(statusCell);
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
    this.detailEnd.textContent = maintenance.endDate || "-";
    this.detailItem.textContent = maintenance.itemName;

    this.detailTech.textContent = maintenance.tech || "-";
    this.detailDesc.textContent = maintenance.desc || "-";
    this.detailTotal.textContent = `€${maintenance.totalCost.toFixed(2)}`;

    const currentUser = this.presenter.getCurrentUser?.();
    const canTechnicianClose =
      currentUser &&
      currentUser.role === "technician" &&
      currentUser.techName === maintenance.tech &&
      maintenance.status !== "closed";

    if (this.closeMaintenanceBtn) {
      this.closeMaintenanceBtn.style.display = canTechnicianClose
        ? "inline-flex"
        : "none";
    }

    if (this.addOperationBtn) {
      // Only the assigned technician on an open maintenance can add operations
      this.addOperationBtn.style.display = canTechnicianClose
        ? "inline-flex"
        : "none";
    }

    this.renderOperations(maintenance.operations || []);
    this.openModal("maintenance-detail-modal");
  }

  handleCloseMaintenance() {
    if (!this.currentMaintenanceId) return;
    const maintenance = this.presenter.getMaintenanceById(
      this.currentMaintenanceId,
    );
    if (!maintenance || maintenance.status === "closed") return;

    const closeOptions = {
      notes: this.closeNotesInput?.value.trim() || "",
      stillBroken: Boolean(this.closeStillBrokenCheckbox?.checked),
    };

    if (!closeOptions.notes) {
      this.mediator?.toaster?.error("Please provide a work summary");
      this.closeNotesInput?.focus();
      return;
    }

    const success = this.presenter.closeMaintenance(
      this.currentMaintenanceId,
      closeOptions,
    );
    if (success) {
      const updated = this.presenter.getMaintenanceById(
        this.currentMaintenanceId,
      );
      if (updated) {
        this.detailEnd.textContent = updated.endDate || "-";
      }
      if (this.closeMaintenanceBtn) {
        this.closeMaintenanceBtn.style.display = "none";
      }
      this.closeModal("maintenance-close-confirm-modal");
      this.closeModal("maintenance-detail-modal");
      this.render();
      this.mediator?.toaster?.success("Maintenance closed");
    } else {
      this.closeModal("maintenance-close-confirm-modal");
      this.mediator?.toaster?.error("Unable to close maintenance");
    }
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
    if (!this.currentMaintenanceId) {
      this.mediator.toaster.error("No maintenance selected");
      return;
    }

    // Reset form
    this.operationForm.reset();

    // Set default dates to maintenance dates
    const maintenance = this.presenter.getMaintenanceById(
      this.currentMaintenanceId,
    );
    if (maintenance) {
      // Convert DD/MM/YYYY to YYYY-MM-DD for input[type="date"]
      const formatDateForInput = (dateStr) => {
        const [day, month, year] = dateStr.split("/");
        return `${year}-${month}-${day}`;
      };

      this.operationStartInput.value = formatDateForInput(
        maintenance.startDate,
      );
      const effectiveEndDate = maintenance.endDate || maintenance.startDate;
      this.operationEndInput.value = formatDateForInput(effectiveEndDate);
    }

    this.openModal("add-operation-modal");
  }

  handleAddOperation() {
    const desc = this.operationDescInput.value.trim();
    const cost = parseFloat(this.operationCostInput.value);
    const startDate = this.operationStartInput.value;
    const endDate = this.operationEndInput.value;

    if (!desc || isNaN(cost) || !startDate || !endDate) {
      this.mediator.toaster.error("Please fill all fields correctly");
      return;
    }

    // Convert YYYY-MM-DD to DD/MM/YYYY
    const formatDateForDisplay = (dateStr) => {
      const [year, month, day] = dateStr.split("-");
      return `${day}/${month}/${year}`;
    };

    const newOperation = {
      desc,
      cost,
      startDate: formatDateForDisplay(startDate),
      endDate: formatDateForDisplay(endDate),
    };

    // Add operation via presenter
    const success = this.presenter.addOperation(
      this.currentMaintenanceId,
      newOperation,
    );

    if (success) {
      this.closeModal("add-operation-modal");
      this.mediator.toaster.success("Operation added successfully");

      // Refresh the maintenance detail view
      const maintenance = this.presenter.getMaintenanceById(
        this.currentMaintenanceId,
      );
      if (maintenance) {
        this.detailTotal.textContent = `€${maintenance.totalCost.toFixed(2)}`;
        this.renderOperations(maintenance.operations || []);
      }

      // Re-render table to show updated total cost
      this.render();
    } else {
      this.mediator.toaster.error("Failed to add operation");
    }
  }
}

export function initMaintenanceView(presenter, mediator) {
  return new MaintenanceView(presenter, mediator);
}
