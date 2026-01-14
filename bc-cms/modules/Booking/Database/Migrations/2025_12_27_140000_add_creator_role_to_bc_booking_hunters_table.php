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
        Schema::table('bc_booking_hunters', function (Blueprint $table) {
            $table->string('creator_role', 50)->nullable()->after('invited_by')->comment('Роль создателя брони (hunter, baseadmin и т.д.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_hunters', function (Blueprint $table) {
            $table->dropColumn('creator_role');
        });
    }
};
