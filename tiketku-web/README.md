# TiketKu Web

Web client server-rendered berbasis Express dan EJS yang memanggil REST API
Laravel di `tiketku-api`. Session web menyimpan token Sanctum dan data user
yang sedang login. GSAP digunakan untuk animasi entry halaman, kartu event/order,
dan interaksi kursi.

## Prasyarat

- Node.js >= 18
- npm
- API Laravel berjalan di `http://127.0.0.1:8000`

## Setup dan menjalankan

```bash
cd tiketku-web
npm install
cp .env.example .env       # opsional, untuk mengubah konfigurasi default
npm start                   # http://localhost:3000
```

Jalankan API Laravel lebih dulu. Dari root project, kedua service juga dapat
dijalankan bersamaan dengan `./start.sh`.

Variabel lingkungan opsional:

```bash
PORT=3000
API_URL=http://127.0.0.1:8000/api
SESSION_SECRET=ganti-dengan-string-acak
```

`SESSION_SECRET` harus diganti dengan string acak pada deployment.

Contoh:

```bash
API_URL=http://127.0.0.1:8000/api npm start
```

Untuk pengembangan dengan Node watch mode:

```bash
npm run dev
```

## Halaman

- `/` — daftar event, pencarian, filter kategori
- `/events/:id` — detail event + peta kursi interaktif
- `/register`, `/login` — autentikasi
- `/orders` — riwayat pesanan customer
- `/orders/:id` — detail, hitung mundur pembayaran, bayar/batalkan
- `/admin` — dashboard statistik (khusus admin)
- `/admin/events` — kelola event, kategori, jenis tiket + kursi
- `/admin/orders` — semua order pelanggan

Tema terang/gelap tersedia dari tombol di navigasi dan tersimpan di browser.
Animasi otomatis dinonaktifkan ketika browser mengaktifkan
`prefers-reduced-motion`.

GSAP dimuat dari dependency lokal `gsap` melalui
<https://gsap.com/>.

## Akun demo

- Admin: `admin@tiketku.test` / `admin123`
- Customer: `budi@tiketku.test` / `budi123`
