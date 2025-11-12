<?php
namespace Modules\Course\Models;

use App\BaseModel;

class CourseCategoryTranslation extends BaseModel
{
    protected $table = 'course_category_translations';
    protected $fillable = [
        'name',
        'content',
    ];
    protected $cleanFields = [
        'content'
    ];
}
