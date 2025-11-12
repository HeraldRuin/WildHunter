<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Booking\Models\Service;
use Modules\Booking\Models\ServiceTranslation;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable((new Service())->getTable())) {
            Schema::create((new Service())->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('title', 255)->nullable();
                $table->string('slug', 255)->charset('utf8')->index();
                $table->integer('category_id')->nullable();
                $table->integer('location_id')->nullable();
                $table->string('address', 255)->nullable();
                $table->string('map_lat', 20)->nullable();
                $table->string('map_lng', 20)->nullable();
                $table->tinyInteger('is_featured')->nullable();
                $table->tinyInteger('star_rate')->nullable();
                //Price
                $table->decimal('price', 12, 2)->nullable();
                $table->decimal('sale_price', 12, 2)->nullable();

                //Tour type
                $table->integer('min_people')->nullable();
                $table->integer('max_people')->nullable();
                $table->integer('max_guests')->nullable();
                $table->integer('review_score')->nullable();
                $table->integer('min_day_before_booking')->nullable();
                $table->integer('min_day_stays')->nullable();
                $table->integer('object_id')->nullable();
                $table->string('object_model', 255)->nullable();
                $table->string('status', 50)->nullable();
                
                $table->bigInteger('author_id')->nullable();

                $table->integer('create_user')->nullable();
                $table->integer('update_user')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable((new ServiceTranslation())->getTable())) {
            Schema::create((new ServiceTranslation())->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('origin_id')->nullable();
                $table->string('locale', 10)->nullable();

                $table->string('title', 255)->nullable();
                $table->text('address')->nullable();
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
        Schema::dropIfExists('user_wishlist');
        Schema::dropIfExists('bc_booking_payments');
    }
};
