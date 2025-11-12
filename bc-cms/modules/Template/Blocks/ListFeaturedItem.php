<?php
namespace Modules\Template\Blocks;

use Modules\Media\Helpers\FileHelper;

class ListFeaturedItem extends BaseBlock
{
    public $list_item;
    public $style;


    public function getOptions(){
        return [
            'settings' => [
                [
                    'id'          => 'list_item',
                    'type'        => 'listItem',
                    'label'       => __('List Item(s)'),
                    'title_field' => 'title',
                    'settings'    => [
                        [
                            'id'        => 'title',
                            'type'      => 'input',
                            'inputType' => 'text',
                            'label'     => __('Title')
                        ],
                        [
                            'id'        => 'sub_title',
                            'type'      => 'input',
                            'inputType' => 'textArea',
                            'label'     => __('Sub Title')
                        ],
                        [
                            'id'    => 'icon_image',
                            'type'  => 'uploader',
                            'label' => __('Image Uploader')
                        ],
                        [
                            'id'        => 'order',
                            'type'      => 'input',
                            'inputType' => 'number',
                            'label'     => __('Order')
                        ],
                    ]
                ],
                [
                    'id'            => 'style',
                    'type'          => 'radios',
                    'label'         => __('Style'),
                    'values'        => [
                        [
                            'value'   => 'normal',
                            'name' => __("Normal")
                        ],
                        [
                            'value'   => 'style2',
                            'name' => __("Style 2")
                        ],
                        [
                            'value'   => 'style3',
                            'name' => __("Style 3")
                        ],
                        [
                            'value'   => 'style4',
                            'name' => __("Style 4")
                        ],
                        [
                            'value'   => 'style5',
                            'name' => __("Style 5")
                        ]
                    ]
                ]
            ],
            'category'=>__("Other Block")
        ];
    }

    public function getTitle()
    {
        return __('List Featured Item');
    }

    public function render()
    {
        $model = [
            'list_item' => $this->list_item,
            'style' => $this->style,
        ];
        return $this->view('Template::frontend.blocks.list-featured-item.index', $model);
    }
    public function contentAPI($model = []){
        if(!empty($model['list_item'])){
            foreach (  $model['list_item'] as &$item ){
                $item['icon_image_url'] = FileHelper::url($item['icon_image'], 'full');
            }
        }
        return $model;
    }
}
