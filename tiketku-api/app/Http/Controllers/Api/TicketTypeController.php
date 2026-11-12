<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Seat;
use App\Services\SeatGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketTypeController extends Controller
{
    /**
     * Menambah jenis tiket sekaligus membuat kursinya (satu transaksi).
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:50|unique:ticket_types,nama,NULL,id,event_id,' . $event->id,
            'harga' => 'required|integer|min:0',
            'prefix' => 'required|alpha|max:3',
            'jumlah_baris' => 'required|integer|min:1|max:26',
            'kursi_per_baris' => 'required|integer|min:1|max:50',
        ]);

        $prefix = strtoupper($data['prefix']);

        if (Seat::where('event_id', $event->id)->where('kode', 'like', $prefix . '%')->exists()) {
            throw ValidationException::withMessages(['prefix' => 'Prefix kursi sudah dipakai di event ini.']);
        }

        $ticketType = DB::transaction(function () use ($event, $data, $prefix) {
            $ticketType = $event->ticketTypes()->create([
                'nama' => $data['nama'],
                'harga' => $data['harga'],
            ]);

            SeatGenerator::generate($ticketType, $prefix, $data['jumlah_baris'], $data['kursi_per_baris']);

            return $ticketType;
        });

        return response()->json([
            'data' => [
                'id' => $ticketType->id,
                'nama' => $ticketType->nama,
                'harga' => $ticketType->harga,
                'jumlah_kursi' => $data['jumlah_baris'] * $data['kursi_per_baris'],
            ],
        ], 201);
    }
}
