import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";

export class ClientsPresenter extends BasePresenter {
  constructor() {
    super([]);
    this.db = MockDatabase.getInstance();
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
      };
    });
  }

  addClient(payload) {
    const clientData = {
      name: payload.firstName || "",
      surname: payload.lastName || "",
      email: payload.email || "",
      state: 1, // active
    };

    const newClient = this.db.addClient(clientData);
    this.notifyListeners();
    return newClient;
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
    };
  }

  deleteClient(id) {
    const deleted = this.db.deleteClient(id);
    if (deleted) {
      this.notifyListeners();
    }
    return deleted;
  }

  getLoansByClientId(clientId) {
    const client = this.db.getClientById(clientId);
    if (!client) return [];

    return this.db.getLoansByClientId(clientId);
  }

  updateClient(id, payload) {
    const updates = {
      name: payload.firstName || "",
      surname: payload.lastName || "",
      email: payload.email || "",
    };

    const updated = this.db.updateClient(id, updates);
    if (updated) {
      this.notifyListeners();
    }
    return updated;
  }
}
