<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_news', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('cat_id')->nullable();
            $table->integer('image_id')->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->string('gallery')->nullable();
            $table->bigInteger('author_id')->nullable();
            $table->softDeletes();

            //Languages
            $table->bigInteger('origin_id')->nullable();
            $table->string('lang', 10)->nullable();

            $table->timestamps();
        });

        Schema::create('core_news_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('news_id')->nullable();
            $table->integer('tag_id')->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('core_news_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('status', 50)->nullable();

            $table->nestedSet();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_news');
        Schema::dropIfExists('core_news_tag');
        Schema::dropIfExists('core_news_category');
    }
}
