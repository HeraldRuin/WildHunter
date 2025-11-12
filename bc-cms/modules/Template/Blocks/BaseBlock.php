<?php

namespace Modules\Template\Blocks;

use Illuminate\Support\Facades\View;
use Livewire\Component;

class BaseBlock extends Component
{
    public $id;
    public $options = [];

    public $__nodeId = '';// For Render

    public $__isPreview = false;

    static $__tree = []; // For render children

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getOption($k, $default = false)
    {
        if (empty($this->options)) {
            $this->options = $this->getOptions();
        }
        return $this->options[$k] ?? $default;
    }

    public function getOptions()
    {
        return [];
    }

    public function view($view, $data = null)
    {
        if (View::exists($view)) {

            // Support preview
            if ($this->__isPreview) {
                return view('Template::frontend.preview-layout', [
                    ...($data ?? []),
                    "__view" => $view,
                ]);
            }
            return view($view, $data);
        }

        return view("Layout::livewire.fallback", $data);
    }

    public function children()
    {
        $ids = BaseBlock::$__tree[$this->__nodeId]['nodes'] ?? [];

        if (empty($ids)) return [];

        $res = [];
        foreach ($ids as $id) {
            if (isset(BaseBlock::$__tree[$id])) {
                $res[$id] = BaseBlock::$__tree[$id];

                // add some extra fields to block model if needed
                $res[$id]['model'] = $res[$id]['model'] ?? []; //  Make sure model is an array

                // add __nodeId
                $res[$id]['model']['__nodeId'] = $id;

                // Add flag to children
                if ($this->__isPreview) {
                    $res[$id]['model']['__isPreview'] = true;
                }

            }
        }

        return $res;
    }

    public function render()
    {
        // default is empty
        return <<<'HTML'
        <div></div>
        HTML;
    }

    public function preview($__tree)
    {
        BaseBlock::$__tree = $__tree;
    }
}
