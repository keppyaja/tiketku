<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'lokasi' => $this->lokasi,
            'tanggal_mulai' => $this->tanggal_mulai,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'nama' => $this->category->nama,
            ]),
            'harga_mulai' => $this->whenLoaded('ticketTypes', fn () => $this->ticketTypes->min('harga')),
            'kursi_tersedia' => $this->when(isset($this->kursi_tersedia), fn () => $this->kursi_tersedia),
            'ticket_types' => $this->whenLoaded('ticketTypes', fn () => $this->ticketTypes->map(
                fn ($t) => array_merge(
                    ['id' => $t->id, 'nama' => $t->nama, 'harga' => $t->harga],
                    $t->relationLoaded('seats')
                        ? ['seats' => $t->seats->map(fn ($s) => [
                            'id' => $s->id,
                            'kode' => $s->kode,
                            'status' => $s->status,
                        ])->values()]
                        : []
                )
            )->values()),
        ];
    }
}
