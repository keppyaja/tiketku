# TiketKu API (Laravel 12)

REST API untuk aplikasi pemesanan tiket event. Proyek ini sudah berupa aplikasi
Laravel 12 lengkap (skeleton + `vendor` + Sanctum) dan siap dijalankan.

## Menjalankan

```bash
cd tiketku-api
composer install                    # hanya jika folder vendor belum ada
cp .env.example .env                # hanya jika .env belum ada
php artisan key:generate            # hanya jika APP_KEY belum terisi
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve                   # http://127.0.0.1:8000
```

Database default memakai **SQLite** (`database/database.sqlite`) agar langsung
bisa dipakai tanpa konfigurasi server database. Untuk memakai MySQL, ubah `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiketku
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `tiketku` terlebih dahulu, lalu jalankan lagi
`php artisan migrate:fresh --seed`.

## Akun demo

| Role     | Email                 | Password |
| -------- | --------------------- | -------- |
| Admin    | `admin@tiketku.test`  | `admin123` |
| Customer | `budi@tiketku.test`   | `budi123`  |

## Perintah penting

```bash
php artisan test                    # 6 test, termasuk alur order & pembayaran
php artisan tiket:release-expired   # lepaskan kursi dari order kedaluwarsa
php artisan schedule:work           # jalankan pengecekan otomatis tiap menit
```

Jadwal `tiket:release-expired` sudah terdaftar di `routes/console.php`.

## Catatan API

- Semua request wajib memakai header `Accept: application/json`.
- Autentikasi memakai Bearer token dari Sanctum:
  `POST /api/login` lalu kirim `Authorization: Bearer <token>`.
- Endpoint publik: kategori, daftar event, detail event.
- Endpoint customer: order, pembayaran, pembatalan.
- Endpoint admin: statistik, CRUD event/kategori, tambah jenis tiket + kursi.

Zona waktu aplikasi memakai `APP_TIMEZONE=Asia/Jakarta`.
