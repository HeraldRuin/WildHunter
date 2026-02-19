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
        Schema::create('bc_booking_room_places', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedSmallInteger('place_number')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('is_reserved')->default(true);
            $table->timestamps();

            // внешние ключи (если есть таблицы bookings и rooms)
            $table->foreign('booking_id')->references('id')->on('bc_bookings')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('bc_hotel_rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_booking_room_places');
    }
};
