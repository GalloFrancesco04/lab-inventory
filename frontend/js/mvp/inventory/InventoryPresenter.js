import { BasePresenter } from "../core/BasePresenter.js";
import { ApiStore, request } from "../../api/ApiClient.js";
import {
  emailToDisplayName,
  stateToStatus,
} from "../../utils/Transformers.js";

export class InventoryPresenter extends BasePresenter {
  constructor() {
    super([]);
    this.db = ApiStore.getInstance();
  }

  subscribe(callback) {
    this.listeners.push(callback);
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getItemById(id) {
    const object = this.db.getObjectById(id);
    if (!object) return null;

    const objectType = this.db.getObjectTypeByCode(object.objectTypeCode);
    const maintenanceRecords = this.db.getMaintenanceByObjectId(id);

    // Map maintenance records with operations
    const maintenance = maintenanceRecords.map((m) => {
      const operations = this.db.getOperationsByMaintenanceId(m.id);
      const tech = this.db.getUserById(m.technicianId);

      return {
        id: m.id,
        startDate: m.startDate,
        endDate: m.endDate || "",
        type: m.type,
        tech: tech ? emailToDisplayName(tech.email) : "Unknown",
        phoneNumber: m.phoneNumber,
        desc: m.description,
        operations: operations.map((op) => ({
          desc: op.description,
          cost: op.cost,
          startDate: op.startDate,
          endDate: op.endDate || "",
        })),
        status: m.endDate ? "closed" : "open",
      };
    });

    return {
      id: object.id,
      name: objectType ? objectType.name : "Unknown",
      type: objectType ? objectType.name : "Unknown",
      brand: objectType ? objectType.brand : "Unknown",
      model: objectType ? objectType.model : "Unknown",
      status: stateToStatus(object.state),
      location: object.location,
      imageUrl: null,
      maintenance: maintenance,
    };
  }

  getItems() {
    const objects = this.db.getObjects();
    return objects.map((obj) => this.getItemById(obj.id));
  }

  getLocations() {
    const objects = this.db.getObjects();
    const locations = objects.map((obj) => obj.location).filter(Boolean);
    return [...new Set(locations)];
  }

  getItemTypes() {
    const objectTypes = this.db.getObjectTypes();
    return objectTypes.map((type) => {
      const objects = this.db.getObjectsByTypeCode(type.code);
      const availableCount = objects.filter((obj) => obj.state === 1).length;

      return {
        code: type.code,
        name: type.name,
        description: type.description,
        model: type.model,
        brand: type.brand,
        quantity: availableCount,
      };
    });
  }

  async addObjectType(typeData) {
    if (!typeData || !typeData.name || !typeData.code) return null;

    // Check if code already exists
    if (this.db.getObjectTypeByCode(typeData.code)) {
      return null;
    }

    await request("POST", "/objecttypes", {
      code: typeData.code,
      name: typeData.name,
      description: typeData.description || "",
      model: typeData.model || "Various",
      brand: typeData.brand || "Various",
    });

    // Create OBJECT instances based on quantity
    for (let i = 0; i < (typeData.quantity || 0); i++) {
      await request("POST", "/objects", {
        object_type_code: typeData.code,
        location: typeData.location || "",
      });
    }

    await this.db.load();
    this.notifyListeners();
    return this.db.getObjectTypeByCode(typeData.code);
  }

  async addObjectInstance(typeCode, location = "") {
    if (!this.db.getObjectTypeByCode(typeCode)) return null;

    const { id } = await request("POST", "/objects", {
      object_type_code: typeCode,
      location: location,
    });

    await this.db.load();
    this.notifyListeners();
    return this.db.getObjectById(id);
  }

  getTechnicians() {
    const users = this.db.getTechnicians();
    return users.map((u) => ({
      id: u.id,
      email: u.email,
      name: u.email
        .split("@")[0]
        .replace(".", " ")
        .replace(/\b\w/g, (l) => l.toUpperCase()),
    }));
  }

  async createMaintenance(itemId, description, technicianId) {
    if (!this.db.getObjectById(itemId)) return null;

    await request("POST", "/maintenance", {
      object_id: itemId,
      description: description,
      technician_id: parseInt(technicianId),
      type: "repair",
    });

    await this.db.load();
    this.notifyListeners();
    return true;
  }

  async updateItem(id, payload) {
    const object = this.db.getObjectById(id);
    if (!object) throw new Error("Item not found");

    if (payload.location !== undefined && payload.location !== object.location) {
      await request("PUT", `/objects/${id}`, { location: payload.location });
    }

    // The backend only toggles out of service; other states follow loans and maintenance
    const status = stateToStatus(object.state);
    if (payload.status !== undefined && payload.status !== status) {
      await request("PUT", `/objects/${id}/toggle-service`);
    }

    await this.db.load();
    this.notifyListeners();
    return true;
  }
}
