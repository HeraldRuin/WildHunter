<?php

namespace Modules\Visa\Helpers;


class VisaFormSettings
{
    protected $__form;
    protected $__form_flat;

    public function getForm()
    {
        if ($this->__form) {
            return $this->__form;
        }
        $this->__form = json_decode(file_get_contents(base_path('modules/Visa/Configs/form.json')), true);
        return $this->__form;
    }

    public function getFormFlat()
    {
        if ($this->__form_flat) {
            return $this->__form_flat;
        }
        $flat = [];
        $tree = $this->getForm();

        function walk(array $node, array &$result): string {
            $id = $node['id'];
            $children = $node['children'] ?? [];
    
            // Save a list of children IDs (if any)
            $nodeEntry = $node;
            unset($nodeEntry['children']);
    
            if (!empty($children)) {
                $nodeEntry['nodes'] = array_map(fn($child) => $child['id'], $children);
            }
    
            $result[$id] = $nodeEntry;
    
            // Recurse
            foreach ($children as $child) {
                walk($child, $result);
            }
    
            return $id;
        }

        walk($tree, $flat);

        $this->__form_flat = $flat;

        return $this->__form_flat;
    }
}
