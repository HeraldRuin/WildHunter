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
        Schema::create('bc_caliber_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('origin_id')->nullable()->index();
            $table->string('locale', 10)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
            $table->text('trip_ideas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_caliber_translations');
    }
};
