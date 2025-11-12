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
        Schema::create('bc_visa_types', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('content')->nullable();
            $table->string('status')->default('draft');

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_visa_services', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->string('content')->nullable();
            $table->string('status')->default('draft');
            $table->string('slug')->nullable()->unique();

            $table->bigInteger('type_id')->nullable();
            $table->string('from_country')->nullable();
            $table->string('to_country')->nullable();
            $table->bigInteger('form_id')->nullable(); // Form ID in Form module
            $table->bigInteger('image_id')->nullable();

            $table->decimal('price', 12, 2)->nullable(); // Service fee
            $table->decimal('original_price', 12, 2)->nullable(); // Original price
            $table->integer('processing_days')->nullable(); // Processing time
            $table->integer('max_stay_days')->nullable(); // Validity period
            $table->integer('multiple_entry')->nullable()->default(1); // Multiple entry
            $table->decimal('review_score')->nullable();

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->index(['status', 'to_country']);

            $table->unique(['to_country', 'code']);

            $table->bigInteger('author_id')->nullable();
            $table->index(['status','price']);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_visa_applications', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->string('country')->nullable();
            $table->string('status')->default('draft');
            
            // Booking ID
            $table->bigInteger('booking_id')->nullable();

            $table->text('form')->nullable(); // In JSON
            

            $table->bigInteger('author_id')->nullable(); // Customer ID

            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_visa_type_translations', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('origin_id')->nullable();
            $table->string('locale', 10)->nullable();

            $table->string('name', 255)->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();

            $table->unique(['origin_id', 'locale']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bc_visa_service_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('origin_id')->nullable();
            $table->string('locale', 10)->nullable();

            $table->string('title')->nullable();
            $table->text('content')->nullable();

            $table->integer('create_user')->nullable();
            $table->integer('update_user')->nullable();

            $table->unique(['origin_id', 'locale']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bc_visa_types');
        Schema::dropIfExists('bc_visa_services');
        Schema::dropIfExists('bc_visa_applications');
        Schema::dropIfExists('bc_visa_type_translations');
        Schema::dropIfExists('bc_visa_service_translations');
    }
};
