<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_blogs', function (Blueprint $table) {
            if (!Schema::hasColumn('core_blogs', 'author_id')) {
                $table->unsignedBigInteger('author_id')->nullable()->after('content_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('core_blogs', function (Blueprint $table) {
            if (Schema::hasColumn('core_blogs', 'author_id')) {
                $table->dropColumn('author_id');
            }
        });
    }
};
