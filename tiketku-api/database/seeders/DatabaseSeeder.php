<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Services\SeatGenerator;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin TiketKu', 'username' => 'admin_tiketku', 'email' => 'admin@tiketku.test', 'password' => 'admin123', 'role' => 'admin']);
        User::create(['name' => 'Budi Santoso', 'username' => 'budi_santoso', 'email' => 'budi@tiketku.test', 'password' => 'budi123', 'role' => 'customer']);

        $kategori = collect(['Konser', 'Seminar', 'Olahraga'])
            ->mapWithKeys(fn ($nama) => [$nama => Category::create(['nama' => $nama])]);

        $events = [
            ['Konser Senja Surabaya', 'Konser', 'Lapangan Thor, Surabaya', 14, 'Konser musik akustik bersama band lokal Jawa Timur.', 350000, 150000],
            ['Seminar AI untuk Mahasiswa', 'Seminar', 'Aula Kampus, Surabaya', 7, 'Seminar pengenalan AI dan karier di bidang data.', 100000, 50000],
            ['Futsal Cup Antar Kampus', 'Olahraga', 'GOR Delta, Sidoarjo', 21, 'Turnamen futsal antar kampus se-Jawa Timur.', 75000, 25000],
        ];

        foreach ($events as [$nama, $kat, $lokasi, $hari, $deskripsi, $hargaVip, $hargaReguler]) {
            $event = Event::create([
                'category_id' => $kategori[$kat]->id,
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'lokasi' => $lokasi,
                'tanggal_mulai' => now()->addDays($hari)->setTime(19, 0),
            ]);

            $vip = $event->ticketTypes()->create(['nama' => 'VIP', 'harga' => $hargaVip]);
            SeatGenerator::generate($vip, 'V', 2, 5);

            $reguler = $event->ticketTypes()->create(['nama' => 'Reguler', 'harga' => $hargaReguler]);
            SeatGenerator::generate($reguler, 'R', 3, 8);
        }
    }
}
