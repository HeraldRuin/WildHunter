<?php
namespace Modules\Course\Admin;

use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Course\Models\CourseLevel;
use Modules\Course\Models\CourseLevelTranslation;


class LevelController extends AdminController
{
    protected $courseLevelClass;
    public function __construct()
    {
        $this->setActiveMenu(route('course.admin.index'));
        $this->courseLevelClass = CourseLevel::class;
    }

    public function index(Request $request)
    {
        $this->checkPermission('course_manage_attributes');
        $listLevels = $this->courseLevelClass::query();
        if (!empty($search = $request->query('s'))) {
            $listLevels->where('name', 'LIKE', '%' . $search . '%');
        }
        $listLevels->orderBy('created_at', 'desc');
        $data = [
            'rows'        => $listLevels->get(),
            'row'         => new $this->courseLevelClass(),
            'translation'    => new CourseLevelTranslation(),
            'breadcrumbs' => [
                [
                    'name' => __('Course'),
                    'url'  => route('course.admin.index')
                ],
                [
                    'name'  => __('Skill Level'),
                    'class' => 'active'
                ],
            ]
        ];
        return view('Course::admin.level.index', $data);
    }

    public function edit(Request $request, $id)
    {
        $this->checkPermission('course_manage_attributes');
        $row = $this->courseLevelClass::find($id);
        if (empty($row)) {
            return redirect(route('course.admin.level.index'));
        }
        $translation = $row->translate($request->query('lang'));
        $data = [
            'translation'    => $translation,
            'enable_multi_lang'=>true,
            'row'         => $row,
            'breadcrumbs' => [
                [
                    'name' => __('Course'),
                    'url'  => route('course.admin.index')
                ],
                [
                    'name'  => __('Level'),
                    'url'  => route('course.admin.level.index')
                ],
                [
                    'name'  => __('Level :name',['name'=>$row->name]),
                    'class' => 'active'
                ],
            ]
        ];
        return view('Course::admin.level.detail', $data);
    }

    public function store(Request $request , $id = 0)
    {
        $this->checkPermission('course_manage_attributes');
        $this->validate($request, [
            'name' => 'required'
        ]);

        if($id>0){
            $row = $this->courseLevelClass::find($id);
            if (empty($row)) {
                return redirect(route('course.admin.level.index'));
            }
        }else{
            $row = new $this->courseLevelClass();
        }
        $row->fill($request->input());
        $res = $row->saveOriginOrTranslation($request->input('lang'));

        return back()->with('success',  __('Level saved') );

    }

    public function bulkEdit(Request $request)
    {
        $this->checkPermission('course_manage_attributes');
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('Select at least 1 item!'));
        }
        if (empty($action)) {
            return redirect()->back()->with('error', __('Select an Action!'));
        }
        if ($action == "delete") {
            foreach ($ids as $id) {
                $query = $this->courseLevelClass::where("id", $id)->first();
                if(!empty($query)){
                    $query->delete();
                }
            }
        } else {
            foreach ($ids as $id) {
                $query = $this->courseLevelClass::where("id", $id);
                $query->update(['status' => $action]);
            }
        }
        return redirect()->back()->with('success', __('Updated success!'));
    }

    public function getForSelect2(Request $request)
    {
        $pre_selected = $request->query('pre_selected');
        $selected = $request->query('selected');

        if($pre_selected && $selected){
            $item = $this->courseLevelClass::find($selected);
            if(empty($item)){
                return response()->json([
                    'text'=>''
                ]);
            }else{
                return response()->json([
                    'text'=>$item->name
                ]);
            }
        }
        $q = $request->query('q');
        $query = $this->courseLevelClass::select('id', 'name as text')->where("status","publish");
        if ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        $res = $query->orderBy('id', 'desc')->limit(20)->get();
        return response()->json([
            'results' => $res
        ]);
    }
}
