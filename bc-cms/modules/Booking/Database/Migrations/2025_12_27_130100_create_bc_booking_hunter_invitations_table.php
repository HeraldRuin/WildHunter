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
        Schema::create('bc_booking_hunter_invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_hunter_id')->index();
            $table->unsignedBigInteger('hunter_id')->index();
            $table->boolean('invited')->default(true)->comment('Был ли охотник приглашен');
            $table->string('status', 50)->default('invited')->comment('invited, accepted, declined, removed');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('invitation_token', 64)->nullable()->unique()->comment('Токен для ссылки приглашения');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_hunter_id')->references('id')->on('bc_booking_hunters')->onDelete('cascade');
            $table->foreign('hunter_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['booking_hunter_id', 'hunter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_booking_hunter_invitations');
    }
};
