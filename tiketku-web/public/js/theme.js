(() => {
  const toggle = document.getElementById("theme-toggle");
  if (!toggle) return;

  const setTheme = (theme) => {
    const dark = theme === "dark";
    document.body.classList.toggle("dark-theme", dark);
    toggle.textContent = dark ? "☀️" : "🌙";
    toggle.setAttribute("aria-label", dark ? "Aktifkan tema terang" : "Aktifkan tema gelap");
    toggle.setAttribute("aria-pressed", String(dark));
  };

  const getTheme = () => {
    const storedTheme = localStorage.getItem("theme");
    return storedTheme === "dark" || storedTheme === "light"
      ? storedTheme
      : matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  };

  const setStoredTheme = (theme) => {
    localStorage.setItem("theme", theme);
    document.querySelector('meta[name="color-scheme"]').content = theme;
    setTheme(theme);
  };

  setTheme(getTheme());

  toggle.addEventListener("click", () => {
    setStoredTheme(document.body.classList.contains("dark-theme") ? "light" : "dark");
  });

  addEventListener("storage", (event) => {
    if (event.key === "theme") setTheme(getTheme());
  });
})();
