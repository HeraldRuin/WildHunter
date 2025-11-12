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
use Modules\Course\Models\CourseSection;

class SectionController extends AdminController
{

    public function __construct()
    {
        $this->setActiveMenu(route('course.admin.index'));
    }

    public function store($id = ''){
        $row = $this->checkItemPermission($id);

        if(empty($row)){
            return $this->sendError(__("Course not found"));
        }
        $rules = [
            'title'=>'required',
        ];

        request()->validate($rules);

        if($section_id = request()->input('id')){
            $section = CourseSection::find($section_id);
            if(empty($section)){
                return $this->sendError(__("Section not found"));
            }
        }else{
            $section = new CourseSection();
            $section->course_id = $id;
        }

        $section->fillByAttr([
            'title',
            'active',
            'display_order'
        ],request()->input());

        $section->save();
        $section->load('modules');

        if($section_id){
            return $this->sendSuccess(__("Section updated"));
        }else{
            return $this->sendSuccess(['section'=>$section],__("Section created"));
        }
    }

    public function destroy(Request $request, $id){
        $row = $this->checkItemPermission($id);
        if(empty($row)){
            return $this->sendError(__("Course not found"));
        }

        $section_id = $request->get('section_id');
        $section = CourseSection::find($section_id);
        if($section){
            $section->modules()->delete();
            $section->delete();
        }

        return $this->sendSuccess(__("Delete section successfully!"));;
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
