<?php
namespace Modules\Course\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Course\Models\Course;

class CourseLevel extends BaseModel
{
    use SoftDeletes;
    protected $table = 'course_level';
    protected $fillable = [
        'name',
        'content',
        'slug',
        'status'
    ];
    protected $slugField     = 'slug';
    protected $slugFromField = 'name';

    public static function getModelName()
    {
        return __("Course Level");
    }

    public function courses(){
        return $this->hasMany(Course::class, 'level_id', 'id');
    }
}
