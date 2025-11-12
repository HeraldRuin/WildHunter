<?php

namespace Modules\Course\Models;

use App\BaseModel;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCategory extends BaseModel
{
    use SoftDeletes;
    use NodeTrait;
    protected $table = 'course_category';
    protected $fillable = [
        'name',
        'content',
        'slug',
        'status',
        'parent_id',
        'image_id'
    ];
    protected $slugField     = 'slug';
    protected $slugFromField = 'name';

    public static function getModelName()
    {
        return __("Course Category");
    }

    public static function searchForMenu($q = false)
    {
        $query = static::select('id', 'name');
        if (strlen($q)) {
            $query->where('name', 'like', "%" . $q . "%");
        }
        $a = $query->limit(10)->get();
        return $a;
    }

    public function getDetailUrl()
    {
        return route('course.category', ['slug' => $this->slug]);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'cat_id');
    }
}
