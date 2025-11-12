<?php

namespace Modules\Template\Blocks;

class RootBlock extends BaseBlock
{

    public function getTitle()
    {
        return '';
    }


    public function render()
    {
        $data = [
            'children'=>$this->children()
        ];
        return $this->view('Template::frontend.blocks.root', $data);
    }
}
