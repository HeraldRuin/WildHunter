<?php
namespace Modules\Template\Blocks;
class Column extends BaseBlock
{
    public $size = 6;
    public $padding = [];
    public $margin = [];
    public function getTitle()
    {
        return __('Column');
    }
    public function getOptions()
    {
        return [
            'child_of'     => ['row'],
            'is_container' => true,
            'component'    => 'ColumnBlock',
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
            'settings'     => [
                [
                    'id' => 'size',
                    'type' => 'select',
                    'label' => __('Column Size'),
                    "std" => 6,
                    'values' => [
                        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12
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
            ]
        ];
    }

    public function render()
    {
        return $this->view('Template::frontend.blocks.column', [
            'size' => $this->size,
            'children' => $this->children(),
            'wrapper_class' => 'col-' . $this->size,
            'css_code' => generate_css([
                'padding' => $this->padding,
                'margin' => $this->margin,
            ])
        ]);
    }
}
