import { BasePresenter } from "../core/BasePresenter.js";

export class MaintenancePresenter extends BasePresenter {
  constructor(inventoryPresenter, authPresenter = null) {
    super();
    this.inventoryPresenter = inventoryPresenter;
    this.authPresenter = authPresenter;
  }

  getCurrentUser() {
    return this.authPresenter ? this.authPresenter.getCurrentUser() : null;
  }

  subscribe(callback) {
    this.listeners.push(callback);
    // Also subscribe to inventory changes so maintenance updates reflect inventory changes
    this.inventoryPresenter.subscribe(callback);
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getMaintenanceHistory() {
    const items = this.inventoryPresenter.getItems();
    const history = [];

    items.forEach((item) => {
      if (item.maintenance && item.maintenance.length > 0) {
        item.maintenance.forEach((m) => {
          const status = m.status || (m.endDate ? "closed" : "open");
          history.push({
            id: m.id || `${item.id}-${m.startDate}`,
            startDate: m.startDate,
            endDate: m.endDate,
            itemId: item.id,
            itemName: item.name,
            type: m.type,
            tech: m.tech,
            desc: m.desc,
            operations: m.operations || [],
            status,
            totalCost: m.operations
              ? m.operations.reduce((sum, op) => sum + (op.cost || 0), 0)
              : 0,
          });
        });
      }
    });

    // Filter by technician if user is technician
    let filteredHistory = history;
    const currentUser = this.getCurrentUser();
    if (
      currentUser &&
      currentUser.role === "technician" &&
      currentUser.techName
    ) {
      filteredHistory = history.filter(
        (m) => m.tech === currentUser.techName && m.status !== "closed",
      );
    }

    // Sort by date descending
    filteredHistory.sort(
      (a, b) => new Date(b.startDate) - new Date(a.startDate),
    );
    return filteredHistory;
  }

  getMaintenanceById(id) {
    const items = this.inventoryPresenter.getItems();
    for (const item of items) {
      if (item.maintenance && item.maintenance.length > 0) {
        const found = item.maintenance.find(
          (m) => (m.id || `${item.id}-${m.startDate}`) === id,
        );
        if (found) {
          const status = found.status || (found.endDate ? "closed" : "open");
          return {
            id: found.id || `${item.id}-${found.startDate}`,
            startDate: found.startDate,
            endDate: found.endDate,
            itemId: item.id,
            itemName: item.name,
            type: found.type,
            tech: found.tech,
            desc: found.desc,
            operations: found.operations || [],
            status,
            totalCost: found.operations
              ? found.operations.reduce((sum, op) => sum + (op.cost || 0), 0)
              : 0,
          };
        }
      }
    }
    return undefined;
  }

  addOperation(maintenanceId, operation) {
    const items = this.inventoryPresenter.getItems();

    // Find the item and maintenance record
    for (const item of items) {
      if (item.maintenance && item.maintenance.length > 0) {
        const maintenanceRecord = item.maintenance.find(
          (m) => (m.id || `${item.id}-${m.startDate}`) === maintenanceId,
        );

        if (maintenanceRecord) {
          // Add the operation
          if (!maintenanceRecord.operations) {
            maintenanceRecord.operations = [];
          }
          maintenanceRecord.operations.push(operation);

          // Notify listeners to update views
          this.notifyListeners();
          return true;
        }
      }
    }

    return false;
  }

  closeMaintenance(maintenanceId, options = {}) {
    const items = this.inventoryPresenter.getItems();
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, "0");
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const yyyy = today.getFullYear();
    const todayStr = `${dd}/${mm}/${yyyy}`;

    for (const item of items) {
      if (item.maintenance && item.maintenance.length > 0) {
        const maintenanceRecord = item.maintenance.find(
          (m) => (m.id || `${item.id}-${m.startDate}`) === maintenanceId,
        );

        if (maintenanceRecord) {
          maintenanceRecord.endDate = todayStr;
          maintenanceRecord.status = "closed";
          if (options.notes) {
            maintenanceRecord.closeNotes = options.notes;
          }
          if (options.stillBroken) {
            item.status = "outofservice";
          }
          this.notifyListeners();
          return true;
        }
      }
    }

    return false;
  }
}
