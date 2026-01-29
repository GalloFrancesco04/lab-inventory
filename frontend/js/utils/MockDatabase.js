export class MockDatabase {
  static getInstance() {
    if (!MockDatabase.instance) {
      MockDatabase.instance = new MockDatabase();
    }
    return MockDatabase.instance;
  }

  constructor() {
    this.initializeDatabase();
  }

  initializeDatabase() {
    // USER table - from db.mmd schema
    this.users = [
      {
        id: 1,
        email: "admin@library.com",
        pass: "admin123",
        role: 0, // 0 = admin
      },
      {
        id: 2,
        email: "mario.rossi@library.com",
        pass: "tech123",
        role: 1, // 1 = technician
      },
      {
        id: 3,
        email: "sara.bianchi@library.com",
        pass: "tech123",
        role: 1,
      },
      {
        id: 4,
        email: "roberto.verdi@library.com",
        pass: "tech123",
        role: 1,
      },
      {
        id: 5,
        email: "anna.neri@library.com",
        pass: "tech123",
        role: 1,
      },
    ];

    // CLIENT table - from db.mmd schema
    this.clients = [
      {
        id: 1,
        email: "mario.sale@example.com",
        name: "Mario",
        surname: "Sale",
        state: 1, // 1 = active
      },
      {
        id: 2,
        email: "laura.bianchi@example.com",
        name: "Laura",
        surname: "Bianchi",
        state: 1,
      },
      {
        id: 3,
        email: "giuseppe.verdi@example.com",
        name: "Giuseppe",
        surname: "Verdi",
        state: 1,
      },
      {
        id: 4,
        email: "anna.rossi@example.com",
        name: "Anna",
        surname: "Rossi",
        state: 1,
      },
      {
        id: 5,
        email: "luca.neri@example.com",
        name: "Luca",
        surname: "Neri",
        state: 1,
      },
    ];

    // OBJECTTYPE table - from db.mmd schema
    this.objectTypes = [
      {
        code: "COMP",
        name: "Computer",
        description: "Desktop and laptop computers",
        model: "Various",
        brand: "Various",
        quantity: 8,
      },
      {
        code: "TABL",
        name: "Tablet",
        description: "Tablets and mobile devices",
        model: "Various",
        brand: "Various",
        quantity: 5,
      },
      {
        code: "MOUS",
        name: "Mouse",
        description: "Computer mice",
        model: "Various",
        brand: "Various",
        quantity: 10,
      },
      {
        code: "KEYB",
        name: "Keyboard",
        description: "Computer keyboards",
        model: "Various",
        brand: "Various",
        quantity: 8,
      },
      {
        code: "MONI",
        name: "Monitor",
        description: "Computer monitors and displays",
        model: "Various",
        brand: "Various",
        quantity: 6,
      },
      {
        code: "HEAD",
        name: "Headset",
        description: "Headphones and headsets",
        model: "Various",
        brand: "Various",
        quantity: 4,
      },
    ];

    // OBJECT table - from db.mmd schema
    // state: 1=available, 2=loaned, 3=maintenance, 4=damaged, 5=outofservice
    this.objects = [
      // Computers
      { id: 1, objectTypeCode: "COMP", state: 1, location: "Lab A" },
      { id: 2, objectTypeCode: "COMP", state: 2, location: "Lab A" },
      { id: 3, objectTypeCode: "COMP", state: 1, location: "Lab B" },
      { id: 4, objectTypeCode: "COMP", state: 2, location: "Lab A" },
      { id: 5, objectTypeCode: "COMP", state: 3, location: "Maintenance" },
      { id: 6, objectTypeCode: "COMP", state: 1, location: "Lab C" },
      { id: 7, objectTypeCode: "COMP", state: 4, location: "Storage" },
      { id: 8, objectTypeCode: "COMP", state: 1, location: "Lab B" },
      // Tablets
      { id: 9, objectTypeCode: "TABL", state: 1, location: "Lab A" },
      { id: 10, objectTypeCode: "TABL", state: 2, location: "Lab B" },
      { id: 11, objectTypeCode: "TABL", state: 1, location: "Lab C" },
      { id: 12, objectTypeCode: "TABL", state: 1, location: "Lab A" },
      { id: 13, objectTypeCode: "TABL", state: 2, location: "Lab B" },
      // Mice
      { id: 14, objectTypeCode: "MOUS", state: 1, location: "Lab A" },
      { id: 15, objectTypeCode: "MOUS", state: 1, location: "Lab A" },
      { id: 16, objectTypeCode: "MOUS", state: 2, location: "Lab B" },
      { id: 17, objectTypeCode: "MOUS", state: 1, location: "Lab C" },
      { id: 18, objectTypeCode: "MOUS", state: 1, location: "Lab A" },
      { id: 19, objectTypeCode: "MOUS", state: 4, location: "Storage" },
      { id: 20, objectTypeCode: "MOUS", state: 1, location: "Lab B" },
      { id: 21, objectTypeCode: "MOUS", state: 1, location: "Lab C" },
      { id: 22, objectTypeCode: "MOUS", state: 1, location: "Lab A" },
      { id: 23, objectTypeCode: "MOUS", state: 1, location: "Lab B" },
      // Keyboards
      { id: 24, objectTypeCode: "KEYB", state: 1, location: "Lab A" },
      { id: 25, objectTypeCode: "KEYB", state: 2, location: "Lab A" },
      { id: 26, objectTypeCode: "KEYB", state: 1, location: "Lab B" },
      { id: 27, objectTypeCode: "KEYB", state: 1, location: "Lab C" },
      { id: 28, objectTypeCode: "KEYB", state: 1, location: "Lab A" },
      { id: 29, objectTypeCode: "KEYB", state: 1, location: "Lab B" },
      { id: 30, objectTypeCode: "KEYB", state: 1, location: "Lab C" },
      { id: 31, objectTypeCode: "KEYB", state: 1, location: "Lab A" },
      // Monitors
      { id: 32, objectTypeCode: "MONI", state: 1, location: "Lab A" },
      { id: 33, objectTypeCode: "MONI", state: 1, location: "Lab B" },
      { id: 34, objectTypeCode: "MONI", state: 2, location: "Lab C" },
      { id: 35, objectTypeCode: "MONI", state: 1, location: "Lab A" },
      { id: 36, objectTypeCode: "MONI", state: 1, location: "Lab B" },
      { id: 37, objectTypeCode: "MONI", state: 1, location: "Lab C" },
      // Headsets
      { id: 38, objectTypeCode: "HEAD", state: 1, location: "Lab A" },
      { id: 39, objectTypeCode: "HEAD", state: 2, location: "Lab B" },
      { id: 40, objectTypeCode: "HEAD", state: 1, location: "Lab C" },
      { id: 41, objectTypeCode: "HEAD", state: 1, location: "Lab A" },
    ];

    // LOANS table - from db.mmd schema
    // state: true=active, false=closed
    this.loans = [
      {
        id: 1,
        startDate: "2024-12-01",
        endDate: "2024-12-15",
        returnDate: null,
        clientId: 1,
        notes: "",
        quantity: 3,
        state: true, // active
      },
      {
        id: 2,
        startDate: "2024-11-10",
        endDate: "2024-11-25",
        returnDate: "2024-11-24",
        clientId: 2,
        notes: "Returned on time",
        quantity: 2,
        state: false, // closed
      },
      {
        id: 3,
        startDate: "2024-12-10",
        endDate: "2024-12-20",
        returnDate: null,
        clientId: 3,
        notes: "",
        quantity: 2,
        state: true, // active
      },
      {
        id: 4,
        startDate: "2024-10-05",
        endDate: "2024-10-20",
        returnDate: "2024-10-20",
        clientId: 4,
        notes: "Equipment in good condition",
        quantity: 1,
        state: false, // closed
      },
      {
        id: 5,
        startDate: "2024-12-15",
        endDate: "2025-01-05",
        returnDate: null,
        clientId: 5,
        notes: "",
        quantity: 3,
        state: true, // active
      },
    ];

    // LOANEDOBJECTS table - from db.mmd schema
    this.loanedObjects = [
      // Loan 1 (active) - client 1
      { objectTypeCode: "COMP", objectId: 2, loanId: 1, damaged: false },
      { objectTypeCode: "MOUS", objectId: 16, loanId: 1, damaged: false },
      { objectTypeCode: "KEYB", objectId: 25, loanId: 1, damaged: false },
      // Loan 2 (closed) - client 2 - already returned
      // Loan 3 (active) - client 3
      { objectTypeCode: "COMP", objectId: 4, loanId: 3, damaged: false },
      { objectTypeCode: "TABL", objectId: 10, loanId: 3, damaged: false },
      // Loan 4 (closed) - client 4 - already returned
      // Loan 5 (active) - client 5
      { objectTypeCode: "TABL", objectId: 13, loanId: 5, damaged: false },
      { objectTypeCode: "MONI", objectId: 34, loanId: 5, damaged: false },
      { objectTypeCode: "HEAD", objectId: 39, loanId: 5, damaged: false },
    ];

    // MAINTENANCE table - from db.mmd schema
    this.maintenance = [
      {
        id: 1,
        objectId: 5,
        description: "Hard drive replacement",
        startDate: "2024-12-20",
        endDate: null,
        type: "repair",
        technicianId: 2, // Mario Rossi
        phoneNumber: "+39 328 123 4567",
        cost: 150.0,
      },
      {
        id: 2,
        objectId: 7,
        description: "Screen repair",
        startDate: "2024-11-15",
        endDate: "2024-11-20",
        type: "repair",
        technicianId: 3, // Sara Bianchi
        phoneNumber: "+39 331 234 5678",
        cost: 250.0,
      },
      {
        id: 3,
        objectId: 19,
        description: "Button replacement",
        startDate: "2024-12-01",
        endDate: "2024-12-05",
        type: "repair",
        technicianId: 4, // Roberto Verdi
        phoneNumber: "+39 335 345 6789",
        cost: 20.0,
      },
      {
        id: 4,
        objectId: 12,
        description: "Battery replacement",
        startDate: "2024-12-18",
        endDate: null,
        type: "repair",
        technicianId: 2, // Mario Rossi
        phoneNumber: "+39 328 123 4567",
        cost: 80.0,
      },
      {
        id: 5,
        objectId: 15,
        description: "Keyboard cleaning and maintenance",
        startDate: "2024-12-15",
        endDate: null,
        type: "maintenance",
        technicianId: 2, // Mario Rossi
        phoneNumber: "+39 328 123 4567",
        cost: 45.0,
      },
      // Previous maintenance history
      {
        id: 6,
        objectId: 5,
        description: "RAM upgrade",
        startDate: "2024-10-15",
        endDate: "2024-10-18",
        type: "repair",
        technicianId: 3, // Sara Bianchi
        phoneNumber: "+39 331 234 5678",
        cost: 120.0,
      },
      {
        id: 7,
        objectId: 5,
        description: "Motherboard repair",
        startDate: "2024-08-20",
        endDate: "2024-08-25",
        type: "repair",
        technicianId: 4, // Roberto Verdi
        phoneNumber: "+39 335 345 6789",
        cost: 200.0,
      },
      {
        id: 8,
        objectId: 7,
        description: "Display replacement",
        startDate: "2024-09-10",
        endDate: "2024-09-15",
        type: "repair",
        technicianId: 2, // Mario Rossi
        phoneNumber: "+39 328 123 4567",
        cost: 300.0,
      },
      {
        id: 9,
        objectId: 12,
        description: "Screen cleaning",
        startDate: "2024-11-01",
        endDate: "2024-11-03",
        type: "maintenance",
        technicianId: 3, // Sara Bianchi
        phoneNumber: "+39 331 234 5678",
        cost: 30.0,
      },
      {
        id: 10,
        objectId: 15,
        description: "Keyboard replacement",
        startDate: "2024-07-20",
        endDate: "2024-07-22",
        type: "repair",
        technicianId: 5, // Anna Neri
        phoneNumber: "+39 340 456 7890",
        cost: 80.0,
      },
      {
        id: 6,
        objectId: 8,
        description: "Monitor cable replacement",
        startDate: "2024-12-10",
        endDate: "2024-12-11",
        type: "repair",
        technicianId: 3, // Sara Bianchi
        phoneNumber: "+39 331 234 5678",
        cost: 35.0,
      },
      {
        id: 7,
        objectId: 22,
        description: "Headset microphone repair",
        startDate: "2024-12-19",
        endDate: null,
        type: "repair",
        technicianId: 5, // Anna Neri
        phoneNumber: "+39 339 567 8901",
        cost: 55.0,
      },
    ];

    // OPERATIONS table - from db.mmd schema
    this.operations = [
      // Operations for maintenance 1
      {
        id: 1,
        maintenanceId: 1,
        description: "Diagnostic",
        startDate: "2024-12-20",
        endDate: "2024-12-20",
        cost: 30.0,
      },
      {
        id: 2,
        maintenanceId: 1,
        description: "Hard drive replacement",
        startDate: "2024-12-21",
        endDate: null,
        cost: 120.0,
      },
      // Operations for maintenance 2
      {
        id: 3,
        maintenanceId: 2,
        description: "Screen diagnostic",
        startDate: "2024-11-15",
        endDate: "2024-11-15",
        cost: 50.0,
      },
      {
        id: 4,
        maintenanceId: 2,
        description: "LCD replacement",
        startDate: "2024-11-16",
        endDate: "2024-11-20",
        cost: 200.0,
      },
      // Operations for maintenance 3
      {
        id: 5,
        maintenanceId: 3,
        description: "Button replacement",
        startDate: "2024-12-01",
        endDate: "2024-12-05",
        cost: 20.0,
      },
      // Operations for maintenance 4
      {
        id: 6,
        maintenanceId: 4,
        description: "Battery diagnostic",
        startDate: "2024-12-18",
        endDate: "2024-12-18",
        cost: 20.0,
      },
      {
        id: 7,
        maintenanceId: 4,
        description: "Battery replacement",
        startDate: "2024-12-19",
        endDate: null,
        cost: 60.0,
      },
      // Operations for maintenance 5
      {
        id: 8,
        maintenanceId: 5,
        description: "Keyboard cleaning",
        startDate: "2024-12-15",
        endDate: null,
        cost: 25.0,
      },
      {
        id: 9,
        maintenanceId: 5,
        description: "Key mechanism inspection",
        startDate: "2024-12-15",
        endDate: null,
        cost: 20.0,
      },
      // Operations for maintenance 6
      {
        id: 10,
        maintenanceId: 6,
        description: "Cable replacement",
        startDate: "2024-12-10",
        endDate: "2024-12-11",
        cost: 35.0,
      },
      // Operations for maintenance 7
      {
        id: 11,
        maintenanceId: 7,
        description: "Microphone repair",
        startDate: "2024-12-19",
        endDate: null,
        cost: 55.0,
      },
    ];

    // Auto-increment counters
    this.counters = {
      userId: 6,
      clientId: 6,
      objectId: 42,
      loanId: 6,
      maintenanceId: 8,
      operationId: 12,
    };
  }

  // ========== USER Methods ==========
  getUsers() {
    return [...this.users];
  }

  getUserById(id) {
    return this.users.find((u) => u.id === id);
  }

  getUserByEmail(email) {
    return this.users.find((u) => u.email === email);
  }

  getTechnicians() {
    return this.users.filter((u) => u.role === 1);
  }

  // ========== CLIENT Methods ==========
  getClients() {
    return [...this.clients];
  }

  getClientById(id) {
    return this.clients.find((c) => c.id === id);
  }

  addClient(clientData) {
    const newClient = {
      id: this.counters.clientId++,
      email: clientData.email || "",
      name: clientData.name || "",
      surname: clientData.surname || "",
      state: clientData.state || 1, // 1 = active
    };
    this.clients.push(newClient);
    return newClient;
  }

  updateClient(id, updates) {
    const client = this.getClientById(id);
    if (!client) return null;
    Object.assign(client, updates);
    return client;
  }

  deleteClient(id) {
    const index = this.clients.findIndex((c) => c.id === id);
    if (index === -1) return false;
    this.clients.splice(index, 1);
    return true;
  }

  // ========== OBJECTTYPE Methods ==========
  getObjectTypes() {
    return [...this.objectTypes];
  }

  getObjectTypeByCode(code) {
    return this.objectTypes.find((ot) => ot.code === code);
  }

  addObjectType(typeData) {
    const newType = {
      code: typeData.code,
      name: typeData.name || "",
      description: typeData.description || "",
      model: typeData.model || "",
      brand: typeData.brand || "",
      quantity: typeData.quantity || 0,
    };
    this.objectTypes.push(newType);
    return newType;
  }

  updateObjectType(code, updates) {
    const type = this.getObjectTypeByCode(code);
    if (!type) return null;
    Object.assign(type, updates);
    return type;
  }

  deleteObjectType(code) {
    const index = this.objectTypes.findIndex((ot) => ot.code === code);
    if (index === -1) return false;
    this.objectTypes.splice(index, 1);
    return true;
  }

  // ========== OBJECT Methods ==========
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

  addObject(objectData) {
    const newObject = {
      id: this.counters.objectId++,
      objectTypeCode: objectData.objectTypeCode,
      state: objectData.state || 0, // 0 = available
      location: objectData.location || "",
    };
    this.objects.push(newObject);
    return newObject;
  }

  updateObject(id, updates) {
    const object = this.getObjectById(id);
    if (!object) return null;
    Object.assign(object, updates);
    return object;
  }

  deleteObject(id) {
    const index = this.objects.findIndex((o) => o.id === id);
    if (index === -1) return false;
    this.objects.splice(index, 1);
    return true;
  }

  // ========== LOANS Methods ==========
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
    return this.loans.filter(
      (l) => l.clientId === clientId && l.state === true,
    );
  }

  addLoan(loanData) {
    const newLoan = {
      id: this.counters.loanId++,
      startDate: loanData.startDate,
      endDate: loanData.endDate || null,
      returnDate: loanData.returnDate || null,
      clientId: loanData.clientId,
      notes: loanData.notes || "",
      quantity: loanData.quantity || 0,
      state: loanData.state !== undefined ? loanData.state : true, // true = active
    };
    this.loans.push(newLoan);
    return newLoan;
  }

  updateLoan(id, updates) {
    const loan = this.getLoanById(id);
    if (!loan) return null;
    Object.assign(loan, updates);
    return loan;
  }

  deleteLoan(id) {
    const index = this.loans.findIndex((l) => l.id === id);
    if (index === -1) return false;
    this.loans.splice(index, 1);
    return true;
  }

  // ========== LOANEDOBJECTS Methods ==========
  getLoanedObjects() {
    return [...this.loanedObjects];
  }

  getLoanedObjectsByLoanId(loanId) {
    return this.loanedObjects.filter((lo) => lo.loanId === loanId);
  }

  getLoanedObjectsByObjectId(objectId) {
    return this.loanedObjects.filter((lo) => lo.objectId === objectId);
  }

  addLoanedObject(loanedObjectData) {
    const newLoanedObject = {
      objectTypeCode: loanedObjectData.objectTypeCode,
      objectId: loanedObjectData.objectId,
      loanId: loanedObjectData.loanId,
      damaged: loanedObjectData.damaged || false,
    };
    this.loanedObjects.push(newLoanedObject);
    return newLoanedObject;
  }

  updateLoanedObject(objectId, loanId, updates) {
    const loanedObject = this.loanedObjects.find(
      (lo) => lo.objectId === objectId && lo.loanId === loanId,
    );
    if (!loanedObject) return null;
    Object.assign(loanedObject, updates);
    return loanedObject;
  }

  deleteLoanedObject(objectId, loanId) {
    const index = this.loanedObjects.findIndex(
      (lo) => lo.objectId === objectId && lo.loanId === loanId,
    );
    if (index === -1) return false;
    this.loanedObjects.splice(index, 1);
    return true;
  }

  // ========== MAINTENANCE Methods ==========
  getMaintenanceRecords() {
    return [...this.maintenance];
  }

  getMaintenanceById(id) {
    return this.maintenance.find((m) => m.id === id);
  }

  getMaintenanceByObjectId(objectId) {
    return this.maintenance.filter((m) => m.objectId === objectId);
  }

  getMaintenanceByTechnicianId(technicianId) {
    return this.maintenance.filter((m) => m.technicianId === technicianId);
  }

  addMaintenance(maintenanceData) {
    const newMaintenance = {
      id: this.counters.maintenanceId++,
      objectId: maintenanceData.objectId,
      description: maintenanceData.description || "",
      startDate: maintenanceData.startDate,
      endDate: maintenanceData.endDate || null,
      type: maintenanceData.type || "",
      technicianId: maintenanceData.technicianId,
      phoneNumber: maintenanceData.phoneNumber || "",
      cost: maintenanceData.cost || 0,
    };
    this.maintenance.push(newMaintenance);
    return newMaintenance;
  }

  updateMaintenance(id, updates) {
    const maintenance = this.getMaintenanceById(id);
    if (!maintenance) return null;
    Object.assign(maintenance, updates);
    return maintenance;
  }

  deleteMaintenance(id) {
    const index = this.maintenance.findIndex((m) => m.id === id);
    if (index === -1) return false;
    this.maintenance.splice(index, 1);
    return true;
  }

  // ========== OPERATIONS Methods ==========
  getOperations() {
    return [...this.operations];
  }

  getOperationById(id) {
    return this.operations.find((o) => o.id === id);
  }

  getOperationsByMaintenanceId(maintenanceId) {
    return this.operations.filter((o) => o.maintenanceId === maintenanceId);
  }

  addOperation(operationData) {
    const newOperation = {
      id: this.counters.operationId++,
      maintenanceId: operationData.maintenanceId,
      description: operationData.description || "",
      startDate: operationData.startDate,
      endDate: operationData.endDate || null,
      cost: operationData.cost || 0,
    };
    this.operations.push(newOperation);
    return newOperation;
  }

  updateOperation(id, updates) {
    const operation = this.getOperationById(id);
    if (!operation) return null;
    Object.assign(operation, updates);
    return operation;
  }

  deleteOperation(id) {
    const index = this.operations.findIndex((o) => o.id === id);
    if (index === -1) return false;
    this.operations.splice(index, 1);
    return true;
  }

  // ========== Helper Methods ==========
  reset() {
    this.initializeDatabase();
  }
}
