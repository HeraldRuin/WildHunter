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
        Schema::create('bc_hotel_animal_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')
                ->constrained('bc_hotels')
                ->cascadeOnDelete();

            $table->morphs('priceable');

            $table->decimal('price', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['hotel_id', 'priceable_id', 'priceable_type'], 'hotel_price_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_hotel_animal_prices');
    }
};
