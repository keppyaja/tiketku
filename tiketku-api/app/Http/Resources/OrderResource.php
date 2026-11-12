<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_order' => $this->kode_order,
            'total' => $this->total,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event->id,
                'nama' => $this->event->nama,
                'lokasi' => $this->event->lokasi,
                'tanggal_mulai' => $this->event->tanggal_mulai,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'seats' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'kode' => $item->seat?->kode,
                'tipe' => $item->seat?->ticketType?->nama,
                'harga' => $item->harga,
            ])->values()),
            'payment' => $this->whenLoaded('payment', fn () => [
                'metode' => $this->payment->metode,
                'jumlah' => $this->payment->jumlah,
                'referensi' => $this->payment->referensi,
                'dibayar_pada' => $this->payment->dibayar_pada,
            ]),
        ];
    }
}
