<?php
namespace Modules\Course\Models;

use App\BaseModel;

class Course2User extends BaseModel
{
    protected $table = 'course_user';
    protected $fillable = [
        'user_id',
        'course_id'
    ];
}