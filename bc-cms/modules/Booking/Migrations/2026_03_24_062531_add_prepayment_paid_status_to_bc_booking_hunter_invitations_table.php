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
        Schema::table('bc_booking_hunter_invitations', function (Blueprint $table) {
            $table->string('prepayment_paid_status')->nullable()->after('prepayment_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_hunter_invitations', function (Blueprint $table) {
            $table->dropColumn('prepayment_paid_status');
        });
    }
};
