<?php

namespace  Modules\Visa;

use Modules\Core\Abstracts\BaseSettingsClass;
use Modules\Core\Models\Settings;

class SettingClass extends BaseSettingsClass
{
    public static function getSettingPages()
    {
        $configs = [
            'visa'=>[
                'id'   => 'visa',
                'title' => __("Visa Settings"),
                'position'=>20,
                'view'=>"Visa::admin.settings.visa",
                "keys"=>[
                    'visa_disable',
                    'visa_page_search_title',
                    'visa_page_search_banner',
                    'visa_page_limit_item',

                    'visa_enable_review',
                    'visa_review_approved',
                    'visa_enable_review_after_booking',
                    'visa_review_number_per_page',
                    'visa_review_stats',

                    'visa_page_list_seo_title',
                    'visa_page_list_seo_desc',
                    'visa_page_list_seo_image',
                    'visa_page_list_seo_share',

                    'visa_search_fields',
                    'visa_map_search_fields',

                    'visa_allow_review_after_making_completed_booking',
                    'visa_deposit_enable',
                    'visa_deposit_type',
                    'visa_deposit_amount',
                    'visa_deposit_fomular',

                    // NOTE: Visa does not allow vendor to create service
                ],
                'html_keys'=>[

                ],
                'filter_demo_mode'=>[
                ]
            ]
        ];
        return apply_filters(Hook::VISA_SETTING_CONFIG,$configs);
    }
}
