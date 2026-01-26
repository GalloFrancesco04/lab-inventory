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
        name: "Notebook",
        type: "Electronics",
        status: "available",
        location: "Lab 1",
        imageUrl: null,
        maintenance: [
          {
            id: "1001",
            startDate: "10/01/2026",
            endDate: "10/01/2026",
            type: "preventive",
            tech: "S. Bianchi",
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
          {
            id: "1002",
            startDate: "22/12/2025",
            endDate: "",
            status: "open",
            type: "repair",
            tech: "M. Rossi",
            desc: "Keyboard key replacement",
            operations: [
              {
                desc: "Keyboard diagnostic",
                cost: 10.0,
                startDate: "22/12/2025",
                endDate: "22/12/2025",
              },
              {
                desc: "Key replacement",
                cost: 35.0,
                startDate: "22/12/2025",
                endDate: "23/12/2025",
              },
            ],
          },
          {
            id: "1003",
            startDate: "15/11/2025",
            endDate: "15/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Battery health check",
            operations: [
              {
                desc: "Battery test and report",
                cost: 20.0,
                startDate: "15/11/2025",
                endDate: "15/11/2025",
              },
            ],
          },
        ],
      },
      {
        id: "20002",
        name: "Tablet",
        type: "Electronics",
        status: "loaned",
        location: "Lab 2",
        imageUrl: null,
        maintenance: [
          {
            id: "1004",
            startDate: "05/12/2025",
            endDate: "05/12/2025",
            type: "repair",
            tech: "R. Verdi",
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
          {
            id: "1005",
            startDate: "20/11/2025",
            endDate: "20/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Software update",
            operations: [
              {
                desc: "OS update and configuration",
                cost: 15.0,
                startDate: "20/11/2025",
                endDate: "20/11/2025",
              },
            ],
          },
          {
            id: "1006",
            startDate: "02/10/2025",
            endDate: "03/10/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Battery replaced",
            operations: [
              {
                desc: "Battery removal",
                cost: 10.0,
                startDate: "02/10/2025",
                endDate: "02/10/2025",
              },
              {
                desc: "New battery installation",
                cost: 80.0,
                startDate: "02/10/2025",
                endDate: "03/10/2025",
              },
            ],
          },
        ],
      },
      {
        id: "20003",
        name: "Monitor",
        type: "Electronics",
        status: "available",
        location: "Lab 3",
        imageUrl: null,
        maintenance: [
          {
            id: "1007",
            startDate: "18/11/2025",
            endDate: "18/11/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Power supply replaced",
            operations: [
              {
                desc: "Power supply unit replacement",
                cost: 45.0,
                startDate: "18/11/2025",
                endDate: "18/11/2025",
              },
            ],
          },
          {
            id: "1008",
            startDate: "05/11/2025",
            endDate: "05/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Cable check",
            operations: [
              {
                desc: "Cable inspection",
                cost: 10.0,
                startDate: "05/11/2025",
                endDate: "05/11/2025",
              },
            ],
          },
          {
            id: "1009",
            startDate: "22/09/2025",
            endDate: "",
            status: "open",
            type: "repair",
            tech: "M. Rossi",
            desc: "HDMI port soldered",
            operations: [
              {
                desc: "HDMI port repair",
                cost: 25.0,
                startDate: "22/09/2025",
                endDate: "23/09/2025",
              },
            ],
          },
          {
            id: "1010",
            startDate: "15/08/2025",
            endDate: "15/08/2025",
            type: "preventive",
            tech: "A. Neri",
            desc: "Calibration check",
            operations: [
              {
                desc: "Color calibration",
                cost: 20.0,
                startDate: "15/08/2025",
                endDate: "15/08/2025",
              },
            ],
          },
        ],
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

  getItemTypes() {
    return this.objectTypes;
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
      status: itemData.status || "available",
      location: itemData.location,
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
}
