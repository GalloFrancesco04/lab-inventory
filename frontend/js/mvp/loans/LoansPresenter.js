import { BasePresenter } from "../core/BasePresenter.js";
import { MockDatabase } from "../../utils/MockDatabase.js";
import { LoanValidator, ValidationError } from "../../utils/Validators.js";

export class LoansPresenter extends BasePresenter {
  constructor(
    clientsPresenter = null,
    inventoryPresenter = null,
    mediator = null,
  ) {
    super([]);
    this.db = MockDatabase.getInstance();
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

  addLoan({ client, startDate, endDate, items }) {
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

    // Create loan
    const newLoan = this.db.addLoan({
      startDate: startDate,
      endDate: endDate || null,
      returnDate: null,
      clientId: clientObj.id,
      notes: "",
      quantity: items.length,
      state: true, // active
    });

    // Create loaned objects entries
    items.forEach((item) => {
      const object = this.db.getObjectById(item.id);
      if (object) {
        this.db.addLoanedObject({
          objectTypeCode: object.objectTypeCode,
          objectId: item.id,
          loanId: newLoan.id,
          damaged: false,
        });

        // Update object state to loaned
        this.db.updateObject(item.id, { state: 2 });
      }
    });

    // Update inventory presenter if available
    if (this.inventoryPresenter) {
      items.forEach((item) => {
        try {
          this.inventoryPresenter.updateItem(item.id, { status: "loaned" });
        } catch (error) {
          console.error(`Unable to update item ${item.id} status`, error);
        }
      });
    }

    // Emit event for other modules to listen
    if (this.mediator) {
      this.mediator.emit("loan:created", {
        loanId: newLoan.id,
        clientId: clientObj.id,
        items: items,
        startDate: startDate,
        endDate: endDate,
      });
    }

    this.notifyListeners();
    return this.getLoanById(newLoan.id);
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

  closeLoan(id, actualReturnDate, damagedItems = [], notes = "") {
    const loan = this.db.getLoanById(id);
    if (!loan) return null;

    // Update loan
    this.db.updateLoan(id, {
      state: false, // closed
      returnDate: actualReturnDate,
      notes: notes,
    });

    // Get loaned objects
    const loanedObjects = this.db.getLoanedObjectsByLoanId(id);

    // Mark damaged items in loaned objects
    const damagedIds = damagedItems.map((d) => d.id);
    loanedObjects.forEach((lo) => {
      if (damagedIds.includes(lo.objectId)) {
        this.db.updateLoanedObject(lo.objectId, lo.loanId, { damaged: true });
        this.db.updateObject(lo.objectId, { state: 4 }); // damaged
      } else {
        this.db.updateObject(lo.objectId, { state: 1 }); // available
      }
    });

    // Update inventory presenter if available
    if (this.inventoryPresenter) {
      if (damagedItems.length) {
        this.inventoryPresenter.markItemsDamaged(damagedIds);
      }

      loanedObjects.forEach((lo) => {
        if (!damagedIds.includes(lo.objectId)) {
          try {
            this.inventoryPresenter.updateItem(lo.objectId, {
              status: "available",
            });
          } catch (error) {
            console.error(
              `Unable to return item ${lo.objectId} to available`,
              error,
            );
          }
        }
      });
    }

    // Emit event for other modules to listen
    if (this.mediator) {
      const allItems = loanedObjects.map((lo) => ({ id: lo.objectId }));

      this.mediator.emit("loan:closed", {
        loanId: id,
        items: allItems,
        damagedItems: damagedItems,
      });
    }

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

    // Fallback: add directly to DB
    const newClient = this.db.addClient({
      name: firstName,
      surname: lastName,
      email: email || "",
      state: 1,
    });

    this.notifyListeners();
    return newClient;
  }
}
