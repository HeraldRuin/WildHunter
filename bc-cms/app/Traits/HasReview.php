<?php

namespace App\Traits;

use Modules\Review\Models\Review;
use Illuminate\Support\Facades\Cache;

trait HasReview
{
    public function cacheKey($prefix='review')
    {
        $type = $this->type ?? 'review';
        return strtolower($prefix . '-' . $type . '-' . $this->id);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'object_id')->where('object_model', $this->type)->where('status', 'approved');
    }

    public function getReviewsCountAttribute()
    {
        return Cache::rememberForever($this->cacheKey('reviews_count') , function () {
            return $this->reviews()->count();
        });
    }
    public function getAvgRatingAttribute()
    {
        return Cache::rememberForever($this->cacheKey('avg_rating') , function () {
            return $this->reviews()->avg('rate_number');
        });
    }
}
