<?php
namespace Modules\Course\Models;

use App\BaseModel;

class CourseTerm extends BaseModel
{
    protected $table = 'course_term';
    protected $fillable = [
        'term_id',
        'target_id'
    ];
}