<?php
namespace Modules\Contact\Blocks;

use Modules\Template\Blocks\BaseBlock;

class Contact extends BaseBlock
{
    public $class;

    function getOptions()
    {
        return ([
            'settings' => [
                [
                    'id'        => 'class',
                    'type'      => 'input',
                    'inputType' => 'text',
                    'label'     => __('Class Block')
                ],
            ],
            'category'=>__("Other Block")
        ]);
    }

    public function getTitle()
    {
        return __('Contact Block');
    }

    public function render()
    {
        $model = [
            'class' => $this->class
        ];
        return $this->view('Contact::frontend.blocks.contact.index', $model);
    }
}
