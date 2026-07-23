<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->decimal('star_rate', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->smallInteger('star_rate')->nullable()->change();
        });
    }
};
