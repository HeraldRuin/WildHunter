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
        Schema::create('bc_hotel_animals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('animal_id');

            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('bc_hotels')->onDelete('cascade');
            $table->foreign('animal_id')->references('id')->on('bc_animals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_hotel_animals');
    }
};
