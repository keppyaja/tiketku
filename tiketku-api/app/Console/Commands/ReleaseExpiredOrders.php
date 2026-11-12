<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ReleaseExpiredOrders extends Command
{
    protected $signature = 'tiket:release-expired';

    protected $description = 'Batalkan order pending yang lewat batas waktu dan bebaskan kursinya';

    public function handle(OrderService $service): int
    {
        $jumlah = $service->releaseExpired();
        $this->info("{$jumlah} order kedaluwarsa dibebaskan.");

        return self::SUCCESS;
    }
}
