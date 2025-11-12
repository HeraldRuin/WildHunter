<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFrom120To130 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('bc_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_bookings', 'buyer_fees')) {
                $table->text('buyer_fees')->nullable();
                $table->decimal('total_before_fees',10,2)->nullable();
            }
            if (!Schema::hasColumn('bc_bookings', 'paid_vendor')) {
                $table->tinyInteger('paid_vendor')->nullable();
            }
        });

        Schema::table('bc_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_locations', 'banner_image_id')) {
                $table->integer('banner_image_id')->nullable();
                $table->text('trip_ideas')->nullable();
            }
        });
        Schema::table('bc_location_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_location_translations', 'trip_ideas')) {
                $table->text('trip_ideas')->nullable();
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
