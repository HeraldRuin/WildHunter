<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bc_additional_systems') || Schema::hasColumn('bc_additional_systems', 'type')) {
            return;
        }

        Schema::table('bc_additional_systems', function (Blueprint $table) {
            $table->string('type', 16)->default('base')->after('name');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bc_additional_systems') || !Schema::hasColumn('bc_additional_systems', 'type')) {
            return;
        }

        Schema::table('bc_additional_systems', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
