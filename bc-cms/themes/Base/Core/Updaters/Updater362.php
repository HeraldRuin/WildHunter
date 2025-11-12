<?php

namespace Themes\Base\Core\Updaters;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingPassenger;

class Updater362
{


    public static function run()
    {
        $version = '1.2';
        if (version_compare(setting_item('update_to_362'), $version, '>=')) return;

        Artisan::call('migrate', [
            '--force' => true,
        ]);

        if(Schema::hasTable('bc_tours'))
        {
            Schema::table('bc_tours',function(Blueprint $blueprint){
                if(!Schema::hasColumn('bc_tours','date_select_type')){
                    $blueprint->string('date_select_type')->nullable();
                }
            });
        }

        $mysqlVersion = DB::select("select version() as version")[0]->version;

        if (!\Illuminate\Support\Facades\Schema::hasColumn('bc_review', 'object_author_id')) {
            if (version_compare($mysqlVersion, '8.0.0', '>=')) {
                DB::statement('ALTER TABLE bc_review RENAME COLUMN vendor_id TO object_author_id');
            } else {
                DB::statement('ALTER TABLE bc_review CHANGE vendor_id object_author_id BIGINT');
            }
        }

        Schema::table(BookingPassenger::getTableName(),function(Blueprint $blueprint){
            if(!Schema::hasColumn(BookingPassenger::getTableName(),'index')){
                $blueprint->integer('index')->nullable();
            }
        });

        // Run Update
        Artisan::call('cache:clear');

        setting_update_item('update_to_362', $version);
    }
}
