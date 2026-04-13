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
        Schema::table('bc_booking_payments', function (Blueprint $table) {
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('next_check_at')->nullable();
            $table->dateTime('last_checked_at')->nullable();

            $table->index('next_check_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_payments', function (Blueprint $table) {
            $table->dropIndex(['next_check_at']);

            $table->dropColumn([
                'attempts',
                'next_check_at',
                'last_checked_at',
            ]);
        });
    }
};
