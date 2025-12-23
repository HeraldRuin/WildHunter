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
        Schema::create('bc_weapons', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('content')->nullable();
            $table->integer('image_id')->nullable();
            $table->integer('banner_image_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('map_lat', 20)->nullable();
            $table->string('map_lng', 20)->nullable();
            $table->integer('map_zoom')->nullable();
            $table->tinyInteger('is_featured')->nullable();
            $table->string('gallery', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->text('faqs')->nullable();
            $table->tinyInteger('number')->nullable();
            $table->string('status', 50)->nullable();
            $table->tinyInteger('default_state')->default(1);
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->decimal('review_score', 4, 2)->nullable();
            $table->bigInteger('author_id')->nullable();
            $table->string('ical_import_url', 191)->nullable();

            // Индексы
            $table->unique('slug');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_weapons');
    }
};
