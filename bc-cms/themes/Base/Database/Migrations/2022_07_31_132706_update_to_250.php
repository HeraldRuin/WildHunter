<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTo250 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Booking Passengers
        if(\Illuminate\Support\Facades\Schema::hasTable('booking_passengers')){
            Schema::rename('booking_passengers','bc_booking_passengers');
        }

        if(Schema::hasTable('bc_booking_passengers')){
            Schema::table('bc_booking_passengers',function(Blueprint $blueprint){
                if(!Schema::hasColumn('bc_booking_passengers','object_model')){
                    $blueprint->string('object_model',30);
                    $blueprint->bigInteger('object_id')->nullable();
                    $blueprint->index('booking_id');
                    $blueprint->index(['object_model','object_id']);
                }
            });
        }
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
