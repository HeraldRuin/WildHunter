<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_animals', function (Blueprint $table) {
            $table->boolean('hunt_individual')->default(false);
            $table->boolean('hunt_group')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bc_animals', function (Blueprint $table) {
            $table->dropColumn(['hunt_individual', 'hunt_group']);
        });
    }
};
