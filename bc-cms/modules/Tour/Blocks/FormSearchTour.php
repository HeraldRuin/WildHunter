<?php
namespace Modules\Tour\Blocks;

use Modules\Template\Blocks\BaseBlock;
use Modules\Location\Models\Location;
use Modules\Media\Helpers\FileHelper;
use Modules\Tour\Models\TourCategory;

class  FormSearchTour extends BaseBlock
{
    public $title;
    public $sub_title;
    public $style;
    public $bg_image;
    public $list_slider;

    public function getOptions(){
        return [
            'settings' => [
                [
                    'id'        => 'title',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Title')
                ],
                [
                    'id'        => 'sub_title',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Sub Title')
                ],
                [
                    'id'            => 'style',
                    'type'          => 'radios',
                    'label'         => __('Style Background'),
                    'values'        => [
                        [
                            'value'   => '',
                            'name' => __("Normal")
                        ],
                        [
                            'value'   => 'carousel',
                            'name' => __("Slider Carousel")
                        ],
                        [
                            'value'   => 'carousel_v2',
                            'name' => __("Slider Carousel Ver 2")
                        ]
                    ]
                ],
                [
                    'id'    => 'bg_image',
                    'type'  => 'uploader',
                    'label' => __('- Layout Normal: Background Image Uploader')
                ],
                [
                    'id'          => 'list_slider',
                    'type'        => 'listItem',
                    'label'       => __('- Layout Slider: List Item(s)'),
                    'title_field' => 'title',
                    'settings'    => [
                        [
                            'id'        => 'title',
                            'type'      => 'input',
                            'inputType' => 'text',
                            'label'     => __('Title (using for slider ver 2)')
                        ],
                        [
                            'id'        => 'desc',
                            'type'      => 'input',
                            'inputType' => 'text',
                            'label'     => __('Desc (using for slider ver 2)')
                        ],
                        [
                            'id'    => 'bg_image',
                            'type'  => 'uploader',
                            'label' => __('Background Image Uploader')
                        ]
                    ]
                ]
            ],
            'category'=>__("Service Tour")
        ];
    }

    public function getTitle()
    {
        return __('Tour: Form Search');
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'style' => $this->style,
            'bg_image' => $this->bg_image,
            'list_slider' => $this->list_slider,
        ];
        $data = [
            'tour_location' => Location::where("status","publish")->limit(1000)->with(['translation'])->get()->toTree(),
            'bg_image_url'  => '',
        ];
        $data = array_merge($model, $data);
        if (!empty($model['bg_image'])) {
            $data['bg_image_url'] = FileHelper::url($model['bg_image'], 'full');
        }
        $data['style'] = $model['style'] ?? "";
        $data['list_slider'] = $model['list_slider'] ?? "";
        $data['tour_category'] = TourCategory::where('status', 'publish')->with(['translation'])->get()->toTree();
        return $this->view('Tour::frontend.blocks.form-search-tour.index', $data);
    }

    public function contentAPI($model = []){
        if (!empty($model['bg_image'])) {
            $model['bg_image_url'] = FileHelper::url($model['bg_image'], 'full');
        }
        return $model;
    }
}
