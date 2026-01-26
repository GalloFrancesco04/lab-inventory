import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";

export class LoansPresenter extends BasePresenter {
  constructor(clientsPresenter = null) {
    const mockDb = MockDatabase.getInstance();
    const initialLoans = [
      {
        id: "10001",
        client: "Mario Sale",
        status: "active",
        returnDate: "12/12/2024",
        items: [
          { id: "20001", name: "Notebook", type: "Computer" },
          { id: "30001", name: "Mouse", type: "Peripherals" },
        ],
      },
      {
        id: "10002",
        client: "Sale Mario",
        status: "closed",
        returnDate: "02/10/2024",
        items: [{ id: "20002", name: "Tablet", type: "Tablet" }],
      },
      {
        id: "10003",
        client: "Marius Salis",
        status: "active",
        returnDate: "20/11/2024",
        items: [
          { id: "20003", name: "Monitor", type: "Computer" },
          { id: "30002", name: "Keyboard", type: "Peripherals" },
        ],
      },
    ];

    super(initialLoans);
    this.clientsPresenter = clientsPresenter;
    this.clients = mockDb.getClients();
    this.availableItems = [
      { id: "20001", name: "Laptop", type: "Computer" },
      { id: "20002", name: "Tablet", type: "Tablet" },
      { id: "20003", name: "Monitor", type: "Computer" },
      { id: "30001", name: "Mouse", type: "Peripherals" },
      { id: "30002", name: "Keyboard", type: "Peripherals" },
      { id: "30003", name: "Headset", type: "Peripherals" },
      { id: "30004", name: "Docking Station", type: "Peripherals" },
      { id: "30005", name: "Webcam", type: "Peripherals" },
      { id: "30006", name: "USB-C Hub", type: "Peripherals" },
      { id: "30007", name: "Charger 65W", type: "Peripherals" },
    ];
  }

  addLoan({ client, returnDate, items }) {
    if (!client || !items || items.length === 0 || items.length > 5) {
      return null;
    }

    const newLoan = {
      id: String(Date.now()),
      client,
      status: "active",
      returnDate: returnDate || "",
      items: items.map((item) => ({
        id: item.id,
        name: item.name,
        type: item.type || "",
      })),
    };

    this.data.unshift(newLoan);
    this.notifyListeners();
    return newLoan;
  }

  getLoans() {
    return this.data;
  }

  getLoanById(id) {
    return this.data.find((l) => l.id === id);
  }

  getAvailableItems() {
    return this.availableItems;
  }

  getClients() {
    return this.clients;
  }

  addClient(name, email) {
    if (!name) return null;

    const mockDb = MockDatabase.getInstance();

    // Check if client already exists
    if (!mockDb.addClient(name)) {
      return null;
    }

    // Update local clients list
    this.clients = mockDb.getClients();

    // Also add to clients presenter if provided
    if (this.clientsPresenter?.addClient) {
      this.clientsPresenter.addClient({
        firstName: name,
        lastName: "",
        email,
      });
    }

    this.notifyListeners();
    return { name, email };
  }
}
