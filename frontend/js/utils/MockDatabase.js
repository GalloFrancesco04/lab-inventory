export class MockDatabase {
  static getInstance() {
    if (!MockDatabase.instance) {
      MockDatabase.instance = new MockDatabase();
    }
    return MockDatabase.instance;
  }

  constructor() {
    this.clients = ["Mario Sale", "Sale Mario", "Marius Salis"];
    this.technicians = ["M. Rossi", "S. Bianchi", "R. Verdi", "A. Neri"];
  }

  getClients() {
    return [...this.clients];
  }

  getTechnicians() {
    return [...this.technicians];
  }

  addClient(name) {
    if (!name || this.clients.includes(name)) {
      return false;
    }
    this.clients.push(name);
    return true;
  }

  addTechnician(name) {
    if (!name || this.technicians.includes(name)) {
      return false;
    }
    this.technicians.push(name);
    return true;
  }
}
