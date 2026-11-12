const express = require("express");
const { api, ApiError } = require("../lib/api");
const { requireLogin } = require("../lib/auth");

const router = express.Router();
router.use(requireLogin);

// Riwayat order milik user
router.get("/", async (req, res, next) => {
  try {
    const page = req.query.page ? `?page=${encodeURIComponent(req.query.page)}` : "";
    const orders = await api(req, "GET", `/orders${page}`);
    res.render("orders", { title: "Pesanan Saya", orders: orders.data, meta: orders.meta });
  } catch (e) {
    next(e);
  }
});

// Buat order dari kursi yang dipilih
router.post("/", async (req, res, next) => {
  const eventId = req.body.event_id;
  try {
    const raw = req.body.seat_ids;
    const seatIds = (Array.isArray(raw) ? raw : raw ? [raw] : []).map(Number);

    const { data } = await api(req, "POST", "/orders", { event_id: Number(eventId), seat_ids: seatIds });
    req.session.flash = { type: "success", text: "Kursi berhasil ditahan. Selesaikan pembayaran sebelum waktu habis." };
    res.redirect(`/orders/${data.id}`);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      const text = Object.values(e.errors).flat().join(" ") || e.message;
      req.session.flash = { type: "error", text };
      return res.redirect(`/events/${eventId}`);
    }
    next(e);
  }
});

router.get("/:id", async (req, res, next) => {
  try {
    const { data: order } = await api(req, "GET", `/orders/${req.params.id}`);
    res.render("order", { title: order.kode_order, order });
  } catch (e) {
    next(e);
  }
});

router.post("/:id/pay", async (req, res, next) => {
  try {
    await api(req, "POST", `/orders/${req.params.id}/pay`, { metode: req.body.metode });
    req.session.flash = { type: "success", text: "Pembayaran berhasil. E-tiket Anda sudah aktif." };
    res.redirect(`/orders/${req.params.id}`);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: Object.values(e.errors).flat().join(" ") || e.message };
      return res.redirect(`/orders/${req.params.id}`);
    }
    next(e);
  }
});

router.post("/:id/cancel", async (req, res, next) => {
  try {
    await api(req, "POST", `/orders/${req.params.id}/cancel`);
    req.session.flash = { type: "success", text: "Order dibatalkan, kursi dilepas kembali." };
    res.redirect(`/orders/${req.params.id}`);
  } catch (e) {
    if (e instanceof ApiError && e.status === 422) {
      req.session.flash = { type: "error", text: Object.values(e.errors).flat().join(" ") || e.message };
      return res.redirect(`/orders/${req.params.id}`);
    }
    next(e);
  }
});

module.exports = router;
