<?php

namespace Modules\Form\Fields;

class DateField extends BaseField {
    public function getName()
    {
        return __("Date");
    }

    public function render()
    {
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        $value = $this->options['value'] ?? '';
        return <<<HTML
<div class="form-group">
    <label for="{$this->name}">{$this->label}</label>
    <input type="date" name="{$this->name}" id="{$this->name}" value="{$value}" {$attr} />
</div>
HTML;
    }
} 