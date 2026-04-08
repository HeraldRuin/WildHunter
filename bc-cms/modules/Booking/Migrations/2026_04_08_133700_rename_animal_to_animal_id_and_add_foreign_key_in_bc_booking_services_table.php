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
        Schema::table('bc_booking_services', function (Blueprint $table) {
            $table->renameColumn('animal', 'animal_id');
        });

        Schema::table('bc_booking_services', function (Blueprint $table) {
            $table->unsignedBigInteger('animal_id')->nullable()->change();

            $table->foreign('animal_id')
                ->references('id')
                ->on('bc_animals')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bc_booking_services', function (Blueprint $table) {
            $table->dropForeign(['animal_id']);
        });
        Schema::table('bc_booking_services', function (Blueprint $table) {
            $table->renameColumn('animal_id', 'animal');
        });
    }
};
