<?php

namespace Modules\Form\Fields;

abstract class BaseField
{
    abstract public function render();
    abstract public function getName();

    // For admin display
    public function getIcon()
    {
        return 'fa fa-pencil';
    }

    protected function attributesToHtml(array $attributes)
    {
        return collect($attributes)->map(fn($v, $k) => $k . '="' . e($v) . '"')->implode(' ');
    }

    // Admin settings to display in form builder
    public function getSettings()
    {
        return [
            'groups'=>[],
            'fields'=>[],
            'sections'=>[],
        ];
    }
}