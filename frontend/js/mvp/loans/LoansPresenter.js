import { BasePresenter } from "../core/BasePresenter.js";
import { ApiStore, request } from "../../api/ApiClient.js";
import { LoanValidator, ValidationError } from "../../utils/Validators.js";

export class LoansPresenter extends BasePresenter {
  constructor(
    clientsPresenter = null,
    inventoryPresenter = null,
    mediator = null,
  ) {
    super([]);
    this.db = ApiStore.getInstance();
    this.clientsPresenter = clientsPresenter;
    this.inventoryPresenter = inventoryPresenter;
    this.mediator = mediator; // For event emission
  }

  getLoans() {
    const loans = this.db.getLoans();

    return loans.map((loan) => {
      const client = this.db.getClientById(loan.clientId);
      const loanedObjects = this.db.getLoanedObjectsByLoanId(loan.id);

      // Map loaned objects to items
      const items = loanedObjects.map((lo) => {
        const object = this.db.getObjectById(lo.objectId);
        const objectType = object
          ? this.db.getObjectTypeByCode(object.objectTypeCode)
          : null;

        return {
          id: lo.objectId,
          name: objectType ? objectType.name : "Unknown",
          type: objectType ? objectType.name : "Unknown",
          damaged: lo.damaged,
        };
      });

      // Get damaged items
      const damagedItems = loanedObjects
        .filter((lo) => lo.damaged)
        .map((lo) => ({
          id: lo.objectId,
          reason: "Damaged",
        }));

      return {
        id: loan.id,
        client: client ? `${client.name} ${client.surname}` : "Unknown",
        clientId: loan.clientId,
        startDate: loan.startDate,
        endDate: loan.endDate || "",
        actualReturnDate: loan.returnDate || "",
        status: loan.state ? "active" : "closed",
        items: items,
        damagedItems: damagedItems,
        notes: loan.notes,
        quantity: loan.quantity,
      };
    });
  }

  getLoanById(id) {
    const loans = this.getLoans();
    return loans.find((l) => l.id === id);
  }

  async addLoan({ client, startDate, endDate, items }) {
    // Validate loan data
    try {
      LoanValidator.validateCreate({ client, startDate, endDate, items });
    } catch (error) {
      if (error instanceof ValidationError) {
        throw error;
      }
      throw new ValidationError("Loan validation failed");
    }

    // Additional validation: max 5 items per loan
    if (items.length > 5) {
      throw new ValidationError("Maximum 5 items per loan", "items");
    }

    // Find client by name
    const clients = this.db.getClients();
    const clientObj = clients.find((c) => `${c.name} ${c.surname}` === client);

    if (!clientObj) {
      throw new ValidationError("Client not found");
    }

    const { id } = await request("POST", "/loans", {
      client_id: clientObj.id,
      start_date: startDate,
      end_date: endDate || null,
      inventory_items: items.map((item) => ({
        id: item.id,
        object_type_code: this.db.getObjectById(item.id)?.objectTypeCode,
      })),
    });

    await this.db.load();
    this.notifyListeners();
    return this.getLoanById(id);
  }

  getAvailableItems() {
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

    // Fallback: get from DB directly
    const objects = this.db.getObjectsByState(1); // available
    return objects.map((obj) => {
      const objectType = this.db.getObjectTypeByCode(obj.objectTypeCode);
      return {
        id: obj.id,
        name: objectType ? objectType.name : "Unknown",
        type: objectType ? objectType.name : "Unknown",
      };
    });
  }

  getClients() {
    const clients = this.db.getClients();
    return clients.map((c) => `${c.name} ${c.surname}`);
  }

  async closeLoan(id, actualReturnDate, damagedItems = [], notes = "") {
    const loan = this.db.getLoanById(id);
    if (!loan) return null;

    // Record notes and damaged items first, then close (the backend sets the return date)
    const damagedIds = damagedItems.map((d) => Number(d.id)); // checkbox values are strings
    await request("PUT", `/loans/${id}`, {
      note: notes,
      inventory_items: this.db.getLoanedObjectsByLoanId(id).map((lo) => ({
        id: lo.objectId,
        object_type_code: lo.objectTypeCode,
        state: damagedIds.includes(lo.objectId) ? 4 : 1,
      })),
    });
    await request("POST", `/loans/${id}/close`);

    await this.db.load();
    this.notifyListeners();
    return this.getLoanById(id);
  }

  addClient(name, email) {
    if (!name) return null;

    // Split name into first and last name
    const nameParts = name.trim().split(" ");
    const firstName = nameParts[0] || "";
    const lastName = nameParts.slice(1).join(" ") || "";

    // Add client via clients presenter if available
    if (this.clientsPresenter?.addClient) {
      return this.clientsPresenter.addClient({
        firstName: firstName,
        lastName: lastName,
        email: email || "",
      });
    }

    return null;
  }
}
