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
        Schema::table('bc_booking_room_places', function (Blueprint $table) {
            $table->unique(
                ['booking_id', 'room_id', 'room_index', 'place_number'],
                'booking_room_index_place_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_room_places', function (Blueprint $table) {
            $table->dropUnique('booking_room_index_place_unique');
        });
    }
};
