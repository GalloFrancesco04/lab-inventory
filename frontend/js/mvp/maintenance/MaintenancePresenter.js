import { BasePresenter } from "../core/BasePresenter.js";
import { ApiStore, request } from "../../api/ApiClient.js";

export class MaintenancePresenter extends BasePresenter {
  constructor(inventoryPresenter, authPresenter = null) {
    super([]);
    this.db = ApiStore.getInstance();
    this.inventoryPresenter = inventoryPresenter;
    this.authPresenter = authPresenter;
  }

  getCurrentUser() {
    return this.authPresenter ? this.authPresenter.getCurrentUser() : null;
  }

  getCurrentUserRole() {
    const user = this.getCurrentUser();
    return user ? user.role : null;
  }

  isTechnician() {
    return this.getCurrentUserRole() === "technician";
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
        tech: tech ? tech.email : "Unknown",
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
    if (currentUser && currentUser.role === "technician" && currentUser.email) {
      filteredHistory = history.filter(
        (m) => m.tech === currentUser.email && m.status !== "closed",
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
      tech: tech ? tech.email : "Unknown",
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

  getMaintenancesByObjectId(objectId) {
    const allMaintenances = this.db.getMaintenanceRecords();
    const itemMaintenances = allMaintenances
      .filter((m) => m.objectId === objectId)
      .map((m) => {
        const tech = this.db.getUserById(m.technicianId);
        const operations = this.db.getOperationsByMaintenanceId(m.id);
        const totalCost = operations.reduce(
          (sum, op) => sum + (op.cost || 0),
          0,
        );
        const status = m.endDate ? "closed" : "open";

        return {
          id: m.id,
          startDate: m.startDate,
          endDate: m.endDate || "",
          tech: tech ? tech.email : "Unknown",
          desc: m.description,
          status,
          totalCost,
        };
      });

    return itemMaintenances;
  }

  async addOperation(maintenanceId, operation) {
    const today = new Date().toISOString().split("T")[0];

    const { id } = await request("POST", "/operations", {
      maintenance_id: maintenanceId,
      description: operation.desc || "",
      start_date: operation.startDate || today,
      cost: operation.cost || 0,
    });

    // The end date can only be set by updating the operation
    if (operation.endDate) {
      await request("PUT", `/operations/${id}`, {
        description: operation.desc || "",
        end_date: operation.endDate,
        cost: operation.cost || 0,
      });
    }

    await this.db.load();
    this.notifyListeners();
    return true;
  }

  async updateOperation(maintenanceId, operationIndex, updates) {
    const operations = this.db.getOperationsByMaintenanceId(maintenanceId);

    if (operationIndex < 0 || operationIndex >= operations.length) {
      return false;
    }

    const operation = operations[operationIndex];

    await request("PUT", `/operations/${operation.id}`, {
      description: updates.desc,
      cost:
        typeof updates.cost === "number" && !Number.isNaN(updates.cost)
          ? updates.cost
          : operation.cost,
      end_date: updates.endDate || operation.endDate,
    });

    await this.db.load();
    this.notifyListeners();
    return true;
  }

  async closeMaintenance(maintenanceId, options = {}) {
    if (!this.db.getMaintenanceById(maintenanceId)) return false;

    await request("PUT", `/maintenance/${maintenanceId}`, {
      damaged: options.stillBroken ? 1 : 0,
    });

    await this.db.load();
    this.notifyListeners();
    return true;
  }
}
