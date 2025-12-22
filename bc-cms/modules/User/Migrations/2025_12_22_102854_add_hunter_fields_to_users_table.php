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
        Schema::table('users', function (Blueprint $table) {
            $table->string('hunter_billet_number')->nullable();
            $table->string('hunter_license_number')->nullable();
            $table->date('hunter_license_date')->nullable();
            $table->unsignedBigInteger('weapon_type_id')->nullable();
            $table->string('caliber')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hunter_billet_number',
                'hunter_license_number',
                'hunter_license_date',
                'weapon_type_id',
                'caliber'
            ]);
        });
    }
};
