export class InventoryPresenter {
  constructor() {
    this.items = [
      {
        id: "20001",
        name: "Notebook",
        type: "Electronics",
        status: "Available",
        location: "Lab 1",
        imageUrl: null,
        maintenance: [
          {
            date: "10/01/2026",
            type: "Preventive",
            tech: "S. Bianchi",
            desc: "Dust cleaning",
          },
          {
            date: "22/12/2025",
            type: "Repair",
            tech: "M. Rossi",
            desc: "Keyboard key replacement",
          },
          {
            date: "15/11/2025",
            type: "Preventive",
            tech: "S. Bianchi",
            desc: "Battery health check",
          },
        ],
      },
      {
        id: "20002",
        name: "Tablet",
        type: "Electronics",
        status: "Loaned",
        location: "Lab 2",
        imageUrl: null,
        maintenance: [
          {
            date: "05/12/2025",
            type: "Repair",
            tech: "R. Verdi",
            desc: "Screen replaced",
          },
          {
            date: "20/11/2025",
            type: "Preventive",
            tech: "S. Bianchi",
            desc: "Software update",
          },
          {
            date: "02/10/2025",
            type: "Replacement",
            tech: "A. Neri",
            desc: "Battery replaced",
          },
        ],
      },
      {
        id: "20003",
        name: "Monitor",
        type: "Electronics",
        status: "Available",
        location: "Lab 3",
        imageUrl: null,
        maintenance: [
          {
            date: "18/11/2025",
            type: "Replacement",
            tech: "A. Neri",
            desc: "Power supply replaced",
          },
          {
            date: "05/11/2025",
            type: "Preventive",
            tech: "S. Bianchi",
            desc: "Cable check",
          },
          {
            date: "22/09/2025",
            type: "Repair",
            tech: "M. Rossi",
            desc: "HDMI port soldered",
          },
          {
            date: "15/08/2025",
            type: "Preventive",
            tech: "A. Neri",
            desc: "Calibration check",
          },
        ],
      },
    ];
  }

  getItemById(id) {
    return this.items.find((i) => i.id === id);
  }

  getItems() {
    return this.items;
  }
}
