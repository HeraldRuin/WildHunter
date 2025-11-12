<?php

namespace App\Traits;

use Modules\Review\Models\Review;
use Illuminate\Support\Facades\Cache;

trait HasUserReview
{
    public function cacheKey($prefix='review')
    {
        $type = $this->type ?? 'review';
        return strtolower($prefix . '-' . $type . '-' . $this->id);
    }

    public function reviewsAboutMe()
    {
        return $this->hasMany(Review::class, 'object_author_id')->where('status', 'approved');
    }

    public function getReviewsCountAttribute()
    {
        return Cache::rememberForever($this->cacheKey('reviews_count') , function () {
            return $this->reviewsAboutMe()->count();
        });
    }
    
}
