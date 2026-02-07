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
        Schema::create('bc_booking_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('service_type')->nullable();
            $table->unsignedBigInteger('hunter_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->string('animal')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->foreign('hunter_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('booking_id')->references('id')->on('bc_bookings')->onDelete('cascade');
            $table->index('booking_id');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_booking_services');
    }
};
