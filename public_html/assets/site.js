(function () {
  var menu = document.getElementById("mnav");
  var backdrop = document.getElementById("mnav-backdrop");
  var modal = document.getElementById("city-modal");
  var search = document.getElementById("city-search");

  function openMenu() {
    if (!menu) return;
    menu.hidden = false;
    backdrop.hidden = false;
    document.body.style.overflow = "hidden";
  }
  function closeMenu() {
    if (!menu) return;
    menu.hidden = true;
    backdrop.hidden = true;
    document.body.style.overflow = "";
  }
  function openCity() {
    closeMenu();
    if (!modal) return;
    modal.hidden = false;
    document.body.style.overflow = "hidden";
    if (search) search.focus();
  }
  function closeCity() {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = "";
  }

  var ham = document.getElementById("open-menu");
  if (ham) ham.addEventListener("click", openMenu);
  if (backdrop) backdrop.addEventListener("click", closeMenu);
  var closeBtn = document.getElementById("close-menu");
  if (closeBtn) closeBtn.addEventListener("click", closeMenu);
  document.querySelectorAll("[data-open-city]").forEach(function (btn) {
    btn.addEventListener("click", openCity);
  });
  var cityClose = document.getElementById("close-city");
  if (cityClose) cityClose.addEventListener("click", closeCity);
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeCity();
    });
  }
  if (search) {
    search.addEventListener("input", function () {
      var q = search.value.trim().toLowerCase();
      document.querySelectorAll("[data-city]").forEach(function (a) {
        var name = (a.getAttribute("data-city") || "").toLowerCase();
        var url = (a.getAttribute("data-url") || "").toLowerCase();
        a.style.display = !q || name.indexOf(q) !== -1 || url.indexOf(q) !== -1 ? "" : "none";
      });
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeMenu();
      closeCity();
    }
  });
})();
