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
        Schema::create('bc_animal_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('target_id')->index();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->tinyInteger('active')->default(0);
            $table->text('note_to_customer')->nullable();
            $table->text('note_to_admin')->nullable();
            $table->tinyInteger('is_instant')->default(0);
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Индексы
            $table->unique(['target_id','start_date'], 'bc_animal_dates_target_id_start_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_animal_dates');
    }
};
