<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('nama');                       // VIP, Reguler, dst
            $table->unsignedInteger('harga');
            $table->timestamps();

            $table->unique(['event_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
