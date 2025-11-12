<?php

namespace Modules\Form;

class FormBuilder {
    public static $types = [];
    public static $groups = [];
    public static $providers = [];

    public $fields = [];

    public static function registerType($type, $class, $group = 'default', $position = 0) {
        self::$types[$type] = [
            'class' => $class,
            'group' => $group,
            'position' => $position,
        ];
    }

    public static function registerGroup($group, $name, $position = 0) {
        self::$groups[$group] = [
            'name' => $name,
            'position' => $position,
        ];
    }

    public static function create() {
        return new self();
    }

    public static function getTypes() {
        return self::$types;
    }

    public static function getGroups() {
        return self::$groups;
    }

    public static function fromJson($json) {
        $data = json_decode($json, true);
        $form = new self();
        $form->fields = $data['fields'];
        return $form;
    }

    public function render()
    {
        return collect($this->fields)
            ->map(fn(BaseField $field) => $field->render())
            ->implode("\n");
    }

    public static function registerProvider($provider, $callable)
    {
        self::$providers[$provider] = $callable;
    }

    public static function getProvider($provider)
    {
        return self::$providers[$provider] ?? null;
    }
}