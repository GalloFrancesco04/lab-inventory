export class ClientsPresenter {
  constructor() {
    this.clients = [
      {
        id: "1001",
        name: "Mario Sale",
        email: "mario.sale@example.com",
        activeLoans: 2,
        totalLoans: 4,
        lastLoan: "12/12/2024",
      },
      {
        id: "1002",
        name: "Sale Mario",
        email: "sale.mario@example.com",
        activeLoans: 0,
        totalLoans: 1,
        lastLoan: "02/10/2024",
      },
      {
        id: "1003",
        name: "Marius Salis",
        email: "marius.salis@example.com",
        activeLoans: 1,
        totalLoans: 2,
        lastLoan: "20/11/2024",
      },
    ];
  }

  getClients() {
    return this.clients;
  }

  addClient(payload) {
    const nextId = String(1000 + this.clients.length + 1);
    const fullName = [payload.firstName, payload.lastName]
      .filter(Boolean)
      .join(" ")
      .trim();
    const newClient = {
      id: nextId,
      name: fullName || payload.email || "Unnamed",
      email: payload.email,
      phone: "",
      activeLoans: 0,
      totalLoans: 0,
      lastLoan: "—",
      blocked: false,
      blockedUntil: null,
    };
    this.clients.push(newClient);
    return newClient;
  }

  getClientById(id) {
    return this.clients.find((c) => c.id === id);
  }

  deleteClient(id) {
    const index = this.clients.findIndex((c) => c.id === id);
    if (index > -1) {
      this.clients.splice(index, 1);
      return true;
    }
    return false;
  }

  blockClient(id, days, reason) {
    const client = this.getClientById(id);
    if (!client) return false;
    const blockedUntil = new Date();
    blockedUntil.setDate(blockedUntil.getDate() + days);
    client.blocked = true;
    client.blockedUntil = blockedUntil.toISOString().split("T")[0];
    client.blockReason = reason || "";
    return true;
  }
}
