# TiketKu API

REST API Laravel 12 untuk aplikasi pemesanan tiket event. API menggunakan
Sanctum untuk autentikasi token dan SQLite sebagai database default.

## Prasyarat

- PHP >= 8.2
- Composer

## Setup dan menjalankan

```bash
cd tiketku-api
composer install
cp .env.example .env                # hanya jika .env belum ada
php artisan key:generate             # hanya jika APP_KEY belum terisi
touch database/database.sqlite
php artisan migrate:fresh --seed     # database baru; menghapus data lama
php artisan serve --host=127.0.0.1 --port=8000
```

Database default memakai **SQLite** (`database/database.sqlite`) sehingga tidak
memerlukan server database tambahan. Jika database sudah berisi data, jalankan
`php artisan migrate` untuk menerapkan skema terbaru tanpa menghapus data.

Untuk memakai MySQL, ubah `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiketku
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `tiketku` terlebih dahulu, lalu jalankan lagi
`php artisan migrate --seed`. Jangan gunakan `migrate:fresh` pada database yang
datanya ingin dipertahankan.

## Akun demo

| Role     | Email                 | Password |
| -------- | --------------------- | -------- |
| Admin    | `admin@tiketku.test`  | `admin123` |
| Customer | `budi@tiketku.test`   | `budi123`  |

## Perintah penting

```bash
php artisan test
php artisan tiket:release-expired   # lepaskan kursi kedaluwarsa satu kali
php artisan schedule:work           # jalankan command terjadwal tiap menit
```

Jadwal `tiket:release-expired` sudah terdaftar setiap menit di
`routes/console.php`. Jalankan `schedule:work` di terminal terpisah dari
server API jika ingin pelepasan kursi berjalan otomatis.

## Endpoint

- Semua request wajib memakai header `Accept: application/json`.
- Endpoint publik: `POST /api/register`, `POST /api/login`, kategori, daftar
  event, detail event, dan `GET /api/users/{id}/avatar` (hanya berkas foto).
- Endpoint terautentikasi memakai Bearer token Sanctum dari `POST /api/login`
  melalui header `Authorization: Bearer <token>`.
- Endpoint customer: `GET /api/me`, `POST /api/profile` (`multipart/form-data`)
  untuk memperbarui username/foto profil, daftar/detail order, pembuatan order,
  pembayaran, dan pembatalan.
- Endpoint admin: statistik, CRUD event/kategori, tambah jenis tiket dan kursi,
  serta refund order.

Field `username` wajib unik (3–30 karakter). Field `avatar` opsional menerima
JPG, PNG, atau WebP hingga 2 MB dan ukuran 64×64–2048×2048 piksel. Field
`remove_avatar=1` menghapus avatar lama. Backend memeriksa tipe serta dimensi
gambar, menyimpan berkas dengan nama acak di disk privat, dan membatasi update
profil hingga 10 permintaan per menit per pengguna. Endpoint avatar hanya
menyajikan berkas gambar; path penyimpanan dan data profil lain tidak dibuka.

Zona waktu aplikasi memakai `APP_TIMEZONE=Asia/Jakarta`.
