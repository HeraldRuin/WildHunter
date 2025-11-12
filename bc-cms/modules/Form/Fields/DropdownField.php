<?php

namespace Modules\Form\Fields;

class DropdownField extends BaseField {
    public function getName()
    {
        return __("Dropdown");
    }

    public function render()
    {
        $options = $this->options['options'] ?? [];
        $selected = $this->options['selected'] ?? null;
        $attr = $this->attributesToHtml($this->options['attr'] ?? []);
        $placeholder = $this->options['placeholder'] ?? 'Select an option';
        
        $html = "<div class='form-group'>";
        $html .= "<label for='{$this->name}'>{$this->label}</label>";
        $html .= "<select name='{$this->name}' id='{$this->name}' {$attr}>";
        $html .= "<option value=''>{$placeholder}</option>";
        
        foreach ($options as $value => $label) {
            $selectedAttr = $selected == $value ? 'selected' : '';
            $html .= "<option value='{$value}' {$selectedAttr}>{$label}</option>";
        }
        
        $html .= "</select></div>";
        return $html;
    }
} 