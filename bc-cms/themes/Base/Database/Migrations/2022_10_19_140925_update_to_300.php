<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $tableAddAuthorId = [
            'bc_hotels',
            'bc_tours',
            'bc_events',
            'bc_spaces',
            'bc_cars',
            'bc_boats',
            'bc_flight',
            'bc_airline',
            'bc_airport',
            'bc_flight_seat',
            'bc_seat_type',
        ];
        foreach ($tableAddAuthorId as $tbName){
            Schema::table($tbName,function(Blueprint $blueprint) use ($tbName){
                if(!Schema::hasColumn($tbName,'author_id')){
                    $blueprint->bigInteger('author_id')->nullable();
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
};
