<?php
namespace Modules\Space\Blocks;

use Modules\Template\Blocks\BaseBlock;
use Modules\Core\Models\Terms;

class SpaceTermFeaturedBox extends BaseBlock
{
    public $title;
    public $desc;
    public $term_space;

    public function getTitle()
    {
        return __('Space: Term Featured Box');
    }

    public function getOptions()
    {
        return [
            'settings' => [
                [
                    'id'        => 'title',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Title')
                ],
                [
                    'id'        => 'desc',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Desc')
                ],
                [
                    'id'           => 'term_space',
                    'type'         => 'select2',
                    'label'        => __('Select term space'),
                    'select2'      => [
                        'ajax'     => [
                            'url'      => route('space.admin.attribute.term.getForSelect2', ['type' => 'space']),
                            'dataType' => 'json'
                        ],
                        'width'    => '100%',
                        'multiple' => "true",
                    ],
                    'pre_selected' => route('space.admin.attribute.term.getForSelect2', [
                        'type'         => 'space',
                        'pre_selected' => 1
                    ])
                ],
            ],
            'category'=>__("Service Space")
        ];
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'desc' => $this->desc,
            'term_space' => $this->term_space,
            'list_term' => []
        ];

        if (!empty($term_space = $model['term_space'])) {
            $list_term = Terms::whereIn('id', $term_space)->with('translation')->get();
            $model['list_term'] = $list_term;
        }
        return $this->view('Space::frontend.blocks.term-featured-box.index', $model);
    }

    public function contentAPI($model = []){
        $model['list_term'] = null;
        if (!empty($term_space = $model['term_space'])) {
            $list_term = Terms::whereIn('id',$term_space)->get();
            if(!empty($list_term)){
                foreach ( $list_term as $item){
                    $model['list_term'][] = [
                        "id"=>$item->id,
                        "attr_id"=>$item->attr_id,
                        "name"=>$item->name,
                        "image_id"=>$item->image_id,
                        "image_url"=>get_file_url($item->image_id,"full"),
                        "icon"=>$item->icon,
                    ];
                }
            }
        }
        return $model;
    }
}
