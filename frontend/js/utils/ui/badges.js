// Get CSS badge class for loan status
export function getLoanStatusBadgeClass(status) {
  if (status === "active") return "badge badge-success";
  if (status === "closed") return "badge badge-neutral";
  return "badge";
}

// Get CSS badge class for inventory item status
export function getItemStatusBadgeClass(status) {
  if (status === "available") return "badge badge-success";
  if (status === "loaned") return "badge badge-warning";
  if (status === "maintenance") return "badge badge-warning";
  if (status === "outofservice") return "badge badge-danger";
  return "badge";
}

// Get CSS badge class for maintenance type
export function getMaintenanceTypeBadgeClass(type) {
  if (type === "repair") return "badge badge-danger";
  if (type === "replacement") return "badge badge-warning";
  if (type === "preventive") return "badge badge-success";
  return "badge";
}
