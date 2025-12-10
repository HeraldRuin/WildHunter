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
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->integer('hotel_id')->after('gateway');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->dropColumn('hotel_id');
        });
    }
};
