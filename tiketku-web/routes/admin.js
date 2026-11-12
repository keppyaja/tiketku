const express = require("express");
const { api, ApiError } = require("../lib/api");
const { requireLogin, requireAdmin } = require("../lib/auth");

const router = express.Router();
router.use(requireLogin, requireAdmin);

const apiErrorText = (e) => Object.values(e.errors || {}).flat().join(" ") || e.message;

// Dashboard statistik (GET /api/stats)
router.get("/", async (req, res, next) => {
  try {
    const { data: stats } = await api(req, "GET", "/stats");
    res.render("admin/dashboard", { title: "Dashboard Admin", stats });
  } catch (e) {
    next(e);
  }
});

// Kelola event + kategori
router.get("/events", async (req, res, next) => {
  try {
    const [events, categories] = await Promise.all([
      api(req, "GET", "/events"),
      api(req, "GET", "/categories"),
    ]);
    res.render("admin/events", { title: "Kelola Event", events: events.data, categories: categories.data });
  } catch (e) {
    next(e);
  }
});

router.post("/events", async (req, res, next) => {
  try {
    const { data } = await api(req, "POST", "/events", {
      category_id: Number(req.body.category_id),
      nama: req.body.nama,
      deskripsi: req.body.deskripsi,
      lokasi: req.body.lokasi,
      tanggal_mulai: req.body.tanggal_mulai,
    });
    req.session.flash = { type: "success", text: "Event dibuat. Tambahkan jenis tiket dan kursinya." };
    res.redirect(`/admin/events/${data.id}`);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: apiErrorText(e) };
      return res.redirect("/admin/events");
    }
    next(e);
  }
});

router.get("/events/:id", async (req, res, next) => {
  try {
    const { data: event } = await api(req, "GET", `/events/${req.params.id}`);
    res.render("admin/event-detail", { title: event.nama, event });
  } catch (e) {
    next(e);
  }
});

router.post("/events/:id/ticket-types", async (req, res, next) => {
  try {
    const { data } = await api(req, "POST", `/events/${req.params.id}/ticket-types`, {
      nama: req.body.nama,
      harga: Number(req.body.harga),
      prefix: req.body.prefix,
      jumlah_baris: Number(req.body.jumlah_baris),
      kursi_per_baris: Number(req.body.kursi_per_baris),
    });
    req.session.flash = { type: "success", text: `Jenis tiket ${data.nama} dibuat dengan ${data.jumlah_kursi} kursi.` };
    res.redirect(`/admin/events/${req.params.id}`);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: apiErrorText(e) };
      return res.redirect(`/admin/events/${req.params.id}`);
    }
    next(e);
  }
});

router.post("/events/:id/delete", async (req, res, next) => {
  try {
    await api(req, "DELETE", `/events/${req.params.id}`);
    req.session.flash = { type: "success", text: "Event dihapus." };
    res.redirect("/admin/events");
  } catch (e) {
    if (e instanceof ApiError && e.status === 409) {
      req.session.flash = { type: "error", text: e.message };
      return res.redirect("/admin/events");
    }
    next(e);
  }
});

router.post("/categories", async (req, res, next) => {
  try {
    await api(req, "POST", "/categories", { nama: req.body.nama });
    req.session.flash = { type: "success", text: "Kategori ditambahkan." };
    res.redirect("/admin/events");
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: apiErrorText(e) };
      return res.redirect("/admin/events");
    }
    next(e);
  }
});

// Semua order (admin melihat seluruh pelanggan)
router.get("/orders", async (req, res, next) => {
  try {
    const params = new URLSearchParams();
    if (req.query.status) params.set("status", req.query.status);
    if (req.query.page) params.set("page", req.query.page);
    const orders = await api(req, "GET", `/orders?${params}`);
    res.render("admin/orders", { title: "Semua Order", orders: orders.data, meta: orders.meta, query: req.query });
  } catch (e) {
    next(e);
  }
});

router.post("/orders/:id/refund", async (req, res, next) => {
  try {
    await api(req, "POST", `/orders/${req.params.id}/refund`);
    req.session.flash = { type: "success", text: "Tiket berhasil direfund dan kursi tersedia kembali." };
    res.redirect("/admin/orders");
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: apiErrorText(e) };
      return res.redirect("/admin/orders");
    }
    next(e);
  }
});

module.exports = router;
