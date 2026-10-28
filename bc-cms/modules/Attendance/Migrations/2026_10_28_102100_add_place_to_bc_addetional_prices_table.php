<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bc_addetional_prices') || Schema::hasColumn('bc_addetional_prices', 'place')) {
            return;
        }

        Schema::table('bc_addetional_prices', function (Blueprint $table) {
            $table->string('place', 16)->default('base')->after('type');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bc_addetional_prices') || !Schema::hasColumn('bc_addetional_prices', 'place')) {
            return;
        }

        Schema::table('bc_addetional_prices', function (Blueprint $table) {
            $table->dropColumn('place');
        });
    }
};
