<?php

namespace Modules\Form\Fields;

class TextareaField extends BaseField {
    public function getName()
    {
        return __("Textarea");
    }

    public function render()
    {
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        $value = $this->options['value'] ?? '';
        return <<<HTML
<div class="form-group">
    <label for="{$this->name}">{$this->label}</label>
    <textarea name="{$this->name}" id="{$this->name}" {$attr}>{$value}</textarea>
</div>
HTML;
    }
} 