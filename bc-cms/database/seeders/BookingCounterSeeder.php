<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;

class BookingCounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            Booking::BookingTypeHotelAnimal,
            Booking::BookingTypeHotel,
            Booking::BookingTypeAnimal,
        ];

        foreach ($types as $type) {
            DB::table('bc_booking_counters')->updateOrInsert(
                ['type' => $type],
                ['last_number' => 0]
            );
        }
    }
}
