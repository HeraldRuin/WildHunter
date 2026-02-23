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
            $table->boolean('is_all_places_assigned')->default(false)->after('prepayment_paid')
                ->comment('Флаг, показывающий, что все места для этой брони распределены');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_bookings', function (Blueprint $table) {
            $table->dropColumn('is_all_places_assigned');
        });
    }
};
