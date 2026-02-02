<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bc_booked_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bc_hotel_room_bookings')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('bc_hotel_rooms')->cascadeOnDelete();
            $table->date('date');
            $table->integer('number');

            $table->index(['room_id', 'date'], 'idx_room_date');
            $table->index('booking_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_booked_days');
    }
};
