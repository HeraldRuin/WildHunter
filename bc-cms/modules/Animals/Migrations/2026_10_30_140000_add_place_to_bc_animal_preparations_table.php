<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bc_animal_preparations') || Schema::hasColumn('bc_animal_preparations', 'place')) {
            return;
        }

        Schema::table('bc_animal_preparations', function (Blueprint $table) {
            $table->string('place', 16)->default('base')->after('type');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bc_animal_preparations') || !Schema::hasColumn('bc_animal_preparations', 'place')) {
            return;
        }

        Schema::table('bc_animal_preparations', function (Blueprint $table) {
            $table->dropColumn('place');
        });
    }
};
