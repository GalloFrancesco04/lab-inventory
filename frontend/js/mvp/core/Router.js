export function showView(viewId) {
  document.querySelectorAll(".table-container").forEach((el) => {
    el.classList.add("hidden");
  });
  document.getElementById(viewId).classList.remove("hidden");
}
