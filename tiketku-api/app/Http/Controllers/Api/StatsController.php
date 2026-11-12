<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $perEvent = Event::withCount([
            'seats as terjual' => fn ($q) => $q->where('status', 'sold'),
            'seats as total_kursi',
        ])->orderByDesc('terjual')->limit(5)->get()->map(fn ($e) => [
            'id' => $e->id,
            'nama' => $e->nama,
            'terjual' => $e->terjual,
            'total_kursi' => $e->total_kursi,
        ]);

        return response()->json(['data' => [
            'total_event' => Event::count(),
            'order_pending' => Order::where('status', 'pending')->count(),
            'order_lunas' => Order::where('status', 'paid')->count(),
            'tiket_terjual' => Seat::where('status', 'sold')->count(),
            'pendapatan' => (int) Payment::sum('jumlah'),
            'per_event' => $perEvent,
        ]]);
    }
}
