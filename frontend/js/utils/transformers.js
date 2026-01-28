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

export function formatDate(date) {
  if (!date) return "-";

  const d = typeof date === "string" ? new Date(date) : date;
  if (isNaN(d.getTime())) return "-";

  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

export function formatCurrency(amount) {
  if (typeof amount !== "number") return "€0.00";

  return `€${amount.toFixed(2)}`;
}

export function formatStatus(status) {
  if (!status) return "-";

  return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
}

export function truncateText(text, maxLength = 50) {
  if (!text) return "";

  if (text.length <= maxLength) return text;

  return text.substring(0, maxLength) + "...";
}

export function formatPhoneNumber(phone) {
  if (!phone) return "-";

  // Remove non-digits
  const cleaned = phone.replace(/\D/g, "");

  // Format as +39 XXX XXXX XXXX
  if (cleaned.length === 10) {
    return `+39 ${cleaned.substring(0, 3)} ${cleaned.substring(3, 7)} ${cleaned.substring(7)}`;
  }

  return phone;
}
