import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";

export class MaintenancePresenter extends BasePresenter {
  constructor(inventoryPresenter, authPresenter = null) {
    super([]);
    this.db = MockDatabase.getInstance();
    this.inventoryPresenter = inventoryPresenter;
    this.authPresenter = authPresenter;
  }

  getCurrentUser() {
    return this.authPresenter ? this.authPresenter.getCurrentUser() : null;
  }

  subscribe(callback) {
    this.listeners.push(callback);
    // Also subscribe to inventory changes so maintenance updates reflect inventory changes
    if (this.inventoryPresenter) {
      this.inventoryPresenter.subscribe(callback);
    }
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getMaintenanceHistory() {
    const maintenanceRecords = this.db.getMaintenanceRecords();
    const history = [];

    maintenanceRecords.forEach((m) => {
      const object = this.db.getObjectById(m.objectId);
      const objectType = object
        ? this.db.getObjectTypeByCode(object.objectTypeCode)
        : null;
      const tech = this.db.getUserById(m.technicianId);
      const operations = this.db.getOperationsByMaintenanceId(m.id);

      const totalCost = operations.reduce((sum, op) => sum + (op.cost || 0), 0);
      const status = m.endDate ? "closed" : "open";

      history.push({
        id: m.id,
        startDate: m.startDate,
        endDate: m.endDate || "",
        itemId: m.objectId,
        itemName: objectType ? objectType.name : "Unknown",
        type: m.type,
        tech: tech
          ? tech.email
              .split("@")[0]
              .replace(".", " ")
              .replace(/\b\w/g, (l) => l.toUpperCase())
          : "Unknown",
        phoneNumber: m.phoneNumber,
        desc: m.description,
        operations: operations.map((op) => ({
          desc: op.description,
          cost: op.cost,
          startDate: op.startDate,
          endDate: op.endDate || "",
        })),
        status,
        totalCost,
      });
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
    const m = this.db.getMaintenanceById(id);
    if (!m) return undefined;

    const object = this.db.getObjectById(m.objectId);
    const objectType = object
      ? this.db.getObjectTypeByCode(object.objectTypeCode)
      : null;
    const tech = this.db.getUserById(m.technicianId);
    const operations = this.db.getOperationsByMaintenanceId(m.id);

    const totalCost = operations.reduce((sum, op) => sum + (op.cost || 0), 0);
    const status = m.endDate ? "closed" : "open";

    return {
      id: m.id,
      startDate: m.startDate,
      endDate: m.endDate || "",
      itemId: m.objectId,
      itemName: objectType ? objectType.name : "Unknown",
      type: m.type,
      tech: tech
        ? tech.email
            .split("@")[0]
            .replace(".", " ")
            .replace(/\b\w/g, (l) => l.toUpperCase())
        : "Unknown",
      phoneNumber: m.phoneNumber,
      desc: m.description,
      operations: operations.map((op) => ({
        desc: op.description,
        cost: op.cost,
        startDate: op.startDate,
        endDate: op.endDate || "",
      })),
      status,
      totalCost,
    };
  }

  addOperation(maintenanceId, operation) {
    const today = new Date().toISOString().split("T")[0];

    const newOperation = this.db.addOperation({
      maintenanceId: maintenanceId,
      description: operation.desc || "",
      startDate: operation.startDate || today,
      endDate: operation.endDate || null,
      cost: operation.cost || 0,
    });

    if (newOperation) {
      this.notifyListeners();
      return true;
    }

    return false;
  }

  updateOperation(maintenanceId, operationIndex, updates) {
    const operations = this.db.getOperationsByMaintenanceId(maintenanceId);

    if (operationIndex < 0 || operationIndex >= operations.length) {
      return false;
    }

    const operation = operations[operationIndex];

    const updatedOperation = this.db.updateOperation(operation.id, {
      description: updates.desc,
      cost:
        typeof updates.cost === "number" && !Number.isNaN(updates.cost)
          ? updates.cost
          : operation.cost,
      startDate: updates.startDate || operation.startDate,
      endDate: updates.endDate || operation.endDate,
    });

    if (updatedOperation) {
      this.notifyListeners();
      return true;
    }

    return false;
  }

  closeMaintenance(maintenanceId, options = {}) {
    const today = new Date().toISOString().split("T")[0];

    const maintenance = this.db.getMaintenanceById(maintenanceId);
    if (!maintenance) return false;

    // Update maintenance record
    this.db.updateMaintenance(maintenanceId, {
      endDate: today,
    });

    // Update object state
    if (options.stillBroken) {
      this.db.updateObject(maintenance.objectId, { state: 5 }); // outofservice
    } else {
      this.db.updateObject(maintenance.objectId, { state: 1 }); // available
    }

    // Update via inventory presenter if available
    if (this.inventoryPresenter) {
      try {
        this.inventoryPresenter.updateItem(maintenance.objectId, {
          status: options.stillBroken ? "outofservice" : "available",
        });
      } catch (error) {
        console.error("Unable to update item status", error);
      }
    }

    this.notifyListeners();
    return true;
  }
}
