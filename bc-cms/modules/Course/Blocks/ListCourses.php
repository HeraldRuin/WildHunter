<?php
namespace Modules\Course\Blocks;

use Modules\Template\Blocks\BaseBlock;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseCategory;

class ListCourses extends BaseBlock
{
    public $title;
    public $number;
    public $style;
    public $category_id;
    public $order;
    public $order_by;

    function __construct()
    {
        $this->setOptions([
            'settings' => [
                [
                    'id'        => 'title',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Title')
                ],
                [
                    'id'        => 'number',
                    'type'      => 'input',
                    'inputType' => 'number',
                    'label'     => __('Number Item')
                ],
                [
                    'id'     => 'style',
                    'type'   => 'radios',
                    'label'  => __('Style'),
                    'values' => [
                        [
                            'value' => 'style_1',
                            'name'  => __("Style 1")
                        ],
                        [
                            'value' => 'style_2',
                            'name'  => __("Style 2")
                        ],
                        [
                            'value' => 'style_3',
                            'name'  => __("Style 3")
                        ],
                    ]
                ],
                [
                    'id'      => 'category_id',
                    'type'    => 'select2',
                    'label'   => __('Filter by Category'),
                    'select2' => [
                        'ajax'  => [
                            'url'      => url('/admin/module/course/category/getForSelect2'),
                            'dataType' => 'json'
                        ],
                        'width' => '100%',
                        'allowClear' => 'true',
                        'placeholder' => __('-- Select --')
                    ],
                    'pre_selected'=>url('/admin/module/course/category/getForSelect2?pre_selected=1')
                ],
                [
                    'id'     => 'order',
                    'type'   => 'radios',
                    'label'  => __('Order'),
                    'values' => [
                        [
                            'value' => 'id',
                            'name'  => __("Date Create")
                        ],
                        [
                            'value' => 'title',
                            'name'  => __("Title")
                        ],
                    ]
                ],
                [
                    'id'     => 'order_by',
                    'type'   => 'radios',
                    'label'  => __('Order By'),
                    'values' => [
                        [
                            'value' => 'asc',
                            'name'  => __("ASC")
                        ],
                        [
                            'value' => 'desc',
                            'name'  => __("DESC")
                        ],
                    ]
                ],
                /*[
                    'type'=> "checkbox",
                    'label'=>__("Only featured items?"),
                    'id'=> "is_featured",
                    'default'=>true
                ]*/
            ]
        ]);
    }

    public function getTitle()
    {
        return __('Courses: List Items');
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'number' => $this->number,
            'style' => $this->style,
            'category_id' => $this->category_id,
            'order' => $this->order,
            'order_by' => $this->order_by,
        ];
        $modelClass = Course::select("courses.*");
        if (empty($model['order']))
            $model['order'] = "id";
        if (empty($model['style']))
            $model['style'] = "style_1";
        if (empty($model['order_by']))
            $model['order_by'] = "desc";
        if (empty($model['number']))
            $model['number'] = 5;
        if (!empty($model['is_featured'])) {
            $modelClass->where('is_featured', 1);
        }
        $modelClass->orderBy("courses." . $model['order'], $model['order_by']);
        if (!empty($model['category_id'])) {
            $category_ids = [$model['category_id']];
            $list_cat = CourseCategory::whereIn('id', $category_ids)->where("status","publish")->get();
            if(!empty($list_cat)){
                $where_left_right = [];
                foreach ($list_cat as $cat){
                    $where_left_right[] = " ( course_category._lft >= {$cat->_lft} AND course_category._rgt <= {$cat->_rgt} ) ";
                }
                $sql_where_join = " ( ".implode("OR" , $where_left_right)." )  ";
                $modelClass
                    ->join('course_category', function ($join) use($sql_where_join) {
                        $join->on('course_category.id', '=', 'courses.cat_id')
                            ->WhereRaw($sql_where_join);
                    });
            }
        }
        $modelClass->where("courses.status", "publish");
        $modelClass->groupBy("courses.id");
        $list = $modelClass->limit($model['number'])->get();
        $data = [
            'rows'       => $list,
            'title'      => $model['title'] ?? "",
            'desc'       => $model['desc'] ?? "",
        ];
        return $this->view('Course::frontend.blocks.list-course.'.$model['style'], $data);
    }
}
