export class InventoryPresenter {
  constructor() {
    this.listeners = [];
    this.objectTypes = [
      {
        code: "ELEC",
        name: "Electronics",
        description: "Electronic devices",
        model: "Various",
        brand: "Various",
        quantity: 15,
      },
      {
        code: "FURN",
        name: "Furniture",
        description: "Office furniture",
        model: "Various",
        brand: "Various",
        quantity: 8,
      },
      {
        code: "TOOL",
        name: "Tools",
        description: "Work tools",
        model: "Various",
        brand: "Various",
        quantity: 12,
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
            date: "10/01/2026",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Dust cleaning",
          },
          {
            date: "22/12/2025",
            type: "repair",
            tech: "M. Rossi",
            desc: "Keyboard key replacement",
          },
          {
            date: "15/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Battery health check",
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
            date: "05/12/2025",
            type: "repair",
            tech: "R. Verdi",
            desc: "Screen replaced",
          },
          {
            date: "20/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Software update",
          },
          {
            date: "02/10/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Battery replaced",
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
            date: "18/11/2025",
            type: "replacement",
            tech: "A. Neri",
            desc: "Power supply replaced",
          },
          {
            date: "05/11/2025",
            type: "preventive",
            tech: "S. Bianchi",
            desc: "Cable check",
          },
          {
            date: "22/09/2025",
            type: "repair",
            tech: "M. Rossi",
            desc: "HDMI port soldered",
          },
          {
            date: "15/08/2025",
            type: "preventive",
            tech: "A. Neri",
            desc: "Calibration check",
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
      name: objectType.name,
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
