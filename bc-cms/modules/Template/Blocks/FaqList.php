<?php
namespace Modules\Template\Blocks;

use Modules\Template\Blocks\BaseBlock;

class FaqList extends BaseBlock
{
    public $title;
    public $list_item;
    public $style;

    public function getTitle()
    {
        return __('FAQ List');
    }

    public function getOptions()
    {
        return [
            'settings' => [
                [
                    'id'            => 'style',
                    'type'          => 'radios',
                    'label'         => __('Style'),
                    'values'        => [
                        [
                            'value'   => '',
                            'name' => __("Style 1")
                        ],
                        [
                            'value'   => 'style_2',
                            'name' => __("Style 2")
                        ],
                    ]
                ],
                [
                    'id'        => 'title',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Title')
                ],
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
                            'label'     => __('Question')
                        ],
                        [
                            'id'        => 'sub_title',
                            'type'      => 'editor',
                            'inputType' => 'textArea',
                            'label'     => __('Answer')
                        ],
                    ]
                ],
            ],
            'category'=>__("Other Block")
        ];
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'list_item' => $this->list_item,
            'style' => $this->style,
        ];
        if(($this->style ?? '') === 'style_2'){
            return $this->view('Template::frontend.blocks.faq.style2', $model);
        }
        return $this->view('Template::frontend.blocks.faq-list', $model);
    }
}
