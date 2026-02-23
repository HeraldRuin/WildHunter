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
            $table->unique(['booking_id', 'room_id', 'place_number'], 'unique_booking_room_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_room_places', function (Blueprint $table) {
            $table->dropUnique('unique_booking_room_place');
        });
    }
};
