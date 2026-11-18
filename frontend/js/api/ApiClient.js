// Talks to the PHP backend (same origin, session cookie) and keeps a local
// copy of its data so presenters can read it synchronously.

export async function request(method, path, body) {
  const response = await fetch(`/api${path}`, {
    method,
    credentials: "same-origin",
    headers: body ? { "Content-Type": "application/json" } : {},
    body: body ? JSON.stringify(body) : undefined,
  });
  const json = await response.json().catch(() => ({}));

  // Session expired: drop the stored user and go back to the login screen
  if (response.status === 401 && path !== "/login") {
    localStorage.removeItem("currentUser");
    window.location.reload();
  }
  if (!response.ok) {
    throw new Error(json.error?.message || `Request failed (${response.status})`);
  }
  return json.data;
}

export class ApiStore {
  static getInstance() {
    if (!ApiStore.instance) {
      ApiStore.instance = new ApiStore();
    }
    return ApiStore.instance;
  }

  constructor() {
    this.users = [];
    this.clients = [];
    this.objectTypes = [];
    this.objects = [];
    this.loans = [];
    this.loanedObjects = [];
    this.maintenance = [];
    this.operations = [];
  }

  // Reload every table from the API, converting rows to the frontend's camelCase shape
  async load() {
    const [technicians, clients, objectTypes, objects, loans, maintenance] =
      await Promise.all([
        request("GET", "/technicians"),
        request("GET", "/clients"),
        request("GET", "/objecttypes"),
        request("GET", "/objects"),
        request("GET", "/loans"),
        request("GET", "/maintenance"),
      ]);

    this.users = technicians;
    this.clients = clients.map(({ id, name, surname, email, state }) => ({
      id, name, surname, email, state,
    }));
    this.objectTypes = objectTypes;
    this.objects = objects.map((o) => ({
      id: o.id,
      objectTypeCode: o.object_type_code,
      location: o.location,
      state: o.state,
    }));
    this.loans = loans.map((l) => ({
      id: l.id,
      clientId: l.client_id,
      startDate: l.start_date,
      endDate: l.end_date,
      returnDate: l.return_date,
      notes: l.note || "",
      quantity: l.quantity,
      state: l.state,
    }));
    this.loanedObjects = loans.flatMap((l) =>
      l.loaned_objects.map((lo) => ({
        objectTypeCode: lo.object_type_code,
        objectId: lo.object_id,
        loanId: l.id,
        damaged: lo.damaged,
      })),
    );
    this.maintenance = maintenance.map((m) => ({
      id: m.id,
      objectId: m.object_id,
      description: m.description,
      startDate: m.start_date,
      endDate: m.end_date,
      type: m.type,
      technicianId: m.technician_id,
      phoneNumber: m.phone_number,
      cost: m.cost,
    }));
    this.operations = maintenance.flatMap((m) =>
      m.operations.map((op) => ({
        id: op.id,
        maintenanceId: op.maintenance_id,
        description: op.description,
        startDate: op.start_date,
        endDate: op.end_date,
        cost: op.cost,
      })),
    );
  }

  getUserById(id) {
    return this.users.find((u) => u.id === id);
  }

  getTechnicians() {
    return [...this.users];
  }

  getClients() {
    return [...this.clients];
  }

  getClientById(id) {
    return this.clients.find((c) => c.id === id);
  }

  getObjectTypes() {
    return [...this.objectTypes];
  }

  getObjectTypeByCode(code) {
    return this.objectTypes.find((ot) => ot.code === code);
  }

  getObjects() {
    return [...this.objects];
  }

  getObjectById(id) {
    return this.objects.find((o) => o.id === id);
  }

  getObjectsByTypeCode(objectTypeCode) {
    return this.objects.filter((o) => o.objectTypeCode === objectTypeCode);
  }

  getObjectsByState(state) {
    return this.objects.filter((o) => o.state === state);
  }

  getLoans() {
    return [...this.loans];
  }

  getLoanById(id) {
    return this.loans.find((l) => l.id === id);
  }

  getLoansByClientId(clientId) {
    return this.loans.filter((l) => l.clientId === clientId);
  }

  getActiveLoansByClientId(clientId) {
    return this.loans.filter((l) => l.clientId === clientId && l.state === true);
  }

  getLoanedObjectsByLoanId(loanId) {
    return this.loanedObjects.filter((lo) => lo.loanId === loanId);
  }

  getMaintenanceRecords() {
    return [...this.maintenance];
  }

  getMaintenanceById(id) {
    return this.maintenance.find((m) => m.id === id);
  }

  getMaintenanceByObjectId(objectId) {
    return this.maintenance.filter((m) => m.objectId === objectId);
  }

  getOperationsByMaintenanceId(maintenanceId) {
    return this.operations.filter((o) => o.maintenanceId === maintenanceId);
  }
}
