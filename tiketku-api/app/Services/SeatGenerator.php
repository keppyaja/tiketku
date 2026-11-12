<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\TicketType;

class SeatGenerator
{
    /**
     * Membuat kursi massal untuk satu jenis tiket.
     * Contoh prefix "V", 2 baris, 5 kursi per baris => VA1..VA5, VB1..VB5
     */
    public static function generate(TicketType $ticketType, string $prefix, int $rows, int $perRow): int
    {
        $now = now();
        $data = [];

        for ($r = 0; $r < $rows; $r++) {
            $huruf = chr(65 + $r);
            for ($n = 1; $n <= $perRow; $n++) {
                $data[] = [
                    'event_id' => $ticketType->event_id,
                    'ticket_type_id' => $ticketType->id,
                    'kode' => strtoupper($prefix) . $huruf . $n,
                    'status' => Seat::AVAILABLE,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Seat::insert($data);

        return count($data);
    }
}
