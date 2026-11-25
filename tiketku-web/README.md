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
npm start                   # http://localhost:3000
```

Jalankan API Laravel lebih dulu. Dari root project, kedua service juga dapat
dijalankan bersamaan dengan `./start.sh`.

Variabel lingkungan dibaca dari environment proses (file `.env` tidak dimuat
otomatis):

| Variabel | Penggunaan |
| -------- | ---------- |
| `PORT` | Port web; default `3000`. |
| `API_URL` | URL dasar API; default `http://127.0.0.1:8000/api`. |
| `NODE_ENV` | Gunakan `production` saat deployment agar cookie memakai `Secure`. |
| `SESSION_SECRET` | Wajib di production; gunakan secret acak minimal 32 byte. |

Di development, session secret acak dibuat otomatis bila tidak disetel. Untuk
deployment, set `NODE_ENV=production` dan `SESSION_SECRET` yang acak melalui
environment deployment; aplikasi menolak start production tanpa secret.
Cookie session menggunakan `HttpOnly`, `SameSite=Lax`, dan `Secure` di
production. Jangan commit secret ke repository.

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
- `/profile` — ubah username unik dan foto profil; login tetap memakai email
- `/admin` — dashboard statistik (khusus admin)
- `/admin/events` — kelola event, kategori, jenis tiket + kursi
- `/admin/orders` — semua order pelanggan

Tema terang/gelap tersedia dari tombol di navigasi dan tersimpan di browser.
Animasi otomatis dinonaktifkan ketika browser mengaktifkan
`prefers-reduced-motion`.

Foto profil menerima JPG, PNG, atau WebP, maksimal 2 MB dan 2048×2048 piksel.
Server memeriksa tipe serta dimensi gambar, membatasi ukuran request, dan
permintaan ubah profil dilindungi token CSRF. Avatar disajikan melalui endpoint
API khusus; berkas tidak disimpan di direktori publik.

GSAP dimuat dari dependency lokal `gsap` melalui
<https://gsap.com/>.

## Akun demo

- Admin: `admin@tiketku.test` / `admin123`
- Customer: `budi@tiketku.test` / `budi123`
