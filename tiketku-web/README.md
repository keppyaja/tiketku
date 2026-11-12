# TiketKu Web (Express + Node)

Front-end server-rendered (EJS) yang memanggil REST API Laravel (`tiketku-api`).

## Menjalankan

```bash
cd tiketku-web
npm install
npm start          # http://localhost:3000
```

Jalankan API Laravel lebih dulu di `http://127.0.0.1:8000`.

Variabel lingkungan opsional:

```bash
PORT=3000
API_URL=http://127.0.0.1:8000/api
SESSION_SECRET=ganti-dengan-string-acak
```

Contoh: `API_URL=http://127.0.0.1:8000/api npm start`.

## Halaman

- `/` — daftar event, pencarian, filter kategori
- `/events/:id` — detail event + peta kursi interaktif
- `/register`, `/login` — autentikasi
- `/orders` — riwayat pesanan customer
- `/orders/:id` — detail, hitung mundur pembayaran, bayar/batalkan
- `/admin` — dashboard statistik (khusus admin)
- `/admin/events` — kelola event, kategori, jenis tiket + kursi
- `/admin/orders` — semua order pelanggan

## Akun demo

- Admin: `admin@tiketku.test` / `admin123`
- Customer: `budi@tiketku.test` / `budi123`
