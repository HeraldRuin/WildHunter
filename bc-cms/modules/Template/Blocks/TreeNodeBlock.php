<?php

namespace Modules\Template\Blocks;

use Livewire\Component;

class TreeNodeBlock extends BaseBlock


{
    public $nodeId;

    public function render()
    {
        return $this->view('Template::frontend.tree-node', ['children' => $this->children()]);
    }
}
