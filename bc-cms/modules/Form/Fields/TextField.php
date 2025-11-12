<?php

namespace Modules\Form\Fields;

class TextField extends BaseField {
    public function getName()
    {
        return __("Input");
    }
    public function render()
    {
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        return <<<HTML
<div class="form-group">
    <label for="{$this->name}">{$this->label}</label>
    <input type="text" name="{$this->name}" id="{$this->name}" {$attr} />
</div>
HTML;
    }  
}
