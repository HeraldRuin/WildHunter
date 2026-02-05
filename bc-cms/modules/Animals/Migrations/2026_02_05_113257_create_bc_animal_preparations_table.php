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
        Schema::create('bc_animal_preparations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animal_id');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type', 255)->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('bc_animals')->onDelete('cascade');
            $table->index('animal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_animal_preparations');
    }
};
