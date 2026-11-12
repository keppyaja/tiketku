function requireLogin(req, res, next) {
  if (!req.session.user) {
    req.session.flash = { type: "error", text: "Silakan login terlebih dahulu." };
    req.session.returnTo = req.originalUrl;
    return res.redirect("/login");
  }
  next();
}

function requireAdmin(req, res, next) {
  if (!req.session.user || req.session.user.role !== "admin") {
    return res.status(403).render("error", { title: "Akses ditolak", message: "Halaman ini khusus admin." });
  }
  next();
}

module.exports = { requireLogin, requireAdmin };
