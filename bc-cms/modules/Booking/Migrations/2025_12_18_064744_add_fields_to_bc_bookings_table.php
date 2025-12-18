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
            $table->unsignedBigInteger('animal_id')->nullable()->after('hotel_id');
            $table->string('type', 50)->nullable()->after('object_model');
            $table->integer('total_hunting')->nullable()->after('total_guests');
            $table->decimal('amount_hunting', 10, 2)->nullable()->after('total');
            $table->dateTime('start_date_animal')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'animal_id',
                'type',
                'total_hunting',
                'amount_hunting'
            ]);
        });
    }
};
