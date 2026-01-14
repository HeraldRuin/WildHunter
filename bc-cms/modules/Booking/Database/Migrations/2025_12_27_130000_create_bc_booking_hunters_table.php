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
        Schema::create('bc_booking_hunters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('hunter_id')->index();
            $table->boolean('is_master')->default(false)->comment('Является ли охотник мастер-охотником на этой брони');
            $table->boolean('invited')->default(false)->comment('Был ли охотник приглашен');
            $table->string('status', 50)->default('accepted')->comment('invited, accepted, declined, removed');
            $table->unsignedBigInteger('invited_by')->nullable()->comment('ID пользователя, который пригласил (обычно baseadmin)');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('invitation_token', 64)->nullable()->unique()->comment('Токен для ссылки приглашения');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bc_bookings')->onDelete('cascade');
            $table->foreign('hunter_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['booking_id', 'hunter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_booking_hunters');
    }
};
