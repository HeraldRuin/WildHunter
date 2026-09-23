<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bc_animals')
            ->where('hunt_individual', 1)
            ->where('hunt_group', 1)
            ->update(['hunt_individual' => 0]);
    }

    public function down(): void
    {
    }
};
