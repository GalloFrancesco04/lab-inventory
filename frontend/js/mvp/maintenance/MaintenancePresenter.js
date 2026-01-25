export class MaintenancePresenter {
  constructor(inventoryPresenter, authPresenter = null) {
    this.inventoryPresenter = inventoryPresenter;
    this.authPresenter = authPresenter;
    this.listeners = [];
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
            totalCost: m.operations
              ? m.operations.reduce((sum, op) => sum + (op.cost || 0), 0)
              : 0,
          });
        });
      }
    });

    // Filter by technician if user is technician
    let filteredHistory = history;
    if (this.authPresenter) {
      const currentUser = this.authPresenter.getCurrentUser();
      if (
        currentUser &&
        currentUser.role === "technician" &&
        currentUser.techName
      ) {
        filteredHistory = history.filter(
          (m) => m.tech === currentUser.techName,
        );
      }
    }

    // Sort by date descending
    filteredHistory.sort((a, b) => new Date(b.date) - new Date(a.date));
    return filteredHistory;
  }

  getMaintenanceById(id) {
    return this.getMaintenanceHistory().find((m) => m.id === id);
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
}
