<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->text('content')->nullable();
            $table->text('short_desc')->nullable();
            $table->bigInteger('cat_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->tinyInteger('is_paid')->nullable()->default(0);
            $table->bigInteger('payment_id')->nullable();
            $table->tinyInteger('is_featured')->nullable()->default(0);
            $table->tinyInteger('is_full_time')->nullable()->default(0);
            $table->string('language')->nullable();
            $table->decimal('duration', 5, 1)->nullable();
            $table->bigInteger('image_id')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('original_price', 15, 2)->nullable();
            $table->integer('views')->nullable()->default(0);

            $table->decimal('review_score')->nullable();
            $table->string('preview_url')->nullable();

            $table->bigInteger('author_id')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();

            $table->index(['status', 'cat_id', 'level_id']);

            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('course_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->text('short_desc')->nullable();
            $table->string('language')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->bigInteger('origin_id')->nullable();
            $table->string('locale', 15)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('icon')->nullable();
            $table->bigInteger('image_id')->nullable();
            $table->nestedSet();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_category_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->bigInteger('origin_id')->nullable();
            $table->string('locale', 15)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_term', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('term_id')->nullable();
            $table->integer('target_id')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('section_id')->nullable();
            $table->string('title')->nullable();
            $table->bigInteger('file_id')->nullable();
            $table->string('type', 30)->nullable();
            $table->tinyInteger('active')->default(1)->nullable();
            $table->tinyInteger('display_order')->default(0)->nullable();
            $table->string('preview_url')->nullable();
            $table->decimal('duration', 5, 2)->nullable(); // Minutes

            $table->text('url')->nullable();

            $table->softDeletes();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->index(['course_id', 'section_id']);
            $table->timestamps();
        });
        Schema::create('course_sections', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->tinyInteger('active')->default(1)->nullable();
            $table->tinyInteger('display_order')->default(0)->nullable();
            $table->decimal('duration', 5, 2)->nullable(); // Minutes

            $table->softDeletes();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
        });
        Schema::create('course_user', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->decimal('percent', 5, 2)->nullable();

            $table->softDeletes();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->unique(['course_id', 'user_id']);
            $table->timestamps();
        });

        Schema::create('course_announcement', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable()->index();
            $table->text('content')->nullable();

            $table->softDeletes();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('course_module_completion', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('module_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->tinyInteger('state')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->tinyInteger('time_spent')->nullable()->comment('Seconds');
            $table->decimal('score', 7, 2)->nullable();
            $table->timestamp('last_studied_at')->nullable();
            $table->text('meta')->nullable();

            $table->unique(['course_id', 'user_id', 'module_id']);

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('course_study_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('module_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->tinyInteger('state')->nullable();
            $table->decimal('percent', 5, 2)->nullable();

            $table->index(['course_id', 'module_id', 'user_id']);
            $table->index(['course_id', 'user_id']);

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
        });

        Schema::create('course_level', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('status', 50)->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_level_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->bigInteger('origin_id')->nullable();
            $table->string('locale', 15)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        // add student_count to users table for fast query
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'student_count')) {
                $table->integer('student_count')->nullable()->default(0);
            }
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_translations');
        Schema::dropIfExists('course_category');
        Schema::dropIfExists('course_category_translations');
        Schema::dropIfExists('course_term');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('course_sections');
        Schema::dropIfExists('course_user');
        Schema::dropIfExists('course_announcement');
        Schema::dropIfExists('course_module_completion');
        Schema::dropIfExists('course_user_completion');
        Schema::dropIfExists('course_study_log');
        Schema::dropIfExists('course_level');
        Schema::dropIfExists('course_level_translations');
    }
}
