<?php
namespace Modules\Course\Models;

use App\BaseModel;

class CourseLevelTranslation extends BaseModel
{
    protected $table = 'course_level_translations';
    protected $fillable = [
        'name',
        'content',
    ];
    protected $cleanFields = [
        'content'
    ];
}
