<?php

namespace Modules\Teacher\Models;

use App\User;
use Modules\Course\Models\Course;

class Teacher extends User
{
    protected $table = 'users';

    public function courses()
    {
        return $this->hasMany(Course::class, 'author_id', 'id');
    }
}
