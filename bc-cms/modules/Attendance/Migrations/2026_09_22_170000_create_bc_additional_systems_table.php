<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bc_additional_systems')) {
            return;
        }

        Schema::create('bc_additional_systems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        // Таблица общая с API, откат миграции её не удаляет.
    }
};
