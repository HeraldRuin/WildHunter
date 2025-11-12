<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Location\Models\LocationCategory;
use Modules\Location\Models\LocationCategoryTranslation;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bc_locations')) {
            Schema::create('bc_locations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255)->nullable();
                $table->text('content')->nullable();
                $table->string('slug', 255)->nullable();
                $table->integer('image_id')->nullable();
                $table->string('map_lat', 20)->nullable();
                $table->string('map_lng', 20)->nullable();
                $table->integer('map_zoom')->nullable();
                $table->string('status', 50)->nullable();
                $table->string('gallery')->nullable();
                $table->nestedSet();

                $table->integer('create_user')->nullable();
                $table->integer('update_user')->nullable();
                $table->softDeletes();

                //Languages
                $table->bigInteger('origin_id')->nullable();
                $table->string('lang', 10)->nullable();

                $table->timestamps();
            });
        }
        if (!Schema::hasTable((new LocationCategory())->getTable())) {
            Schema::create((new LocationCategory())->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255)->nullable();
                $table->string('icon_class', 255)->nullable();
                $table->text('content')->nullable();
                $table->string('slug', 255)->nullable();
                $table->string('status', 50)->nullable();
                $table->nestedSet();

                $table->integer('create_user')->nullable();
                $table->integer('update_user')->nullable();
                $table->softDeletes();

                //Languages
                $table->bigInteger('origin_id')->nullable();
                $table->string('lang', 10)->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable((new LocationCategoryTranslation())->getTable())) {
            Schema::create((new LocationCategoryTranslation())->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('origin_id')->nullable();
                $table->string('locale', 10)->nullable();

                $table->string('name', 255)->nullable();
                $table->text('content')->nullable();

                $table->integer('create_user')->nullable();
                $table->integer('update_user')->nullable();
                $table->unique(['origin_id', 'locale']);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bc_locations');
        Schema::dropIfExists('bc_location_categories');
        Schema::dropIfExists('bc_location_category_translations');
    }
};
