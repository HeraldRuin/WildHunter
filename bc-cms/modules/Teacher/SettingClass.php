<?php

namespace  Modules\Teacher;

use Modules\Core\Abstracts\BaseSettingsClass;
use Modules\Core\Models\Settings;

class SettingClass extends BaseSettingsClass
{
    public static function getSettingPages()
    {
        $configs = [
            'teacher' => [
                'id' => 'teacher',
                'title' => __("Teacher Settings"),
                'position' => 20,
                'view' => "Teacher::admin.settings.teacher",
                "keys" => [
                    'teacher_role_id',
                ],
                'html_keys' => [

                ],
                'filter_demo_mode' => [
                ]
            ]
        ];
        return apply_filters(Hook::TEACHER_SETTING_CONFIG,$configs);
    }
}
