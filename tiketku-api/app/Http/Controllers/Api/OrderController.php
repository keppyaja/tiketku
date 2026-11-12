<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Event;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const RELATIONS = ['event', 'user', 'items.seat.ticketType', 'payment'];

    public function __construct(private OrderService $service)
    {
    }

    public function index(Request $request)
    {
        $this->service->releaseExpired();
        $user = $request->user();

        $orders = Order::with(self::RELATIONS)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->service->releaseExpired();
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 403, 'Bukan order Anda.');

        return new OrderResource($order->fresh()->load(self::RELATIONS));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'seat_ids' => 'required|array|min:1|max:6',
            'seat_ids.*' => 'integer',
        ], [
            'seat_ids.required' => 'Pilih minimal 1 kursi.',
            'seat_ids.max' => 'Maksimal 6 kursi per order.',
        ]);

        $order = $this->service->create($request->user(), Event::findOrFail($data['event_id']), $data['seat_ids']);

        return (new OrderResource($order->load('user')))->response()->setStatusCode(201);
    }

    public function pay(Request $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 403, 'Bukan order Anda.');

        $data = $request->validate(['metode' => 'required|in:transfer,qris,kartu']);

        return new OrderResource($this->service->pay($order, $data['metode'])->load('user'));
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        abort_unless($request->user()->isAdmin() || $order->user_id === $request->user()->id, 403, 'Bukan order Anda.');

        return new OrderResource($this->service->cancel($order)->load('user'));
    }
}
