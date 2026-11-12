<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Order;
use App\Models\Seat;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SeatGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function buatEvent(): Event
    {
        $event = Event::create([
            'category_id' => Category::create(['nama' => 'Konser'])->id,
            'nama' => 'Event Uji',
            'lokasi' => 'Surabaya',
            'tanggal_mulai' => now()->addDays(5),
        ]);
        $tipe = $event->ticketTypes()->create(['nama' => 'Reguler', 'harga' => 100000]);
        SeatGenerator::generate($tipe, 'R', 2, 3);

        return $event;
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    public function test_order_menahan_kursi_dan_total_dihitung_server(): void
    {
        $event = $this->buatEvent();
        $seats = Seat::where('event_id', $event->id)->take(2)->pluck('id')->all();
        Sanctum::actingAs($this->customer());

        $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => $seats])
            ->assertCreated()
            ->assertJsonPath('data.total', 200000)
            ->assertJsonPath('data.status', 'pending');

        $this->assertSame(2, Seat::where('status', 'held')->count());
    }

    public function test_kursi_yang_sama_tidak_bisa_dipesan_dua_kali(): void
    {
        $event = $this->buatEvent();
        $seat = Seat::where('event_id', $event->id)->first();

        Sanctum::actingAs($this->customer());
        $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => [$seat->id]])->assertCreated();

        Sanctum::actingAs($this->customer());
        $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => [$seat->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('seat_ids');

        $this->assertSame(1, Order::count());
    }

    public function test_bayar_mengubah_order_paid_dan_kursi_sold(): void
    {
        $event = $this->buatEvent();
        $seat = Seat::where('event_id', $event->id)->first();
        Sanctum::actingAs($this->customer());

        $orderId = $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => [$seat->id]])
            ->json('data.id');

        $this->postJson("/api/orders/{$orderId}/pay", ['metode' => 'qris'])
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertSame('sold', $seat->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'jumlah' => 100000]);
    }

    public function test_batal_dan_kedaluwarsa_mengembalikan_kursi(): void
    {
        $event = $this->buatEvent();
        $seats = Seat::where('event_id', $event->id)->take(2)->pluck('id');
        Sanctum::actingAs($this->customer());

        $id1 = $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => [$seats[0]]])->json('data.id');
        $this->postJson("/api/orders/{$id1}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->assertSame('available', Seat::find($seats[0])->status);

        $this->postJson('/api/orders', ['event_id' => $event->id, 'seat_ids' => [$seats[1]]])->assertCreated();
        Order::where('status', 'pending')->update(['expires_at' => now()->subMinute()]);

        $this->assertSame(1, app(OrderService::class)->releaseExpired());
        $this->assertSame('available', Seat::find($seats[1])->status);
    }
}
