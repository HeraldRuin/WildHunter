<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Booking numbers are generated per hotel (bc_booking_counters.hotel_id).
     * Unique constraint must match that scope: (hotel_id, booking_number).
     */
    public function up(): void
    {
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->dropUnique('bc_bookings_type_booking_number_unique');
            $table->unique(['hotel_id', 'booking_number'], 'bc_bookings_hotel_id_booking_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->dropUnique('bc_bookings_hotel_id_booking_number_unique');
            $table->unique(['type', 'booking_number'], 'bc_bookings_type_booking_number_unique');
        });
    }
};
