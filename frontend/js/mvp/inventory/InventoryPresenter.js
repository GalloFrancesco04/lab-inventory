import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";
import {
  emailToDisplayName,
  stateToStatus,
  statusToState,
} from "../../utils/transformers.js";

export class InventoryPresenter extends BasePresenter {
  constructor(mediator = null) {
    super([]);
    this.db = MockDatabase.getInstance();
    this.mediator = mediator; // For listening to cross-module events

    // Register event listeners for cross-module communication
    if (this.mediator) {
      this.registerEventListeners();
    }
  }

  registerEventListeners() {
    // Listen for loan creation to update item states
    if (this.mediator) {
      this.mediator.on("loan:created", ({ items }) => {
        items.forEach((item) => {
          try {
            this.updateItem(item.id, { status: "loaned" });
          } catch (error) {
            console.error(
              `Unable to update item ${item.id} on loan:created`,
              error,
            );
          }
        });
      });

      // Listen for loan closure to revert item states
      this.mediator.on("loan:closed", ({ items, damagedItems }) => {
        items.forEach((item) => {
          try {
            // Check if item is damaged
            const isDamaged = damagedItems?.some((d) => d.id === item.id);
            const newStatus = isDamaged ? "damaged" : "available";
            this.updateItem(item.id, { status: newStatus });
          } catch (error) {
            console.error(
              `Unable to update item ${item.id} on loan:closed`,
              error,
            );
          }
        });
      });

      // Listen for maintenance completion to revert item to available or damaged
      this.mediator.on("maintenance:closed", ({ itemId, stillBroken }) => {
        try {
          const newStatus = stillBroken ? "damaged" : "available";
          this.updateItem(itemId, { status: newStatus });
        } catch (error) {
          console.error(
            `Unable to update item ${itemId} on maintenance:closed`,
            error,
          );
        }
      });
    }
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

  addObjectType(typeData) {
    if (!typeData || !typeData.name || !typeData.code) return null;

    // Check if code already exists
    if (this.db.getObjectTypeByCode(typeData.code)) {
      return null;
    }

    const newType = this.db.addObjectType({
      code: typeData.code,
      name: typeData.name,
      description: typeData.description || "",
      model: typeData.model || "Various",
      brand: typeData.brand || "Various",
      quantity: typeData.quantity || 0,
    });

    // Create OBJECT instances based on quantity
    if (typeData.quantity && typeData.quantity > 0) {
      for (let i = 0; i < typeData.quantity; i++) {
        this.db.addObject({
          objectTypeCode: newType.code,
          state: 1, // available
          location: typeData.location || "",
        });
      }
    }

    this.notifyListeners();
    return newType;
  }

  addObjectInstance(typeCode, location = "") {
    const typeObj = this.db.getObjectTypeByCode(typeCode);
    if (!typeObj) return null;

    const newObject = this.db.addObject({
      objectTypeCode: typeCode,
      state: 1, // available
      location: location,
    });

    this.notifyListeners();
    return newObject;
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

  createMaintenance(itemId, description, technicianId) {
    const object = this.db.getObjectById(itemId);
    if (!object) return null;

    // Get technician by ID (technicianId is passed from form value)
    const tech = this.db.getUserById(parseInt(technicianId));
    if (!tech) return null;

    const today = new Date().toISOString().split("T")[0];

    const newMaintenance = this.db.addMaintenance({
      objectId: itemId,
      description: description,
      startDate: today,
      endDate: null,
      type: "repair",
      technicianId: tech.id,
      phoneNumber: tech.phoneNumber || "",
      cost: 0,
    });

    // Update object state to maintenance
    this.db.updateObject(itemId, { state: 3 });

    this.notifyListeners();
    return newMaintenance;
  }

  updateItem(id, payload) {
    const object = this.db.getObjectById(id);
    if (!object) throw new Error("Item not found");

    const updates = {};

    if (payload.location !== undefined) {
      updates.location = payload.location;
    }

    if (payload.status !== undefined) {
      const allowedStatuses = [
        "available",
        "loaned",
        "maintenance",
        "damaged",
        "outofservice",
      ];

      if (allowedStatuses.includes(payload.status)) {
        updates.state = statusToState(payload.status);
      }
    }

    const updated = this.db.updateObject(id, updates);
    if (updated) {
      this.notifyListeners();
    }
    return updated;
  }

  markItemsDamaged(itemIds = []) {
    if (!Array.isArray(itemIds) || itemIds.length === 0) return [];
    const updated = [];

    itemIds.forEach((id) => {
      const result = this.db.updateObject(id, { state: 4 }); // damaged
      if (result) {
        updated.push(result);
      }
    });

    if (updated.length) {
      this.notifyListeners();
    }
    return updated;
  }
}
