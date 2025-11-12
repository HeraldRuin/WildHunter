<?php
namespace Modules\Template\Blocks;

use Modules\Media\Helpers\FileHelper;
use Modules\Template\Blocks\BaseBlock;

class VideoPlayer extends BaseBlock
{
    public $title;
    public $youtube;
    public $bg_image;


    public function getTitle()
    {
        return __('Video Player');
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
                    'id'        => 'youtube',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Youtube link')
                ],
                [
                    'id'    => 'bg_image',
                    'type'  => 'uploader',
                    'label' => __('Background Image Uploader')
                ],
            ],
            'category'=>__("Other Block")
        ];
    }

    public function render()
    {
        $model = [
            'id' => time(),
            'title' => $this->title,
            'youtube' => $this->youtube,
            'bg_image' => $this->bg_image,
        ];
        return $this->view('Template::frontend.blocks.video-player', $model);
    }

    public function contentAPI($model = []){
        if (!empty($model['bg_image'])) {
            $model['bg_image_url'] = FileHelper::url($model['bg_image'], 'full');
        }
        return $model;
    }
}
