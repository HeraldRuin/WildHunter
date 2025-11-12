<?php
namespace Modules\Vendor\Blocks;

use Modules\Media\Helpers\FileHelper;
use Modules\Template\Blocks\BaseBlock;

class VendorRegisterForm extends BaseBlock
{
    public $title;
    public $desc;
    public $youtube;
    public $bg_image;
    function getOptions()
    {
        return ([
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
        ]);
    }

    public function getTitle()
    {
        return __('Vendor Register Form');
    }

    public function render()
    {
        $model = [
            'title' => $this->title,
            'desc' => $this->desc,
            'youtube' => $this->youtube,
            'bg_image' => $this->bg_image,
        ];
        return $this->view('Vendor::frontend.blocks.form-register.index', $model);
    }

    public function contentAPI($model = []){
        if (!empty($model['bg_image'])) {
            $model['bg_image_url'] = FileHelper::url($model['bg_image'], 'full');
        }
        return $model;
    }
}
