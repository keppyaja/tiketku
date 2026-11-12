// Hitung mundur batas waktu pembayaran; reload halaman saat habis.
(function () {
  const el = document.getElementById("countdown");
  if (!el) return;
  const end = new Date(el.dataset.expires).getTime();

  function tick() {
    const sisa = Math.max(0, Math.floor((end - Date.now()) / 1000));
    const m = String(Math.floor(sisa / 60)).padStart(2, "0");
    const s = String(sisa % 60).padStart(2, "0");
    el.textContent = m + ":" + s;
    if (sisa === 0) {
      clearInterval(timer);
      setTimeout(() => location.reload(), 1500);
    }
  }
  const timer = setInterval(tick, 1000);
  tick();
})();
