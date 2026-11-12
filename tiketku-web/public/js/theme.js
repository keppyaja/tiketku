(() => {
  const toggle = document.getElementById("theme-toggle");
  if (!toggle) return;

  const setTheme = (dark) => {
    document.body.classList.toggle("dark-theme", dark);
    toggle.textContent = dark ? "☀️" : "🌙";
    toggle.setAttribute("aria-label", dark ? "Aktifkan tema terang" : "Aktifkan tema gelap");
    toggle.setAttribute("aria-pressed", String(dark));
  };

  const storedTheme = localStorage.getItem("theme");
  setTheme(storedTheme === "dark");

  toggle.addEventListener("click", () => {
    const dark = !document.body.classList.contains("dark-theme");
    localStorage.setItem("theme", dark ? "dark" : "light");
    setTheme(dark);
  });
})();
