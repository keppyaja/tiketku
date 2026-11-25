const crypto = require("node:crypto");
const express = require("express");
const multer = require("multer");
const { api, ApiError } = require("../lib/api");
const { requireLogin } = require("../lib/auth");

const router = express.Router();
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 2 * 1024 * 1024,
    files: 1,
    fields: 3,
    fieldSize: 256,
    parts: 4,
  },
});

router.use(requireLogin);

function parseUpload(req, res, next) {
  upload.single("avatar")(req, res, (error) => {
    if (!error) return next();
    if (!(error instanceof multer.MulterError)) return next(error);

    req.session.flash = {
      type: "error",
      text: error.code === "LIMIT_FILE_SIZE"
        ? "Ukuran foto maksimal 2 MB."
        : "Data upload tidak valid. Pilih satu foto profil lalu coba lagi.",
    };
    res.redirect("/profile");
  });
}

function verifyCsrf(req, res, next) {
  const expected = req.session.csrfToken;
  const submitted = req.body?._csrf;

  if (typeof expected !== "string" || typeof submitted !== "string") {
    return res.status(403).render("error", {
      title: "Permintaan ditolak",
      message: "Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.",
    });
  }

  const expectedBuffer = Buffer.from(expected);
  const submittedBuffer = Buffer.from(submitted);
  if (
    expectedBuffer.length !== submittedBuffer.length
    || !crypto.timingSafeEqual(expectedBuffer, submittedBuffer)
  ) {
    return res.status(403).render("error", {
      title: "Permintaan ditolak",
      message: "Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.",
    });
  }

  next();
}

router.get("/", async (req, res, next) => {
  try {
    const { data: user } = await api(req, "GET", "/me");
    req.session.user = user;
    req.session.csrfToken ||= crypto.randomBytes(32).toString("hex");

    res.render("profile", {
      title: "Profil Saya",
      user,
      csrfToken: req.session.csrfToken,
      errors: {},
      old: {},
    });
  } catch (error) {
    next(error);
  }
});

router.post("/", parseUpload, verifyCsrf, async (req, res, next) => {
  const payload = new FormData();
  payload.set("username", typeof req.body.username === "string" ? req.body.username : "");

  if (req.file) {
    payload.set(
      "avatar",
      new Blob([req.file.buffer], { type: req.file.mimetype }),
      "avatar"
    );
  }
  if (req.body.remove_avatar === "1") {
    payload.set("remove_avatar", "1");
  }

  try {
    const { data: user } = await api(req, "POST", "/profile", payload);
    req.session.user = user;
    req.session.flash = { type: "success", text: "Profil berhasil diperbarui." };
    res.redirect("/profile");
  } catch (error) {
    if (error instanceof ApiError && error.status === 422) {
      return res.status(422).render("profile", {
        title: "Profil Saya",
        user: {
          ...req.session.user,
          username: typeof req.body.username === "string"
            ? req.body.username
            : req.session.user.username,
        },
        csrfToken: req.session.csrfToken,
        errors: error.errors,
        old: req.body,
      });
    }

    next(error);
  }
});

module.exports = router;
