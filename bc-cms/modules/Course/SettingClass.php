<?php

namespace  Modules\Course;

use Modules\Core\Abstracts\BaseSettingsClass;
use Modules\Core\Models\Settings;
use Modules\Course\Hook;


class SettingClass extends BaseSettingsClass
{
    public static function getSettingPages()
    {
        $settings = [
            'course'=>[
                'id'   => 'course',
                'title' => __("Course Settings"),
                'position'=>20,
                'view'=>"Course::admin.settings.course",
                "keys"=>[
                    'course_disable',
                    'course_layout_search',
                    'course_page_search_title',
                    'course_page_search_sub_title',

                    'course_search_fields',

                    'course_enable_review',
                    'course_review_approved',
                    'course_enable_review_after_booking',
                    'course_review_number_per_page',

                    'course_page_list_seo_title',
                    'course_page_list_seo_desc',
                    'course_page_list_seo_image',
                    'course_page_list_seo_share',

                    'course_booking_buyer_fees',
                    'course_teacher_create_service_must_approved_by_admin',
                ],
                'html_keys'=>[

                ]
            ]
        ];

        return apply_filters(Hook::COURSE_SETTING_CONFIG, $settings);
    }
}
