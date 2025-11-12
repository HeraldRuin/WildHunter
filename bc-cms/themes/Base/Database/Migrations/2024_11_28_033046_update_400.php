<?php

use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Schema;
use Modules\Booking\Models\BookingPassenger;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mysqlVersion = DB::select("select version() as version")[0]->version;

        if (!\Illuminate\Support\Facades\Schema::hasColumn('bc_review', 'object_author_id')) {
            if (version_compare($mysqlVersion, '8.0.0', '>=')) {
                DB::statement('ALTER TABLE bc_review RENAME COLUMN vendor_id TO object_author_id');
            } else {
                DB::statement('ALTER TABLE bc_review CHANGE vendor_id object_author_id BIGINT');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
