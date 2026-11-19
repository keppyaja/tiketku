const path = require("path");
const express = require("express");
const session = require("express-session");
const { ApiError } = require("./lib/api");
const helpers = require("./lib/helpers");

const app = express();

app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "views"));

app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, "public")));
app.use("/vendor/gsap", express.static(path.join(__dirname, "node_modules/gsap/dist")));
app.use(
  session({
    secret: process.env.SESSION_SECRET || "dev-secret-tiketku",
    resave: false,
    saveUninitialized: false,
    cookie: { httpOnly: true, maxAge: 1000 * 60 * 60 * 8 },
  })
);

// Variabel yang tersedia di semua view
app.use((req, res, next) => {
  res.locals.currentUser = req.session.user || null;
  res.locals.flash = req.session.flash || null;
  res.locals.old = {};
  res.locals.errors = {};
  res.locals.path = req.path;
  Object.assign(res.locals, helpers);
  delete req.session.flash;
  next();
});

app.use("/", require("./routes/public"));
app.use("/orders", require("./routes/orders"));
app.use("/admin", require("./routes/admin"));

app.use((req, res) => {
  res.status(404).render("error", { title: "404", message: "Halaman tidak ditemukan." });
});

// Penanganan error terpusat
app.use((err, req, res, next) => {
  if (err instanceof ApiError && err.status === 401) {
    req.session.destroy(() => res.redirect("/login"));
    return;
  }
  const status = err.status || 500;
  console.error(err.message);
  res.status(status).render("error", {
    title: `Error ${status}`,
    message: err.message || "Terjadi kesalahan.",
  });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`TiketKu web berjalan di http://localhost:${PORT}`));
