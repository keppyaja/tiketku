const rupiah = (n) => "Rp " + new Intl.NumberFormat("id-ID").format(Number(n || 0));

const tanggal = (iso) =>
  iso
    ? new Intl.DateTimeFormat("id-ID", {
        dateStyle: "full",
        timeStyle: "short",
        timeZone: "Asia/Jakarta",
      }).format(new Date(iso)) + " WIB"
    : "-";

const statusLabel = {
  pending: "Menunggu pembayaran",
  paid: "Lunas",
  cancelled: "Dibatalkan",
  expired: "Kedaluwarsa",
};

module.exports = { rupiah, tanggal, statusLabel };
