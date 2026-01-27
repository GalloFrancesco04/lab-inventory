import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";

export class InventoryPresenter extends BasePresenter {
  constructor() {
    const objectTypes = [
      {
        code: "COMP",
        name: "Computer",
        description: "Computers and laptops",
        model: "Various",
        brand: "Various",
        quantity: 15,
      },
      {
        code: "PERF",
        name: "Peripherals",
        description: "Computer peripherals and accessories",
        model: "Various",
        brand: "Various",
        quantity: 25,
      },
      {
        code: "TABL",
        name: "Tablet",
        description: "Tablets and mobile devices",
        model: "Various",
        brand: "Various",
        quantity: 8,
      },
    ];

    const initialItems = [
      {
        id: "20001",
        name: "Notebook HP",
        type: "Computer",
        status: "available",
        location: "Lab 1",
        brand: "HP",
        model: "Pavilion 15",
        imageUrl: null,
        maintenance: [
          {
            id: "1001",
            startDate: "10/01/2026",
            endDate: "10/01/2026",
            type: "preventive",
            tech: "S. Bianchi",
            phoneNumber: "+39 328 123 4567",
            desc: "Dust cleaning and general check",
            operations: [
              {
                desc: "Internal cleaning",
                cost: 25.0,
                startDate: "10/01/2026",
                endDate: "10/01/2026",
              },
              {
                desc: "Thermal paste replacement",
                cost: 15.0,
                startDate: "10/01/2026",
                endDate: "10/01/2026",
              },
            ],
          },
        ],
      },
      {
        id: "20002",
        name: "Notebook Dell",
        type: "Computer",
        status: "loaned",
        location: "Lab 1",
        brand: "Dell",
        model: "Latitude 5400",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "20003",
        name: "Notebook Lenovo",
        type: "Computer",
        status: "damaged",
        location: "Lab 2",
        brand: "Lenovo",
        model: "ThinkPad E14",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "20004",
        name: "Tablet Apple",
        type: "Tablet",
        status: "available",
        location: "Lab 3",
        brand: "Apple",
        model: "iPad Pro",
        imageUrl: null,
        maintenance: [
          {
            id: "1004",
            startDate: "05/12/2025",
            endDate: "05/12/2025",
            type: "repair",
            tech: "R. Verdi",
            phoneNumber: "+39 331 456 7890",
            desc: "Screen replaced",
            operations: [
              {
                desc: "Screen diagnostic",
                cost: 30.0,
                startDate: "05/12/2025",
                endDate: "05/12/2025",
              },
              {
                desc: "LCD replacement",
                cost: 120.0,
                startDate: "05/12/2025",
                endDate: "06/12/2025",
              },
            ],
          },
        ],
      },
      {
        id: "20005",
        name: "Monitor Dell",
        type: "Computer",
        status: "maintenance",
        location: "Lab 1",
        brand: "Dell",
        model: "P2720H",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "30001",
        name: "Mouse Logitech",
        type: "Peripherals",
        status: "available",
        location: "Lab 1",
        brand: "Logitech",
        model: "M705",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "30002",
        name: "Keyboard Corsair",
        type: "Peripherals",
        status: "available",
        location: "Lab 2",
        brand: "Corsair",
        model: "K60 RGB",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "30003",
        name: "Headset Sony",
        type: "Peripherals",
        status: "loaned",
        location: "Lab 3",
        brand: "Sony",
        model: "WH-1000XM4",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "30004",
        name: "USB Hub Anker",
        type: "Peripherals",
        status: "available",
        location: "Storage",
        brand: "Anker",
        model: "7-in-1",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "30005",
        name: "Webcam Logitech",
        type: "Peripherals",
        status: "damaged",
        location: "Lab 2",
        brand: "Logitech",
        model: "C920",
        imageUrl: null,
        maintenance: [],
      },
      {
        id: "20006",
        name: "Tablet Samsung",
        type: "Tablet",
        status: "available",
        location: "Lab 3",
        brand: "Samsung",
        model: "Galaxy Tab S7",
        imageUrl: null,
        maintenance: [],
      },
    ];

    super(initialItems);
    this.objectTypes = objectTypes;
  }

  subscribe(callback) {
    this.listeners.push(callback);
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getItemById(id) {
    return this.data.find((i) => i.id === id);
  }

  getItems() {
    return this.data;
  }

  getLocations() {
    const locations = this.data.map((item) => item.location).filter(Boolean);
    return [...new Set(locations)];
  }

  getItemTypes() {
    // Calculate quantity dynamically based on available items for each type
    return this.objectTypes.map((type) => {
      const availableCount = this.data.filter(
        (item) => item.type === type.name && item.status === "available",
      ).length;
      return {
        ...type,
        quantity: availableCount,
      };
    });
  }

  addObjectType(typeName) {
    if (!typeName) return null;

    // Generate code from name (first 4 letters uppercase)
    const code = typeName.substring(0, 4).toUpperCase().padEnd(4, "X");

    // Check if code already exists
    if (this.objectTypes.find((t) => t.code === code)) {
      return null;
    }

    const newType = {
      code,
      name: typeName,
      description: typeName,
      model: "Various",
      brand: "Various",
      quantity: 0,
    };
    this.objectTypes.push(newType);
    this.notifyListeners();
    return newType;
  }

  addItem(itemData) {
    const objectType = this.objectTypes.find(
      (t) => t.code === itemData.typeCode,
    );
    if (!objectType) return null;
    const newItem = {
      id: String(20000 + this.data.length + 1),
      name: itemData.name || objectType.name,
      type: objectType.name,
      brand: itemData.brand || objectType.brand,
      model: itemData.model || objectType.model,
      note: itemData.note || "",
      status: "available",
      location: "",
      imageUrl: null,
      maintenance: [],
    };
    this.data.push(newItem);
    this.notifyListeners();
    return newItem;
  }

  getTechnicians() {
    // Return list of available technicians from MockDatabase
    const mockDb = MockDatabase.getInstance();
    return mockDb.getTechnicians();
  }

  createMaintenance(itemId, description, technicianName) {
    const item = this.getItemById(itemId);
    if (!item) return null;

    // Generate new maintenance ID
    const newMaintenanceId = String(
      1000 +
        Math.max(
          0,
          ...this.data.flatMap((i) =>
            i.maintenance.map((m) => parseInt(m.id) || 0),
          ),
        ) +
        1,
    );

    // Create maintenance record
    const today = new Date();
    const dateStr = `${String(today.getDate()).padStart(2, "0")}/${String(today.getMonth() + 1).padStart(2, "0")}/${today.getFullYear()}`;

    const newMaintenance = {
      id: newMaintenanceId,
      startDate: dateStr,
      endDate: "",
      status: "open",
      type: "repair",
      tech: technicianName,
      desc: description,
      operations: [],
    };

    // Add maintenance to item
    item.maintenance.unshift(newMaintenance);

    // Update item status to maintenance
    item.status = "maintenance";

    // Notify listeners to update UI
    this.notifyListeners();

    return newMaintenance;
  }

  updateItem(id, payload) {
    const item = this.getItemById(id);
    if (!item) throw new Error("Item not found");

    const allowedStatuses = [
      "available",
      "loaned",
      "maintenance",
      "damaged",
      "outofservice",
    ];

    const status = allowedStatuses.includes(payload.status)
      ? payload.status
      : item.status;

    const updates = {
      name: payload.name?.trim() || item.name,
      location: payload.location?.trim() || item.location,
      status,
    };

    return this.update(id, updates);
  }

  markItemsDamaged(itemIds = []) {
    if (!Array.isArray(itemIds) || itemIds.length === 0) return [];
    const updated = [];
    itemIds.forEach((id) => {
      const item = this.getItemById(id);
      if (item) {
        item.status = "damaged";
        updated.push(item);
      }
    });
    if (updated.length) this.notifyListeners();
    return updated;
  }
}
