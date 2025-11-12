<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_size', 255)->nullable();
            $table->string('file_type', 255)->nullable();
            $table->string('file_extension', 255)->nullable();
            $table->string('driver', 255)->nullable();
            $table->tinyInteger('is_private')->nullable()->default(0);
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();
            $table->integer('app_id')->nullable();
            $table->integer('app_user_id')->nullable();
            $table->integer('file_width')->nullable();
            $table->integer('file_height')->nullable();
            $table->bigInteger('folder_id')->nullable()->default(0);
            $table->tinyInteger('file_edit')->default(0)->nullable();

            $table->bigInteger('author_id')->nullable();

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
        Schema::dropIfExists('media_files');
    }
}
