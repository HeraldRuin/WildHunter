<?php
namespace Modules\Course\Models;

use App\BaseModel;

class CourseModuleCompletion extends BaseModel
{
    protected $table = 'course_module_completion';

    protected $fillable = [
        'course_id',
        'module_id',
        'user_id',
    ];
}
