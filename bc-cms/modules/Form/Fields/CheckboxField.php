<?php

namespace Modules\Form\Fields;

class CheckboxField extends BaseField {
    public function getName()
    {
        return __("Checkbox");
    }

    public function render()
    {
        $options = $this->options['options'] ?? [];
        $selected = $this->options['selected'] ?? [];
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        
        $html = "<div class='form-group'>";
        $html .= "<label>{$this->label}</label>";
        $html .= "<div class='checkbox-group'>";
        
        foreach ($options as $value => $label) {
            $checked = in_array($value, (array)$selected) ? 'checked' : '';
            $html .= <<<HTML
<div class="checkbox-item">
    <input type="checkbox" name="{$this->name}[]" id="{$this->name}_{$value}" value="{$value}" {$checked} {$attr} />
    <label for="{$this->name}_{$value}">{$label}</label>
</div>
HTML;
        }
        
        $html .= "</div></div>";
        return $html;
    }
} 