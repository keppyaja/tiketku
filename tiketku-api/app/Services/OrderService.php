<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh logika transaksi pemesanan tiket ada di sini.
 * Setiap method yang mengubah lebih dari satu tabel dibungkus DB::transaction()
 * sehingga semua perubahan berhasil bersama atau dibatalkan bersama (atomic).
 */
class OrderService
{
    public const HOLD_MINUTES = 15;

    /**
     * Membuat order: mengunci kursi (held) + membuat order + order_items.
     */
    public function create(User $user, Event $event, array $seatIds): Order
    {
        return DB::transaction(function () use ($user, $event, $seatIds) {
            if ($event->tanggal_mulai->isPast()) {
                throw ValidationException::withMessages([
                    'event_id' => 'Event ini sudah berlangsung.',
                ]);
            }

            // Bebaskan dulu kursi dari order pending yang sudah kedaluwarsa.
            $this->releaseExpired();

            $seatIds = array_values(array_unique($seatIds));

            // lockForUpdate: baris kursi dikunci sampai transaksi selesai,
            // sehingga dua pembeli tidak bisa mengambil kursi yang sama.
            $seats = Seat::with('ticketType')
                ->where('event_id', $event->id)
                ->whereIn('id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw ValidationException::withMessages([
                    'seat_ids' => 'Ada kursi yang tidak valid untuk event ini.',
                ]);
            }

            $tidakTersedia = $seats->where('status', '!=', Seat::AVAILABLE);
            if ($tidakTersedia->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'seat_ids' => 'Kursi sudah tidak tersedia: ' . $tidakTersedia->pluck('kode')->implode(', '),
                ]);
            }

            $order = Order::create([
                'kode_order' => 'TKT-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'event_id' => $event->id,
                'total' => $seats->sum(fn ($seat) => $seat->ticketType->harga), // dihitung server, bukan dari client
                'status' => Order::PENDING,
                'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
            ]);

            foreach ($seats as $seat) {
                $order->items()->create([
                    'seat_id' => $seat->id,
                    'harga' => $seat->ticketType->harga,
                ]);
                $seat->update(['status' => Seat::HELD]);
            }

            return $order->load(['event', 'items.seat.ticketType', 'payment']);
        });
    }

    /**
     * Membayar order: catat payment + order jadi paid + kursi jadi sold.
     */
    public function pay(Order $order, string $metode): Order
    {
        return DB::transaction(function () use ($order, $metode) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== Order::PENDING) {
                throw ValidationException::withMessages([
                    'order' => "Order berstatus {$order->status}, tidak bisa dibayar.",
                ]);
            }

            if ($order->expires_at->isPast()) {
                $this->release($order, Order::EXPIRED);
                throw ValidationException::withMessages([
                    'order' => 'Batas waktu pembayaran sudah lewat, order kedaluwarsa.',
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'metode' => $metode,
                'jumlah' => $order->total,
                'referensi' => 'PAY-' . strtoupper(Str::random(10)),
                'dibayar_pada' => now(),
            ]);

            Seat::whereIn('id', $order->items()->pluck('seat_id'))->update(['status' => Seat::SOLD]);
            $order->update(['status' => Order::PAID]);

            return $order->load(['event', 'items.seat.ticketType', 'payment']);
        });
    }

    /**
     * Membatalkan order pending: kursi dikembalikan ke available.
     */
    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== Order::PENDING) {
                throw ValidationException::withMessages([
                    'order' => "Order berstatus {$order->status}, tidak bisa dibatalkan.",
                ]);
            }

            $this->release($order, Order::CANCELLED);

            return $order->load(['event', 'items.seat.ticketType', 'payment']);
        });
    }

    /**
     * Order pending yang lewat batas waktu => expired, kursi kembali available.
     */
    public function releaseExpired(): int
    {
        return DB::transaction(function () {
            $orders = Order::where('status', Order::PENDING)
                ->where('expires_at', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                $this->release($order, Order::EXPIRED);
            }

            return $orders->count();
        });
    }

    private function release(Order $order, string $status): void
    {
        Seat::whereIn('id', $order->items()->pluck('seat_id'))
            ->where('status', Seat::HELD)
            ->update(['status' => Seat::AVAILABLE]);

        $order->update(['status' => $status]);
    }
}
