<?php

namespace Modules\Course\Models;

use App\BaseModel;
use App\Currency;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Modules\Core\Models\Attributes;
use Modules\Core\Models\SEO;
use Modules\Core\Models\Terms;
use Modules\Media\Helpers\FileHelper;
use Modules\Review\Models\Review;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Teacher\Models\Teacher;
use Modules\User\Models\UserWishList;
use App\Traits\HasReview;
use Modules\Order\Traits\HasBuyable;
use Modules\Course\Traits\HasStudent;

class Course extends BaseModel
{
    use SoftDeletes;
    use HasReview;
    use HasBuyable;
    use HasStudent;

    protected $table = 'courses';
    public $type = 'course';

    protected $fillable = [
        'title',
        'content',
        'status',
        'short_desc',
        'price',
        'image_id',
        'cat_id',
        'duration',
        'language',
        'preview_url'
    ];
    protected $slugField     = 'slug';
    protected $slugFromField = 'title';
    protected $seo_type = 'course';

    protected $casts = [
        'faqs'  => 'array',
        'price' => 'float',
        'original_price' => 'float',
    ];

    /**
     * @var Review
     */
    protected $reviewClass;

    protected $userWishListClass;

    protected $attributeClass;
    protected $termCourseClass;
    protected $termClass;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->reviewClass = Review::class;
        $this->userWishListClass = UserWishList::class;
        $this->termCourseClass = CourseTerm::class;
        $this->attributeClass = Attributes::class;
        $this->termClass = Terms::class;
    }

    public static function getModelName()
    {
        return __("Course");
    }

    public static function getTableName()
    {
        return with(new static)->table;
    }

    public function getAdminJsDataAttribute()
    {
        $this->load('sections.modules');
        $sections = $this->sections;
        return [
            'id' => $this->id,
            'sections' => $sections,
            'i18n' => [
                'add_lecture' => [
                    'video' => __("Add video lecture"),
                    'scorm' => __("Add scorm lecture"),
                    'presentation' => __("Add presentation lecture"),
                    'iframe' => __("Add iframe lecture"),
                ],
                'validate' => [
                    'title' => __("Lecture name is required"),
                    'section_title' => __("Section name is required"),
                    'file_id' => __("File is required"),
                    'url' => __("Url is required"),
                    'duration' => __("Duration is required"),
                ]
            ],
            'routes' => [
                'store' => route('course.admin.lecture.store', ['id' => $this->id]),
                'delete' => route('course.admin.lecture.delete', ['id' => $this->id]),
                'store_section' => route('course.admin.section.store', ['id' => $this->id]),
                'delete_section' => route('course.admin.section.delete', ['id' => $this->id]),
            ]
        ];
    }

    public function getStudyJsDataAttribute()
    {
        $this->load('frontendSections.frontendModules');
        $sectionsData = $this->frontendSections;
        $sections = [];
        foreach ($sectionsData as $k => $section) {
            $sections[] = [
                'id' => $section->id,
                'title' => $section->title,
                'modules' => $section->modules_study_js_data
            ];
        }
        return [
            'id' => $this->id,
            'sections' => $sections,
            'i18n' => [],
            'routes' => [
                'log' => route('course.study-log')
            ]
        ];
    }

    /**
     * Get SEO fop page list
     *
     * @return mixed
     */
    static public function getSeoMetaForPageList()
    {
        $meta['seo_title'] = __("Search for Courses");
        if (!empty($title = setting_item_with_lang("course_page_list_seo_title", false))) {
            $meta['seo_title'] = $title;
        } else if (!empty($title = setting_item_with_lang("course_page_search_title"))) {
            $meta['seo_title'] = $title;
        }
        $meta['seo_image'] = null;
        if (!empty($title = setting_item("course_page_list_seo_image"))) {
            $meta['seo_image'] = $title;
        } else if (!empty($title = setting_item("course_page_search_banner"))) {
            $meta['seo_image'] = $title;
        }
        $meta['seo_desc'] = setting_item_with_lang("course_page_list_seo_desc");
        $meta['seo_share'] = setting_item_with_lang("course_page_list_seo_share");
        $meta['full_url'] = url()->current();
        return $meta;
    }

    public function terms()
    {
        return $this->hasMany($this->termCourseClass, "target_id");
    }

    public function getDetailUrl($include_param = true)
    {
        $param = [];
        if ($include_param) {
            if (!empty($date =  request()->input('date'))) {
                $dates = explode(" - ", $date);
                if (!empty($dates)) {
                    $param['start'] = $dates[0] ?? "";
                    $param['end'] = $dates[1] ?? "";
                }
            }
        }
        $urlDetail = app_get_locale(false, false, '/') . env('COURSE_ROUTE_PREFIX', 'course') . "/" . $this->slug;
        if (!empty($param)) {
            $urlDetail .= "?" . http_build_query($param);
        }
        return url($urlDetail);
    }

    public static function getLinkForPageSearch($locale = false, $param = [])
    {

        return url(app_get_locale(false, false, '/') . env('COURSE_ROUTE_PREFIX', 'course') . "?" . http_build_query($param));
    }

    public function getGallery($featuredIncluded = false)
    {
        if (empty($this->gallery))
            return $this->gallery;
        $list_item = [];
        if ($featuredIncluded and $this->image_id) {
            $list_item[] = [
                'large' => FileHelper::url($this->image_id, 'full'),
                'thumb' => FileHelper::url($this->image_id, 'thumb')
            ];
        }
        $items = explode(",", $this->gallery);
        foreach ($items as $k => $item) {
            $large = FileHelper::url($item, 'full');
            $thumb = FileHelper::url($item, 'thumb');
            $list_item[] = [
                'large' => $large,
                'thumb' => $thumb
            ];
        }
        return $list_item;
    }

    public function getEditUrl()
    {
        return url(route('course.admin.edit', ['id' => $this->id]));
    }

    public function isBookable()
    {
        if ($this->status != 'publish')
            return false;
        return true;
    }

    public static function searchForMenu($q = false)
    {
        $query = static::select('id', 'title as name');
        if (strlen($q)) {

            $query->where('title', 'like', "%" . $q . "%");
        }
        $a = $query->limit(10)->get();
        return $a;
    }

    public static function getMinMaxPrice()
    {
        $model = parent::selectRaw('MIN(price) AS min_price ,
                                    MAX(price) AS max_price ')->where("status", "publish")->first();
        if (empty($model->min_price) and empty($model->max_price)) {
            return [
                0,
                100
            ];
        }
        return [
            $model->min_price,
            $model->max_price
        ];
    }

    public function getReviewEnable()
    {
        return setting_item("course_enable_review", 0);
    }

    public function getReviewApproved()
    {
        return setting_item("course_review_approved", 0);
    }

    public function check_enable_review_after_booking()
    {
        $option = setting_item("course_enable_review_after_booking", 0);
        if ($option) {
            $number_review = $this->reviewClass::countReviewByServiceID($this->id, Auth::id()) ?? 0;
            $number_booking = $this->bookingClass::countBookingByServiceID($this->id, Auth::id()) ?? 0;
            if ($number_review >= $number_booking) {
                return false;
            }
        }
        return true;
    }

    public function check_allow_review_after_making_completed_booking()
    {
        $options = setting_item("course_allow_review_after_making_completed_booking", false);
        if (!empty($options)) {
            $status = json_decode($options);
            $booking = $this->bookingClass::select("status")
                ->where("object_id", $this->id)
                ->where("object_model", $this->type)
                ->where("customer_id", Auth::id())
                ->orderBy("id", "desc")
                ->first();
            $booking_status = $booking->status ?? false;
            if (!in_array($booking_status, $status)) {
                return false;
            }
        }
        return true;
    }

    public static function getReviewStats()
    {
        $reviewStats = [];
        if (!empty($list = setting_item("course_review_stats", []))) {
            $list = json_decode($list, true);
            foreach ($list as $item) {
                $reviewStats[] = $item['title'];
            }
        }
        return $reviewStats;
    }

    public function getReviewDataAttribute()
    {
        $list_score = [
            'score_total'  => 0,
            'score_text'   => __("Not rated"),
            'total_review' => 0,
            'rate_score'   => [],
        ];
        $dataTotalReview = $this->reviewClass::selectRaw(" AVG(rate_number) as score_total , COUNT(id) as total_review ")->where('object_id', $this->id)->where('object_model', $this->type)->where("status", "approved")->first();
        if (!empty($dataTotalReview->score_total)) {
            $list_score['score_total'] = number_format($dataTotalReview->score_total, 1);
            $list_score['score_text'] = Review::getDisplayTextScoreByLever(round($list_score['score_total']));
        }
        if (!empty($dataTotalReview->total_review)) {
            $list_score['total_review'] = $dataTotalReview->total_review;
        }
        $list_data_rate = $this->reviewClass::selectRaw('COUNT( CASE WHEN rate_number = 5 THEN rate_number ELSE NULL END ) AS rate_5,
                                                            COUNT( CASE WHEN rate_number = 4 THEN rate_number ELSE NULL END ) AS rate_4,
                                                            COUNT( CASE WHEN rate_number = 3 THEN rate_number ELSE NULL END ) AS rate_3,
                                                            COUNT( CASE WHEN rate_number = 2 THEN rate_number ELSE NULL END ) AS rate_2,
                                                            COUNT( CASE WHEN rate_number = 1 THEN rate_number ELSE NULL END ) AS rate_1 ')->where('object_id', $this->id)->where('object_model', $this->type)->where("status", "approved")->first()->toArray();
        for ($rate = 5; $rate >= 1; $rate--) {
            if (!empty($number = $list_data_rate['rate_' . $rate])) {
                $percent = ($number / $list_score['total_review']) * 100;
            } else {
                $percent = 0;
            }
            $list_score['rate_score'][$rate] = [
                'title'   => $this->reviewClass::getDisplayTextScoreByLever($rate),
                'total'   => $number,
                'percent' => round($percent),
            ];
        }
        return $list_score;
    }

    /**
     * Get Score Review
     *
     * Using for loop space
     */
    public function getScoreReview()
    {
        $course_id = $this->id;
        $list_score = Cache::rememberForever('review_' . $this->type . '_' . $course_id, function () use ($course_id) {
            $dataReview = $this->reviewClass::selectRaw(" AVG(rate_number) as score_total , COUNT(id) as total_review ")->where('object_id', $course_id)->where('object_model', $this->type)->where("status", "approved")->first();
            $score_total = !empty($dataReview->score_total) ? number_format($dataReview->score_total, 1) : 0;
            return [
                'score_total'  => $score_total,
                'total_review' => !empty($dataReview->total_review) ? $dataReview->total_review : 0,
            ];
        });
        $list_score['review_text'] =  $list_score['score_total'] ? Review::getDisplayTextScoreByLever(round($list_score['score_total'])) : __("Not rated");
        return $list_score;
    }

    public function getNumberReviewsInService($status = false)
    {
        return $this->reviewClass::countReviewByServiceID($this->id, false, $status, $this->type) ?? 0;
    }

    public function saveCloneByID($clone_id)
    {
        $old = parent::find($clone_id);
        if (empty($old)) return false;
        $selected_terms = $old->terms->pluck('term_id');
        $old->title = $old->title . " - Copy";
        $new = $old->replicate();
        $new->save();
        //Terms
        foreach ($selected_terms as $term_id) {
            $this->termClass::firstOrCreate([
                'term_id' => $term_id,
                'target_id' => $new->id
            ]);
        }
        //Language
        $langs = $this->carTranslationClass::where("origin_id", $old->id)->get();
        if (!empty($langs)) {
            foreach ($langs as $lang) {
                $langNew = $lang->replicate();
                $langNew->origin_id = $new->id;
                $langNew->save();
                $langSeo = SEO::where('object_id', $lang->id)->where('object_model', $lang->getSeoType() . "_" . $lang->locale)->first();
                if (!empty($langSeo)) {
                    $langSeoNew = $langSeo->replicate();
                    $langSeoNew->object_id = $langNew->id;
                    $langSeoNew->save();
                }
            }
        }
        //SEO
        $metaSeo = SEO::where('object_id', $old->id)->where('object_model', $this->seo_type)->first();
        if (!empty($metaSeo)) {
            $metaSeoNew = $metaSeo->replicate();
            $metaSeoNew->object_id = $new->id;
            $metaSeoNew->save();
        }
    }

    public function hasWishList()
    {
        return $this->hasOne($this->userWishListClass, 'object_id', 'id')->where('object_model', $this->type)->where('user_id', Auth::id() ?? 0);
    }

    public function isWishList()
    {
        if (Auth::id()) {
            if (!empty($this->hasWishList) and !empty($this->hasWishList->id)) {
                return 'active';
            }
        }
        return '';
    }

    public static function isEnable()
    {
        return setting_item('course_disable') == false;
    }

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'cat_id', 'id')->where('status', 'publish')->withDefault();
    }

    public function level()
    {
        return $this->belongsTo(CourseLevel::class, 'level_id', 'id')->where('status', 'publish')->withDefault();
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'course_id', 'id');
    }

    public function frontendModules()
    {
        return $this->hasMany(CourseModule::class, 'course_id', 'id')->where('active', 1)->orderBy('display_order', 'ASC');
    }

    public function getPreviewUrlEmbedAttribute()
    {
        return getYoutubeEmbedUrl($this->preview_url);
    }

    public function students()
    {
        return $this->hasManyThrough(\App\User::class, Course2User::class, 'user_id', 'id');
    }

    public function hasUser(User $user)
    {
        // check if user is in course user list
        return $this->students()->where('user_id', $user->id)->first();
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class, 'course_id', 'id');
    }
    public function frontendSections()
    {
        return $this->hasMany(CourseSection::class, 'course_id', 'id')->where('active', 1)->orderBy('display_order', 'ASC');
    }

    public function getAttributeInPageSearch()
    {
        $attribute = setting_item("course_attribute_in_page_search", 0);
        return $this->hasManyThrough($this->termClass, $this->termCourseClass, 'target_id', 'id', 'id', 'term_id')->where('bc_terms.attr_id', $attribute)->with(['translations']);
    }

    public function addToCartValidate($qty = 1, $variation_id = false)
    {
        // TODO: apply only one course in cart or one cart per each user
        return true;
    }

    public function teacher()
    {
        $teacherClass = get_class(app(Teacher::class));
        return $this->belongsTo($teacherClass, "author_id", "id")->withDefault();
    }

    public function getDurationTextAttribute()
    {
        return $this->duration ? minute_format($this->duration) : '';
    }


    /**
     * @param $request
     * [location_id] -> number
     * [s] -> keyword
     * @return array|\Illuminate\Database\Eloquent\Builder
     */
    public function search($request)
    {
        $modelCourse = parent::query()->select("courses.*");
        $modelCourse->where("courses.status", "publish");

        $category_ids = $request['category_id'] ?? [];
        if (!empty($category_ids)) {
            $category_ids = array_filter(array_values($category_ids));
            $list_cat = CourseCategory::whereIn('id', $category_ids)->where("status", "publish")->get();
            foreach ($list_cat as $index => $cat) {
                $modelCourse->join('course_category as tc' . $index, function ($join) use ($cat, $index) {
                    $join->on('tc' . $index . '.id', '=', 'courses.cat_id')
                        ->where('tc' . $index . '._lft', '>=', $cat->_lft)
                        ->where('tc' . $index . '._rgt', '<=', $cat->_rgt);
                });
            }
        }

        $level_ids = $request['level'] ?? [];
        if (!empty($level_ids)) {
            $modelCourse->whereIn('level_id', $level_ids);
        }

        $instructors_ids = $request['instructors'] ?? [];
        if (!empty($instructors_ids)) {
            $modelCourse->whereIn('author_id', $instructors_ids);
        }
        if (!empty($request['notInIds'])) {
            $modelCourse->whereNotIn('id', $request['notInIds']);
        }

        if (!empty($request['attrs'])) {
            $modelCourse = $this->filterAttrs($modelCourse, $request['attrs'], 'course_term', 'target_id');
        }

        $review_scores = $request["review_score"] ?? [];
        if (!empty($review_scores)) {
            $review_scores = array_filter(array_values($review_scores));
            $modelCourse = $this->filterReviewScore($modelCourse, $review_scores);
        }

        $course_name = $request['course_name'] ?? '';
        if (!empty($course_name)) {
            if (setting_item('site_enable_multi_lang') && setting_item('site_locale') != app()->getLocale()) {
                $modelCourse->leftJoin('course_translations', function ($join) {
                    $join->on('courses.id', '=', 'course_translations.origin_id');
                });
                $modelCourse->where('course_translations.title', 'LIKE', '%' . $course_name . '%');
            } else {
                $modelCourse->where('courses.title', 'LIKE', '%' . $course_name . '%');
            }
        }

        $duration = $request['duration'] ?? '';
        if (!empty($duration)) {
            $duration = explode('-', $duration);
            $modelCourse->where('courses.duration', '>=', $duration[0])->where('courses.duration', '<=', $duration[1]);
        }

        $language = $request['language'] ?? '';
        if (!empty($language)) {
            $modelCourse->where('courses.language', $language);
        }

        $is_featured = $request['is_featured'] ?? '';
        if (!empty($is_featured)) {
            $modelCourse->where('courses.is_featured', 1);
        }

        $orderby = $request['orderby'] ?? "";
        switch ($orderby) {
            case "price_low":
                $modelCourse->orderBy("price", "asc");
                break;
            case "price_high":
                $modelCourse->orderBy("price", "desc");
                break;
            case "rate_high":
                $modelCourse->orderBy($modelCourse->qualifyColumn("review_score"), "desc");
                break;
            case "new":
                $modelCourse->orderBy($modelCourse->qualifyColumn("created_at"), "desc");
                break;
            case "old":
                $modelCourse->orderBy($modelCourse->qualifyColumn("created_at"), "asc");
                break;
            default:
                if (!empty($request['order']) and !empty($request['order_by'])) {
                    $modelCourse->orderBy($modelCourse->qualifyColumn($request['order']), $request['order_by']);
                } else {
                    $modelCourse->orderBy($modelCourse->qualifyColumn("is_featured"), "desc");
                    $modelCourse->orderBy($modelCourse->qualifyColumn("id"), "desc");
                }
        }

        $modelCourse->groupBy($modelCourse->qualifyColumn("id"));
        return $modelCourse->with([
            'hasWishList'
        ]);
    }

    public function relatedCourses()
    {
        return $this->hasMany(Course::class, 'cat_id', 'cat_id')->where('status', 'publish')->where('id', '!=', $this->id)->limit(12);
    }

    /**
     * Get the query for the user study log
     *
     * @param User|int $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getUserStudyLogQuery(User | int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        $class = app()->make(CourseModuleCompletion::class);
        return $class->where('course_id', $this->id)->where('user_id', $userId);
    }


    // Limit 1 qty of a course in cart
    public function getMaxQuantity(){
        return 1;
    }
}
