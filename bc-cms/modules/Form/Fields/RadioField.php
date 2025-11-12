<?php

namespace Modules\Form\Fields;

class RadioField extends BaseField {
    public function getName()
    {
        return __("Radio");
    }

    public function render()
    {
        $options = $this->options['options'] ?? [];
        $selected = $this->options['selected'] ?? null;
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        
        $html = "<div class='form-group'>";
        $html .= "<label>{$this->label}</label>";
        $html .= "<div class='radio-group'>";
        
        foreach ($options as $value => $label) {
            $checked = $selected == $value ? 'checked' : '';
            $html .= <<<HTML
<div class="radio-item">
    <input type="radio" name="{$this->name}" id="{$this->name}_{$value}" value="{$value}" {$checked} {$attr} />
    <label for="{$this->name}_{$value}">{$label}</label>
</div>
HTML;
        }
        
        $html .= "</div></div>";
        return $html;
    }
} 