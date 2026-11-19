// Peta kursi interaktif: pilih/batalkan kursi, hitung total, isi hidden input untuk form.
(function () {
  const MAX_SEATS = 6;
  const selected = new Map(); // id -> { kode, price }

  const inputs = document.getElementById("seat-inputs");
  const list = document.getElementById("selected-list");
  const totalEl = document.getElementById("total");
  const btn = document.getElementById("btn-order");

  const rupiah = (n) => "Rp " + new Intl.NumberFormat("id-ID").format(n);

  function render() {
    inputs.replaceChildren();
    let total = 0;
    selected.forEach((seat, id) => {
      total += seat.price;
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "seat_ids";
      input.value = id;
      inputs.appendChild(input);
    });

    list.textContent = selected.size
      ? "Kursi: " + Array.from(selected.values()).map((s) => s.kode).join(", ")
      : "Belum ada kursi dipilih.";
    totalEl.textContent = rupiah(total);
    if (btn) btn.disabled = selected.size === 0;
  }

  document.querySelectorAll(".seat-grid .seat").forEach((el) => {
    el.addEventListener("click", () => {
      const id = el.dataset.id;
      if (selected.has(id)) {
        selected.delete(id);
        el.classList.remove("selected");
      } else {
        if (selected.size >= MAX_SEATS) {
          alert("Maksimal " + MAX_SEATS + " kursi per order.");
          return;
        }
        selected.set(id, { kode: el.dataset.kode, price: Number(el.dataset.price) });
        el.classList.add("selected");
      }
      render();
    });
  });

  render();
})();
