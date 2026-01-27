import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";

export class LoansPresenter extends BasePresenter {
  constructor(clientsPresenter = null, inventoryPresenter = null) {
    const mockDb = MockDatabase.getInstance();
    const initialLoans = [
      {
        id: "10001",
        client: "Mario Sale",
        status: "active",
        startDate: "01/12/2024",
        endDate: "12/12/2024",
        actualReturnDate: "",
        damagedItems: [],
        items: [
          { id: "20001", name: "Notebook", type: "Computer" },
          { id: "30001", name: "Mouse", type: "Peripherals" },
        ],
      },
      {
        id: "10002",
        client: "Sale Mario",
        status: "closed",
        startDate: "15/09/2024",
        endDate: "02/10/2024",
        actualReturnDate: "02/10/2024",
        damagedItems: [],
        items: [{ id: "20002", name: "Tablet", type: "Tablet" }],
      },
      {
        id: "10003",
        client: "Marius Salis",
        status: "active",
        startDate: "01/11/2024",
        endDate: "20/11/2024",
        actualReturnDate: "",
        damagedItems: [],
        items: [
          { id: "20003", name: "Monitor", type: "Computer" },
          { id: "30002", name: "Keyboard", type: "Peripherals" },
        ],
      },
    ];

    super(initialLoans);
    this.clientsPresenter = clientsPresenter;
    this.inventoryPresenter = inventoryPresenter;
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

  addLoan({ client, startDate, endDate, items }) {
    if (!client || !items || items.length === 0 || items.length > 5) {
      return null;
    }

    const newLoan = {
      id: String(Date.now()),
      client,
      startDate: startDate || "",
      status: "active",
      endDate: endDate || "",
      actualReturnDate: "",
      damagedItems: [],
      items: items.map((item) => ({
        id: item.id,
        name: item.name,
        type: item.type || "",
      })),
    };

    // Update item statuses to "loaned" in inventory
    if (this.inventoryPresenter) {
      items.forEach((item) => {
        try {
          this.inventoryPresenter.updateItem(item.id, { status: "loaned" });
        } catch (error) {
          console.error(`Unable to update item ${item.id} status`, error);
        }
      });
    }

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
    // Get available OBJECT instances from inventory
    if (this.inventoryPresenter) {
      try {
        const allItems = this.inventoryPresenter.getItems();
        return allItems
          .filter((item) => item.status === "available")
          .map((item) => ({
            id: item.id,
            name: item.name,
            type: item.type,
          }));
      } catch (error) {
        console.error("Unable to get available items from inventory", error);
      }
    }
    
    // Fallback to mock data
    return this.availableItems;
  }

  getClients() {
    return this.clients;
  }

  closeLoan(id, actualReturnDate, damagedItems = [], notes = "") {
    const loan = this.getLoanById(id);
    if (!loan) return null;
    
    loan.status = "closed";
    loan.actualReturnDate = actualReturnDate || loan.actualReturnDate || "";
    loan.damagedItems = damagedItems || [];
    loan.closeNotes = notes || "";
    
    // Update inventory statuses
    if (this.inventoryPresenter) {
      // Mark damaged items
      if (damagedItems.length) {
        const ids = damagedItems.map((d) => d.id).filter(Boolean);
        this.inventoryPresenter.markItemsDamaged(ids);
      }
      
      // Return non-damaged items to "available"
      const damagedIds = damagedItems.map((d) => d.id);
      loan.items.forEach((item) => {
        if (!damagedIds.includes(item.id)) {
          try {
            this.inventoryPresenter.updateItem(item.id, { status: "available" });
          } catch (error) {
            console.error(`Unable to return item ${item.id} to available`, error);
          }
        }
      });
    }
    
    this.notifyListeners();
    return loan;
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
