import { BasePresenter } from "../core/BasePresenter.js";
import { ApiStore, request } from "../../api/ApiClient.js";

export class ClientsPresenter extends BasePresenter {
  constructor() {
    super([]);
    this.db = ApiStore.getInstance();
  }

  getClients() {
    // Get clients from DB and calculate loan statistics
    const clients = this.db.getClients();
    return clients.map((client) => {
      const allLoans = this.db.getLoansByClientId(client.id);
      const activeLoans = this.db.getActiveLoansByClientId(client.id);

      // Find most recent loan
      const sortedLoans = allLoans.sort(
        (a, b) => new Date(b.startDate) - new Date(a.startDate),
      );
      const lastLoan = sortedLoans.length > 0 ? sortedLoans[0].startDate : "—";

      return {
        id: client.id,
        name: `${client.name} ${client.surname}`,
        email: client.email,
        activeLoans: activeLoans.length,
        totalLoans: allLoans.length,
        lastLoan: lastLoan,
        state: client.state,
        blocked: client.state === 0,
      };
    });
  }

  async addClient(payload) {
    const { id } = await request("POST", "/clients", {
      name: payload.firstName || "",
      surname: payload.lastName || "",
      email: payload.email || "",
      state: 1, // active
    });
    await this.db.load();
    this.notifyListeners();
    return this.db.getClientById(id);
  }

  getClientById(id) {
    const client = this.db.getClientById(id);
    if (!client) return null;

    const allLoans = this.db.getLoansByClientId(client.id);
    const activeLoans = this.db.getActiveLoansByClientId(client.id);

    const sortedLoans = allLoans.sort(
      (a, b) => new Date(b.startDate) - new Date(a.startDate),
    );
    const lastLoan = sortedLoans.length > 0 ? sortedLoans[0].startDate : "—";

    return {
      id: client.id,
      name: `${client.name} ${client.surname}`,
      email: client.email,
      activeLoans: activeLoans.length,
      totalLoans: allLoans.length,
      lastLoan: lastLoan,
      state: client.state,
      blocked: client.state === 0,
    };
  }

  async deleteClient(id) {
    await request("DELETE", `/clients/${id}`);
    await this.db.load();
    this.notifyListeners();
  }

  async setBlocked(id, blocked) {
    await request("POST", "/clients/toggle-block", {
      id,
      action: blocked ? "block" : "unblock",
    });
    await this.db.load();
    this.notifyListeners();
  }

  getLoansByClientId(clientId) {
    const client = this.db.getClientById(clientId);
    if (!client) return [];

    return this.db
      .getLoansByClientId(clientId)
      .map((loan) => ({ ...loan, status: loan.state ? "active" : "closed" }));
  }

  async updateClient(id, payload) {
    await request("PUT", `/clients/${id}`, {
      name: payload.firstName || "",
      surname: payload.lastName || "",
    });
    await this.db.load();
    this.notifyListeners();
    return true;
  }
}
