export class LoansPresenter {
  constructor() {
    this.loans = [
      {
        id: "10001",
        client: "Mario Sale",
        status: "Active",
        returnDate: "12/12/2024",
        items: ["Laptop", "Mouse"],
      },
      {
        id: "10002",
        client: "Sale Mario",
        status: "Closed",
        returnDate: "02/10/2024",
        items: ["Tablet"],
      },
      {
        id: "10003",
        client: "Marius Salis",
        status: "Active",
        returnDate: "20/11/2024",
        items: ["Monitor", "HDMI Cable"],
      },
    ];
  }

  getLoans() {
    return this.loans;
  }

  getLoanById(id) {
    return this.loans.find((l) => l.id === id);
  }
}
