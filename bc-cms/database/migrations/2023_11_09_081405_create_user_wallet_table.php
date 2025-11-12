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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('type', 30)->nullable();
            $table->decimal('amount', 10)->nullable();
            $table->text('meta')->nullable();
            $table->string('status', 30)->nullable();
            $table->softDeletes();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->index('user_id');

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
        Schema::dropIfExists('credit_transactions');
    }
};
