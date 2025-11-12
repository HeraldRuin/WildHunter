<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('core_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('locale', 10)->nullable();
            $table->text('string')->nullable();
            $table->bigInteger('parent_id')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();

            $table->timestamp('last_build_at')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });

        $this->createTranslationTables();
    }




    public function createTranslationTables()
    {

        Schema::create('core_page_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->text('short_desc')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->unique(['origin_id', 'locale']);

            $table->timestamps();
        });

        Schema::create('core_news_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('core_news_category_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('core_tag_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('core_menu_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->longText('items')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
        });
        Schema::create('core_template_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('origin_id')->unsigned();
            $table->string('locale')->index();

            $table->string('title', 255)->nullable();
            $table->longText('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('bc_location_translations', function (\Illuminate\Database\Schema\Blueprint $table) {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_translations');
        Schema::dropIfExists('core_page_translations');
        Schema::dropIfExists('core_news_translations');
        Schema::dropIfExists('core_news_category_translations');
        Schema::dropIfExists('core_tag_translations');
        Schema::dropIfExists('core_menu_translations');
        Schema::dropIfExists('core_template_translations');
        Schema::dropIfExists('bc_location_translations');
    }
}
