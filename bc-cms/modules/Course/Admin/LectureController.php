<?php

namespace Modules\Course\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseTerm;
use Modules\Course\Models\CourseTranslation;
use Modules\Core\Models\Attributes;
use Modules\Course\Models\CourseCategory;
use Modules\Course\Models\CourseModule;

class LectureController extends AdminController
{

    public function __construct()
    {
        $this->setActiveMenu(route('course.admin.index'));
    }

    public function index($id = ''){
        $row = $this->checkItemPermission($id);

        if(empty($row)){
            abort(403);
        }

        $data = [
            'rows'=>$row->sections,
            'row'=>$row,
            'page_title'=>__("Lessons Management"),
            'breadcrumbs'        => [
                [
                    'name' => __('Courses'),
                    'url'  => route('course.admin.index')
                ],
                [
                    'name'  => $row->title,
                    'url'  => route('course.admin.edit',['id'=>$row->id]),
                ],
                [
                    'name'  => __("Lessons Management"),
                    'class' => 'active'
                ],
            ],
        ];

        return view('Course::admin.lectures.index',$data);
    }

    public function store($id = ''){
        $row = $this->checkItemPermission($id);

        if(empty($row)){
            return $this->sendError(__("Course not found"));
        }
        $type = request()->input('type');
        $section_id = request()->input('section_id');

        $rules = [
            'title'=>'required',
            // 'file_id'=>'required',
            'duration'=>'required',
            'type'=>'required',
            'section_id'=>'required'
        ];

        // if($type == 'iframe'){
        //     unset($rules['file_id']);
        //     $rules['url'] = 'required';
        // }

        request()->validate($rules);

        if($module_id = request()->input('id')){
            $module = CourseModule::find($module_id);
            if(empty($module)){
                return $this->sendError(__("Lecture not found"));
            }
        }else{
            $module = new CourseModule();
            $module->course_id = $id;
            $module->section_id = $section_id;
        }

        $module->fillByAttr([
            'title',
            'file_id',
            'active',
            'preview_url',
            'url',
            'duration',
            'type',
            'display_order'
        ],request()->input());

        $module->save();

        if($module_id){
            return $this->sendSuccess(__("Lecture updated"));
        }else{
            return $this->sendSuccess(['lecture'=>$module],__("Lecture created"));
        }
    }

    public function destroy(Request $request, $id){
        $row = $this->checkItemPermission($id);
        if(empty($row)){
            return $this->sendError(__("Course not found"));
        }

        $module_id = $request->get('module_id');
        $module = CourseModule::find($module_id);
        $module->delete();

        return $this->sendSuccess(__("Delete lecture successfully!"));;
    }

    protected function checkItemPermission($id){

        if(empty($id)) return false;
        $row = Course::find($id);

        if(empty($row)) return false;

        if(!$this->hasPermission('course_manage_others'))
        {
            if($row->author_id != Auth::id()){
                return false;
            }
        }
        return $row;
    }
}
