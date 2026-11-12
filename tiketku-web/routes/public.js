const express = require("express");
const { api, ApiError } = require("../lib/api");

const router = express.Router();

// Daftar event + pencarian + filter kategori
router.get("/", async (req, res, next) => {
  try {
    const params = new URLSearchParams();
    if (req.query.q) params.set("q", req.query.q);
    if (req.query.category_id) params.set("category_id", req.query.category_id);
    if (req.query.page) params.set("page", req.query.page);

    const [events, categories] = await Promise.all([
      api(req, "GET", `/events?${params}`),
      api(req, "GET", "/categories"),
    ]);

    res.render("index", {
      title: "Event",
      events: events.data,
      meta: events.meta,
      categories: categories.data,
      query: req.query,
    });
  } catch (e) {
    next(e);
  }
});

// Detail event + peta kursi
router.get("/events/:id", async (req, res, next) => {
  try {
    const { data: event } = await api(req, "GET", `/events/${req.params.id}`);
    res.render("event", { title: event.nama, event });
  } catch (e) {
    next(e);
  }
});

router.get("/login", (req, res) => res.render("login", { title: "Login" }));

router.post("/login", async (req, res, next) => {
  try {
    const { data } = await api(req, "POST", "/login", {
      email: req.body.email,
      password: req.body.password,
    });
    req.session.token = data.token;
    req.session.user = data.user;
    const dest = req.session.returnTo || (data.user.role === "admin" ? "/admin" : "/");
    delete req.session.returnTo;
    res.redirect(dest);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      return res.status(422).render("login", { title: "Login", errors: e.errors, old: req.body });
    }
    next(e);
  }
});

router.get("/register", (req, res) => res.render("register", { title: "Daftar" }));

router.post("/register", async (req, res, next) => {
  try {
    const { data } = await api(req, "POST", "/register", {
      name: req.body.name,
      email: req.body.email,
      password: req.body.password,
      password_confirmation: req.body.password_confirmation,
    });
    req.session.token = data.token;
    req.session.user = data.user;
    req.session.flash = { type: "success", text: "Pendaftaran berhasil. Selamat datang!" };
    res.redirect("/");
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      return res.status(422).render("register", { title: "Daftar", errors: e.errors, old: req.body });
    }
    next(e);
  }
});

router.post("/logout", async (req, res) => {
  try {
    await api(req, "POST", "/logout");
  } catch (e) {
    /* token mungkin sudah tidak valid, abaikan */
  }
  req.session.destroy(() => res.redirect("/"));
});

module.exports = router;
