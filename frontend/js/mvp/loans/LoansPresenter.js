export class LoansPresenter {
  constructor() {
    this.listeners = [];
    this.loans = [
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
  }

  subscribe(callback) {
    this.listeners.push(callback);
  }

  notifyListeners() {
    this.listeners.forEach((cb) => cb());
  }

  getLoans() {
    return this.loans;
  }

  getLoanById(id) {
    return this.loans.find((l) => l.id === id);
  }
}
