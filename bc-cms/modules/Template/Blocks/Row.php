<?php
namespace Modules\Template\Blocks;
class Row extends BaseBlock
{
    public $is_container;
    public $padding = [];
    public $margin = [];

    public function getTitle()
    {
        return __('Section');
    }

    public function getOptions()
    {
        return [
            'parent_of'    => ['column'],
            'is_container' => true,
            'component'    => 'RowBlock',
            'category' => __("Other Block"),
            'setting_tabs' => [
                'content' => [
                    'label' => __("Content"),
                    'icon' => 'fa fa-pencil',
                    'order' => 1
                ],
                'style' => [
                    'label' => __("Style"),
                    'order' => 2,
                    'icon' => 'fa fa-object-group',
                ],
            ],
            'settings' => [
                [
                    'id' => 'is_container',
                    'type' => 'radios',
                    "std" => '',
                    'label' => __('Is Container?'),
                    'values' => [
                        [
                            'value' => '',
                            'name' => __("Yes")
                        ],
                        [
                            'value' => 'no',
                            'name' => __("No")
                        ],
                    ],
                    'tab' => 'content'
                ],
                [
                    'id' => 'padding',
                    'type' => 'spacing',
                    'label' => __('Padding'),
                    'tab' => 'style'
                ],
                [
                    'id' => 'margin',
                    'type' => 'spacing',
                    'label' => __('Margin'),
                    'tab' => 'style'
                ],
            ],
            'preset_children' => [
                'c1' => [
                    'type' => 'column',
                    'model' => ['size' => 6]
                ],
                'c2' => [
                    'type' => 'column',
                    'model' => ['size' => 6]
                ]
            ]
        ];
    }

    function render()
    {
        $data = [
            'children' => $this->children(),
            'css_code' => generate_css([
                'padding' => $this->padding,
                'margin' => $this->margin,
            ])
        ];
        return $this->view('Template::frontend.blocks.row', $data);
    }
}
