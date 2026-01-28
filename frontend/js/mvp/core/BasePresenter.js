export class BasePresenter {
  constructor(initialData = []) {
    this.data = initialData;
    this.listeners = [];
  }

  // Get all items
  getAll() {
    return [...this.data];
  }

  //Get item by id
  getById(id) {
    return this.data.find((item) => item.id == id);
  }

  // Add a new item
  add(item) {
    this.data.push(item);
    this.notifyListeners();
    return item;
  }

  // Update an item by id
  update(id, updates) {
    const index = this.data.findIndex((item) => item.id == id);
    if (index === -1) return false;
    this.data[index] = { ...this.data[index], ...updates };
    this.notifyListeners();
    return true;
  }

  // Delete an item by id
  delete(id) {
    const index = this.data.findIndex((item) => item.id == id);
    if (index === -1) return false;
    this.data.splice(index, 1);
    this.notifyListeners();
    return true;
  }

  // Subscribe to data changes
  subscribe(callback) {
    this.listeners.push(callback);
  }

  // Notify all subscribers of data changes
  notifyListeners() {
    this.listeners.forEach((callback) => callback());
  }
}
