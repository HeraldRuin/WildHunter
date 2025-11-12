<?php

namespace Modules\Course\Models;

use App\BaseModel;

class CourseTranslation extends Course
{
    protected $table = 'course_translations';

    protected $fillable = [
        'title',
        'content',
        'short_desc',
        'language'
    ];

    protected $slugField     = false;
    protected $seo_type = 'course_translation';

    protected $cleanFields = [
        'content'
    ];
    protected $casts = [
        'faqs'  => 'array',
    ];

    public function getSeoType(){
        return $this->seo_type;
    }
}
