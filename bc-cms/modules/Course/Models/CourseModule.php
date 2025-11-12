<?php

namespace Modules\Course\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseModule extends BaseModel
{
    use SoftDeletes;
    protected $table = 'course_modules';

    protected $fillable = [
        'title',
        'file_id',
        'active',
        'preview_url',
        'url',
        'section_id',
        'course_id',
        'duration'
    ];

    public function getHasPreviewAttribute()
    {
        return $this->preview_url ? true : false;
    }

    public function getPreviewUrlEmbedAttribute()
    {
        return $this->preview_url ? handleVideoUrl($this->preview_url) : '';
    }
    public function getDurationTextAttribute()
    {
        return $this->duration ? minute_format($this->duration) : '';
    }
    public function getStudyUrlAttribute()
    {
        $url = $this->file_id ? get_file_url($this->file_id) : $this->url;
        switch ($this->type) {
            case "presentation":
                if ($this->file_id) {
                    if (strpos($url, '.ppt')) {
                        $url = asset('libs/ViewerJS/#' . ($url));
                    } else {
                        $url = asset('libs/pdfjs/web/viewer.html?file=' . urlencode($url));
                    }
                }
                break;
            case "scorm":
                if ($this->file_id) {
                    $url = route('course.scorm_player', ['id' => $this->file_id]);
                }
                break;
        }
        if (!$this->file_id) {
            $url = handleVideoUrl($url);
        }

        return $url;
    }
}
