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
        Schema::create('bc_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('content')->nullable();
            $table->string('status')->default('draft');

            $table->bigInteger('author_id')->nullable();

            $table->text('metadata')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_form_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('form_id')->nullable();
            $table->string('label')->nullable();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('status')->default('draft');
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->string('rules')->nullable();
            $table->string('order')->nullable();

            $table->bigInteger('parent_id')->nullable();

            $table->text('metadata')->nullable();

            $table->unique(['form_id', 'name']);

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('bc_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('form_id')->nullable()->index();

            $table->string('status')->default('draft');
            $table->text('metadata')->nullable();
            $table->string('locale')->nullable();

            $table->bigInteger('author_id')->nullable()->index(); // User who submitted the form

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_form_submission_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('submission_id')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->text('value')->nullable();

            $table->text('metadata')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_forms');
        Schema::dropIfExists('bc_form_fields');
        Schema::dropIfExists('bc_form_submissions');
        Schema::dropIfExists('bc_form_submission_fields');
    }
};
