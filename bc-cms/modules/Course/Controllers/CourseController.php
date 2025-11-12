<?php
namespace Modules\Course\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Course\Models\Course;
use Illuminate\Http\Request;
use Modules\Course\Models\Course2User;
use Modules\Course\Models\CourseCategory;
use Modules\Course\Models\CourseLevel;
use Modules\Course\Models\CourseModuleCompletion;
use Modules\Course\Models\CourseStudyLog;
use Modules\Review\Models\Review;
use Modules\Core\Models\Attributes;
use Modules\Course\Traits\HasStudyActions;

class CourseController extends Controller
{
    use HasStudyActions;

    public $course;
    public $module_id;
    public $module;

    protected $courseClass;
    protected $locationClass;
    public function __construct(Course $course)
    {
        $this->courseClass = $course;
    }

    public function index(Request $request)
    {
        $layout = setting_item("course_layout_search", 'style-1');
        if ($request->query('_layout')) {
            $layout = $request->query('_layout');
        }
        $is_ajax = $request->query('_ajax');

        $query = $this->courseClass->search($request->input());
        $limit = 12;
        $list = $query->paginate($limit);

        if ($is_ajax) {
            return $this->sendSuccess([
                'fragments'=>[
                    '.ajax-search-result'=>view('Course::frontend.ajax.search-result', ['rows' => $list, 'layout' => $layout])->render(),
                    '.result-count' => $list->total(),
                ]
            ]);
        }

        $course_page_title = setting_item_with_lang("course_page_search_title", app()->getLocale(), __("Courses"));

        $instructors = \App\Models\User::query()
            ->whereHas('role', function ($q){
                $q->where('code', 'teacher');
            })
            ->where('role_id', 2)
            ->get();

        $data = [
            'rows'               => $list,
            "blank"              => 1,
            "layout"              => $layout,
            "seo_meta"           => $this->courseClass::getSeoMetaForPageList(),
            "course_page_title" => $course_page_title,
            "list_category" => CourseCategory::where('status', 'publish')->get()->toTree(),
            "list_levels" => CourseLevel::where('status', 'publish')->get(),
            "attributes" => Attributes::where('service', 'course')->with(['terms'])->get(),
            "instructors" => $instructors,
            "course_page_sub_title" => setting_item_with_lang("course_page_search_sub_title"),
            'breadcrumbs'       => [
                [
                    'name'  => $course_page_title,
                    'class' => 'active'
                ],
            ],
        ];

        return view('Course::frontend.search', $data);
    }

    public function detail(Request $request, $slug = '')
    {
        if(empty($slug)){
            return redirect('/');
        }
        $row = $this->courseClass::where('slug', $slug)->where("status", "publish")
            ->with(['sections.modules'])
            ->first();
        if (empty($row)) {
            return redirect('/');
        }
        $translation = $row->translate(app()->getLocale());
        $category_id = $row->cat_id;
        if (!empty($category_id)) {
            $course_related = $this->courseClass::where("status", "publish")
                ->take(8)
                ->where('cat_id', $category_id)
                ->whereNotIn('id', [$row->id])
                ->get();
        }

        $review_list = Review::where('object_id', $row->id)->where('object_model', 'course')->where("status", "approved")->orderBy("id", "desc")->with('author')->paginate(setting_item('course_review_number_per_page', 5));

        $data = [
            'row'          => $row,
            'translation'       => $translation,
            'course_related' => $course_related ?? [],
            'review_list'  => $review_list,
            'seo_meta'  => $row->getSeoMetaWithTranslation(app()->getLocale(),$translation),
            'review_data'=>$row->getScoreReview(),
            'breadcrumbs'       => [
                [
                    'name' => __('Course'),
                    'url'  => route('course.search')
                ],
                [
                    'name'  => $translation->title,
                    'class' => 'active'
                ],
            ],
        ];
        $this->setActiveMenu($row);
        return view('Course::frontend.detail', $data);
    }

    public function learn(Request $request, $slug = '')
    {
        if(empty($slug)){
            return redirect('/');
        }
        $row = $this->courseClass::where('slug', $slug)->where("status", "publish")
            ->with(['translations','hasWishList','sections.modules'])
            ->first();;
        if (empty($row)) {
            return redirect('/');
        }
        $translation = $row->translate(app()->getLocale());

        $row->views++;
        $row->save();

        $data = [
            'row'          => $row,
            'translation'       => $translation,
            'seo_meta'  => $row->getSeoMetaWithTranslation(app()->getLocale(),$translation),
            'body_class'=>'is_single',
            'review_data'=>$row->getScoreReview(),
            'header_transparent'=>1
        ];
        $this->setActiveMenu($row);
        $this->registerJs('module/course/js/study.vue.js');
        return view('Course::frontend.learn', $data);
    }

    public function studyLog(){
        \request()->validate([
            'course_id'=>'required',
            'module_id'=>'required',
        ]);

        $module_id = \request()->input('module_id');
        $course_id = \request()->input('course_id');

        $this->course = $this->courseClass->whereId($course_id)->isPublic()->first();
        if(!$this->course){
            return $this->sendError(__("Course not found"));
        }
        $this->module_id = $module_id;
        $this->module = $this->course->modules()->whereId($module_id)->first();

        if(!$this->module){
            return $this->sendError(__("Module not found"));
        }

        try {
            // Validate if the user can study the module
            $this->checkUserCanStudy();

            // Add a log to the course study log table
            $log = $this->addLog();

            return $this->sendSuccess(['log'=>$log],'OK');

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

    }
}
