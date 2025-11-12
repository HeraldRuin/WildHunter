<?php   
namespace Modules\Visa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Visa\Models\VisaType;
use Modules\Visa\Models\VisaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisaSeeder extends Seeder
{
    public function run()
    {
        $typesIds = $this->seedTypes();

        $this->seedForms($typesIds);

        // Seed settings
        $this->seedSettings();
    }

    public function seedForms($typesIds){

        // Images
        $fileArray = [
            ['file_name' => 'visa-01', 'file_path' => 'demo/visa/01.jpg', 'file_type' => 'image/jpeg', 'file_extension' => 'jpg'],
        ];
        foreach($fileArray as $file){
            $mediaIds[] = DB::table('media_files')->insertGetId($file);
        }
        
        $services = [
                [
                    'type_id'=>$typesIds[0],
                    'to_country'=>'US',
                    'code'=>'B-2',
                    'title'=>'B-2',
                    'price'=>100,
                ],
                [
                    'type_id'=>$typesIds[1],
                    'to_country'=>'US',
                    'code'=>'F-1',
                    'title'=>'F-1',
                    'price'=>300,
                ],
                [
                    'type_id'=>$typesIds[0],
                    'to_country'=>'GB',
                    'code'=>'GB-STV',
                    'title'=>'STV',
                    'price'=>100,
                ],
                [
                    'type_id'=>$typesIds[1],
                    'to_country'=>'GB',
                    'code'=>'GB-STU',
                    'title'=>'STU',
                    'price'=>300,
                ],
                [
                    'type_id'=>$typesIds[0],
                    'to_country'=>'AU',
                    'code'=>'AU-VIS600',
                    'title'=>'AU-VIS600',
                    'price'=>300,
                ],
                [
                    'type_id'=>$typesIds[1],
                    'to_country'=>'AU',
                    'code'=>'AU-STU500',
                    'title'=>'AU-STU500',
                    'price'=>300,
                ],
        ];

        foreach ($services as $service) {
            $form = new VisaService();
            $form->fillByAttr(['type_id', 'to_country', 'code', 'title', 'price'], $service);
            $form->status = 'publish';
            $form->processing_days = rand(1, 10);
            $form->max_stay_days = rand(1, 10);
            $form->multiple_entry = rand(0, 5);
            $form->original_price = $service['price'] + rand(20, 100);
            $form->image_id = $mediaIds[rand(0, count($mediaIds) - 1)];
            $form->slug = Str::slug($service['title']);
            $form->save();
        }
    }


    public function seedTypes(){
        $types = [
            [
                'name' => 'Tourist',
            ],
            [
                'name' => 'Student',
            ],
        ];

        $ids = [];
        foreach ($types as $type) {
            $visaType = new VisaType();
            $visaType->fillByAttr(['name'], $type);
            $visaType->status = 'publish';
            $visaType->save();
            $ids[] = $visaType->id;
        }

        return $ids;
    }

    public function seedSettings(){
        $settings = [
            [
                'name' => 'visa_review_approved',
                'val' => '0',
            ],
            [
                'name' => 'visa_review_stats',
                'val' => '[{"title":"Service"},{"title":"Organization"},{"title":"Friendliness"},{"title":"Area Expert"},{"title":"Safety"}]',
            ],
            [
                'name'=>'visa_search_fields',
                'val'=>'[{"title":"Country","field":"to_country","size":"4","position":"1"},{"title":"Visa type","field":"visa_type","size":"4","position":"2"},{"title":"Applications","field":"guests","size":"4","position":"3"}]',
            ]
        ];

        foreach ($settings as $setting) {
            setting_update_item($setting['name'], $setting['val']);
        }
    }
}