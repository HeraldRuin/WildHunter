<?php
namespace Modules\Blog\Models;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends BaseModel
{
    use SoftDeletes;

    protected $table = 'core_blogs';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'image_id',
        'excerpt',
        'content_json',
        'author_id',
    ];

    protected $casts = [
        'content_json' => 'array',
    ];

    protected $slugField = 'slug';
    protected $slugFromField = 'title';

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getEditUrl(bool $vendor = true): string
    {
        if ($vendor) {
            return route('blog.vendor.edit', ['id' => $this->id]);
        }

        return route('blog.admin.edit', ['id' => $this->id]);
    }

    public function getCoverUrl(): ?string
    {
        if (empty($this->image_id)) {
            return null;
        }

        return get_file_url($this->image_id, 'medium');
    }

    public static function getModelName(): string
    {
        return __('Blog');
    }
}
