<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('business_name', 255)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('address', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('birthday')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->integer('zip_code')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->bigInteger('avatar_id')->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 20)->nullable();
            $table->decimal('review_score', 4, 2)->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->integer('vendor_commission_amount')->nullable();
            $table->string('vendor_commission_type', 30)->nullable();
            $table->tinyInteger('need_update_pw')->nullable()->default(0);
            $table->bigInteger('role_id')->nullable();
            $table->string('verify_submit_status', 30)->nullable();
            $table->smallInteger('is_verified')->nullable()->default(0);
            $table->decimal('credit_balance', 15, 2)->nullable();

            $table->string('user_name')->nullable()->unique();

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_wishlist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('object_id')->nullable();
            $table->string('object_model', 255)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();

            $table->unique(['user_id', 'object_id', 'object_model']);

            $table->timestamps();
        });

        Schema::create('user_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('val')->nullable();
            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_wishlist');
        Schema::dropIfExists('user_meta');
        Schema::dropIfExists('password_reset_tokens');
    }
};
