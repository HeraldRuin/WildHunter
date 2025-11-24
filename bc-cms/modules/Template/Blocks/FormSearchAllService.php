<?php
namespace Modules\Template\Blocks;

use Modules\Animals\Models\Animal;
use Modules\Location\Models\Location;
use Modules\Media\Helpers\FileHelper;
use Modules\Tour\Models\TourCategory;

class FormSearchAllService extends BaseBlock
{
    public $title;
    public $sub_title;
    public $service_types;
    public $style;
    public $bg_image;
    public $video_url;
    public $list_slider;
    public $hide_form_search;


    public function getTitle()
    {
        return __('Form Search All Service');
    }

    public function getOptions()
    {
        $list_service = [];
        foreach (get_bookable_services() as $key => $service) {
            $list_service[] = ['value'   => $key,
                'name' => ucwords($key)
            ];
            $arg[] = [
                'id'        => 'title_for_'.$key,
                'type'      => 'input',
                'inputType' => 'text',
                'label'     => __('Title for :service',['service'=>ucwords($key)])
            ];
        }
        $arg[] = [
            'id'            => 'service_types',
            'type'          => 'checklist',
            'listBox'          => 'true',
            'label'         => "<strong>".__('Service Type')."</strong>",
            'values'        => $list_service,
            'std'=>[]
        ];

        $arg[] = [
            'id'        => 'title',
            'type'      => 'input',
            'inputType' => 'text',
            'label'     => __('Title')
        ];
        $arg[] = [
            'id'        => 'sub_title',
            'type'      => 'input',
            'inputType' => 'text',
            'label'     => __('Sub Title')
        ];

        $arg[] =  [
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
                ],
                [
                    'value'   => 'bg_video',
                    'name' => __("Background Video")
                ],
            ]
        ];

        $arg[] = [
            'id'    => 'bg_image',
            'type'  => 'uploader',
            'label' => __('- Layout Normal: Background Image Uploader')
        ];

        $arg[] = [
            'id'        => 'video_url',
            'type'      => 'input',
            'inputType' => 'text',
            'label' => __('- Layout Video: Youtube Url')
        ];

        $arg[] = [
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
        ];

        $arg[] = [
            'type'=> "checkbox",
            'label'=>__("Hide form search service?"),
            'id'=> "hide_form_search",
            'default'=>false
        ];

        return [
            'settings' => $arg,
            'category'=>__("Other Block")
        ];
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'service_types' => $this->service_types,
            'style' => $this->style,
            'bg_image' => $this->bg_image,
            'video_url' => $this->video_url,
            'list_slider' => $this->list_slider,
            'hide_form_search' => $this->hide_form_search,
        ];
        $model['bg_image_url'] = FileHelper::url($model['bg_image'] ?? "", 'full') ?? "";
        $model['list_location'] = $model['tour_location'] = Location::where("status", "publish")->limit(1000)->with(['translation'])->get()->toTree();
        $model['list_animals'] = $model['tour_animals'] = Animal::where("is_active", 1)->limit(1000)->get()->toTree();
        $model['tour_category'] = TourCategory::where('status', 'publish')->with(['translation'])->get()->toTree();
        $model['style'] = $model['style'] ?? "";
        $model['list_slider'] = $model['list_slider'] ?? "";
        $model['modelBlock'] = $model;
        return $this->view('Template::frontend.blocks.form-search-all-service.index', $model);
    }

    public function contentAPI($model = []){
        if (!empty($model['bg_image'])) {
            $model['bg_image_url'] = FileHelper::url($model['bg_image'], 'full');
        }
        return $model;
    }
}
