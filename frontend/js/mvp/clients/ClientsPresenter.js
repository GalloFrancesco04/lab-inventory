import { BasePresenter } from "../core/BasePresenter.js";

export class ClientsPresenter extends BasePresenter {
  constructor() {
    const initialClients = [
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
    super(initialClients);
  }

  getClients() {
    return this.getAll();
  }

  addClient(payload) {
    const nextId = String(1000 + this.data.length + 1);
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
    return this.add(newClient);
  }

  getClientById(id) {
    return this.getById(id);
  }

  deleteClient(id) {
    return this.delete(id);
  }

  blockClient(id, days, reason) {
    const blockedUntil = new Date();
    blockedUntil.setDate(blockedUntil.getDate() + days);
    return this.update(id, {
      blocked: true,
      blockedUntil: blockedUntil.toISOString().split("T")[0],
      blockReason: reason || "",
    });
  }
}
