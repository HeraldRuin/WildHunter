<?php
namespace Modules\Template\Blocks;

use Modules\Media\Helpers\FileHelper;
use Modules\Template\Blocks\BaseBlock;

class ClientFeedBack extends BaseBlock
{
    public $image_id;
    public $list_item;

    public function getTitle()
    {
        return __('Client Feedback');
    }

    public function getOptions()
    {
        return [
            'settings' => [
                [
                    'id'    => 'image_id',
                    'type'  => 'uploader',
                    'label' => __('Featured Image')
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
                            'label'     => __('Title')
                        ],
                        [
                            'id'        => 'sub_title',
                            'type'      => 'input',
                            'inputType' => 'text',
                            'label'     => __('Sub Title')
                        ],
                        [
                            'id'    => 'desc',
                            'type'  => 'textArea',
                            'label' => __('Desc')
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
            'image_id' => $this->image_id,
            'list_item' => $this->list_item,
        ];
        if(!empty($model['image_id'])){
            $model['image_url'] = get_file_url($model['image_id'] , 'full');
        }
        return $this->view('Template::frontend.blocks.client-feedback.index', $model);
    }

    public function contentAPI($model = []){
        if(!empty($model['image_id'])){
            $model['image_url'] = get_file_url($model['image_id'] , 'full');
        }
        return $model;
    }
}
