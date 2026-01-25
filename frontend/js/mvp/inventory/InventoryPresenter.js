export class InventoryPresenter {
  constructor() {
    this.listeners = [];
    this.objectTypes = [
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
    this.items = [
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
            date: "10/01/2026",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Dust cleaning and general check",
            operations: [
              { desc: "Internal cleaning", cost: 25.0 },
              { desc: "Thermal paste replacement", cost: 15.0 },
            ],
          },
          {
            id: "1002",
            date: "22/12/2025",
            type: "repair",
            tech: "M. Rossi",
            desc: "Keyboard key replacement",
            operations: [
              { desc: "Keyboard diagnostic", cost: 10.0 },
              { desc: "Key replacement", cost: 35.0 },
            ],
          },
          {
            id: "1003",
            date: "15/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Battery health check",
            operations: [{ desc: "Battery test and report", cost: 20.0 }],
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
            date: "05/12/2025",
            type: "repair",
            tech: "R. Verdi",
            desc: "Screen replaced",
            operations: [
              { desc: "Screen diagnostic", cost: 30.0 },
              { desc: "LCD replacement", cost: 120.0 },
            ],
          },
          {
            id: "1005",
            date: "20/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Software update",
            operations: [{ desc: "OS update and configuration", cost: 15.0 }],
          },
          {
            id: "1006",
            date: "02/10/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Battery replaced",
            operations: [
              { desc: "Battery removal", cost: 10.0 },
              { desc: "New battery installation", cost: 80.0 },
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
            date: "18/11/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Power supply replaced",
            operations: [{ desc: "Power supply unit replacement", cost: 45.0 }],
          },
          {
            id: "1008",
            date: "05/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Cable check",
            operations: [{ desc: "Cable inspection", cost: 10.0 }],
          },
          {
            id: "1009",
            date: "22/09/2025",
            type: "repair",
            tech: "M. Rossi",
            desc: "HDMI port soldered",
            operations: [{ desc: "HDMI port repair", cost: 25.0 }],
          },
          {
            id: "1010",
            date: "15/08/2025",
            type: "preventive",
            tech: "A. Neri",
            desc: "Calibration check",
            operations: [{ desc: "Color calibration", cost: 20.0 }],
          },
        ],
      },
    ];
  }

  subscribe(callback) {
    this.listeners.push(callback);
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getItemById(id) {
    return this.items.find((i) => i.id === id);
  }

  getItems() {
    return this.items;
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
      id: String(20000 + this.items.length + 1),
      name: itemData.name || objectType.name,
      type: objectType.name,
      status: itemData.status || "available",
      location: itemData.location,
      imageUrl: null,
      maintenance: [],
    };
    this.items.push(newItem);
    this.notifyListeners();
    return newItem;
  }
}
