# TiketKu

Aplikasi pemesanan tiket event: **Laravel 12 REST API** + **Express/EJS web client**.

## Struktur

```
tiketku-api/   REST API Laravel 12 (Sanctum, SQLite/MySQL)
tiketku-web/   Web client Express + EJS
docs/          ERD proyek
```

## Quick start

Pastikan dependency sudah terpasang satu kali:

```bash
cd tiketku-api && composer install
cd ../tiketku-web && npm install
cd ..
```

Jalankan API dan web bersamaan dari root project:

```bash
./start.sh
```

Buka <http://localhost:3000>. API berjalan di <http://127.0.0.1:8000>.

Tekan `Ctrl+C` untuk menghentikan API dan web sekaligus. Jika database belum
diinisialisasi, jalankan sekali:

```bash
cd tiketku-api
php artisan migrate:fresh --seed
cd ..
```

## Akun demo

| Role     | Email                | Password |
| -------- | -------------------- | -------- |
| Admin    | `admin@tiketku.test` | `admin123` |
| Customer | `budi@tiketku.test`  | `budi123`  |

## Fitur

- Daftar, cari, dan filter event berdasarkan kategori
- Peta kursi interaktif (maksimal 6 kursi per order)
- Penahanan kursi 15 menit + hitung mundur pembayaran
- Pembayaran simulasi (transfer, QRIS, kartu)
- Pembatalan/kedaluwarsa order otomatis melepas kursi
- Dashboard admin: statistik, event, kategori, jenis tiket, semua order
- Otorisasi customer/admin dan transaksi database yang atomik

Instruksi lebih lengkap ada di `tiketku-api/README.md` dan
`tiketku-web/README.md`.
