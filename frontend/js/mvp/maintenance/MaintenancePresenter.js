export class MaintenancePresenter {
  constructor(inventoryPresenter) {
    this.inventoryPresenter = inventoryPresenter;
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
            id: m.id || `${item.id}-${m.date}`, // Generate unique ID
            date: m.date,
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

    // Sort by date descending
    history.sort((a, b) => new Date(b.date) - new Date(a.date));
    return history;
  }

  getMaintenanceById(id) {
    return this.getMaintenanceHistory().find((m) => m.id === id);
  }
}
