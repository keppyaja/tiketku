<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $this->orders->releaseExpired();

        $events = Event::with(['category', 'ticketTypes'])
            ->withCount(['seats as kursi_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->where('tanggal_mulai', '>', now())
            ->when($request->q, fn ($q, $v) => $q->where('nama', 'like', "%{$v}%"))
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->orderBy('tanggal_mulai')
            ->paginate(9)
            ->withQueryString();

        return EventResource::collection($events);
    }

    public function show(Event $event): EventResource
    {
        $this->orders->releaseExpired();

        $event->load([
            'category',
            'ticketTypes' => fn ($q) => $q->orderBy('harga', 'desc'),
            'ticketTypes.seats' => fn ($q) => $q->orderBy('id'),
        ]);

        return new EventResource($event);
    }

    public function store(Request $request): JsonResponse
    {
        $event = Event::create($this->validated($request));

        return (new EventResource($event->load('category')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Event $event): EventResource
    {
        $event->update($this->validated($request));

        return new EventResource($event->load('category'));
    }

    public function destroy(Event $event): JsonResponse
    {
        abort_if($event->orders()->exists(), 409, 'Event sudah memiliki order, tidak bisa dihapus.');
        $event->delete(); // ticket_types & seats ikut terhapus (cascade)

        return response()->json(['message' => 'Event dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date|after:now',
        ]);
    }
}
