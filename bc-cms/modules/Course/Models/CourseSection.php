<?php

namespace Modules\Course\Models;

use App\BaseModel;

class CourseSection extends BaseModel
{
    protected $table = 'course_sections';

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'section_id', 'id');
    }

    public function frontendModules()
    {
        return $this->hasMany(CourseModule::class, 'section_id', 'id')->where('active', 1)->orderBy('display_order', 'ASC');
    }

    public function getModulesStudyJsDataAttribute()
    {
        $res = [];
        foreach ($this->frontendModules as $module) {

            $res[] = [
                'title' => $module->title,
                'study_url' => $module->study_url,
                'duration_html' => $module->duration_html
            ];
        }
        return $res;
    }

    public function getDurationTextAttribute()
    {
        return $this->duration ? minute_format($this->duration) : '';
    }
}
