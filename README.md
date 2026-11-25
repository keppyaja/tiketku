# TiketKu

Aplikasi pemesanan tiket event dengan arsitektur dua service:

- **Laravel 12 REST API** untuk autentikasi, event, kursi, order, pembayaran,
  dan dashboard admin.
- **Express + EJS web client** sebagai antarmuka server-rendered untuk customer
  dan admin, dengan animasi GSAP.

## Teknologi dan struktur

```
tiketku-api/   Laravel 12 API (Sanctum, SQLite/MySQL)
tiketku-web/   Express/EJS web client
start.sh       Menjalankan API dan web secara bersamaan
```

## Prasyarat

- PHP >= 8.2 dan Composer
- Node.js >= 18 dan npm
- Bash untuk menjalankan `start.sh`

## Menjalankan project

Install dependency masing-masing service:

```bash
cd tiketku-api && composer install
cd ../tiketku-web && npm install
cd ..
```

Siapkan konfigurasi API jika belum ada:

```bash
cd tiketku-api
cp .env.example .env                # hanya jika .env belum ada
php artisan key:generate             # hanya jika APP_KEY belum terisi
touch database/database.sqlite
php artisan migrate:fresh --seed     # setup database baru; menghapus data lama
cd ..
```

Jika database sudah berisi data, jalankan `cd tiketku-api && php artisan migrate`
agar skema baru (termasuk profil pengguna) diterapkan tanpa menghapus data.

Jalankan kedua service dari root project:

```bash
./start.sh
```

Setelah itu:

- Web: <http://localhost:3000>
- API: <http://127.0.0.1:8000>

Tekan `Ctrl+C` untuk menghentikan keduanya. `start.sh` membutuhkan folder
`tiketku-api/vendor` dan `tiketku-web/node_modules` yang sudah tersedia.

Untuk melepas kursi dari order yang kedaluwarsa secara otomatis, jalankan
scheduler Laravel di terminal lain:

```bash
cd tiketku-api
php artisan schedule:work
cd ..
```

Alternatifnya, perintah satu kali dapat dijalankan secara manual:

```bash
cd tiketku-api
php artisan tiket:release-expired
```

## Akun demo

| Role     | Email                | Password |
| -------- | -------------------- | -------- |
| Admin    | `admin@tiketku.test` | `admin123` |
| Customer | `budi@tiketku.test`   | `budi123`  |

## Fitur

- Daftar, cari, dan filter event berdasarkan kategori
- Profil customer: username unik, upload/preview/hapus foto profil
  (JPG/PNG/WebP, maksimal 2 MB)
- Peta kursi interaktif (maksimal 6 kursi per order)
- Penahanan kursi 15 menit dan hitung mundur pembayaran
- Pembayaran simulasi (transfer, QRIS, kartu)
- Pembatalan, refund, dan kedaluwarsa order melepas kursi
- Dashboard admin: statistik, event, kategori, jenis tiket, dan semua order
- Otorisasi customer/admin dengan Sanctum
- Transaksi database atomik saat membuat order dan mengubah status kursi
- Form profil dilindungi CSRF; upload divalidasi server dan dibatasi per pengguna

## Pengujian

Jalankan test API dari folder `tiketku-api`:

```bash
php artisan test
```

Dokumentasi detail tersedia di [tiketku-api/README.md](tiketku-api/README.md)
dan [tiketku-web/README.md](tiketku-web/README.md).
