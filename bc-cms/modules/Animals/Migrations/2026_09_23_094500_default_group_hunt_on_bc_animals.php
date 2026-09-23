<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bc_animals')
            ->where('hunt_individual', 0)
            ->where('hunt_group', 0)
            ->update(['hunt_group' => 1]);

        Schema::table('bc_animals', function (Blueprint $table) {
            $table->boolean('hunt_group')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bc_animals', function (Blueprint $table) {
            $table->boolean('hunt_group')->default(false)->change();
        });
    }
};
