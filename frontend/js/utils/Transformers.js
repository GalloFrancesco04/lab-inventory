export function emailToDisplayName(email) {
  if (!email) return "Unknown";

  return email
    .split("@")[0]
    .replace(/\./g, " ")
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function stateToStatus(state) {
  const stateMap = {
    1: "available",
    2: "loaned",
    3: "maintenance",
    4: "damaged",
    5: "outofservice",
  };
  return stateMap[state] || "available";
}

export function statusToState(status) {
  const statusMap = {
    available: 1,
    loaned: 2,
    maintenance: 3,
    damaged: 4,
    outofservice: 5,
  };
  return statusMap[status] || 1;
}

