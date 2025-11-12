<?php
namespace Modules\Form\Traits;

trait HasFormFeatures
{
    /**
     * Get fields flatten from a field
     * eg:
     *      [
     *          'type' => 'step',
     *          'children' => [
     *              [
     *                  'type' => 'text',
     *                  'id' => 'name',
     *              ],
     *          ],
     *      ],
     *  
     */
    public function getFieldsFlatten($field, $value = [])
    {
        $fields = [];
        if(empty($field['children'])){
            return [];
        }
        foreach ($field['children'] as $item) {

            // allow to apply value_text to each item
            $this->applyValueText($item, $value);

            if ($item['type'] == 'step') {
                $fields = array_merge($fields, $this->getFieldsFlatten($item, $value));
            } else {
                $fields[] = $item;
            }
        }
        return $fields;
    }

    /**
     * Get fields flatten from array (field array, normaly a form)
     * eg: [
     *      [
     *          'type' => 'step',
     *          'children' => [
     *              [
     *                  'type' => 'text',
     *                  'id' => 'name',
     *              ],
     *          ],
     *      ],
     *  ]
     */
    public function getFieldsFlattenFromArray($array, $value = [])
    {
        $fields = [];
        foreach ($array as $item) {
            $this->applyValueText($item, $value);
            $fields[] = $item;
            $fields = array_merge($fields, $this->getFieldsFlatten($item, $value));
        }
        return $fields;
    }

    /**
     * Find field in form
     */
    protected function findFieldInForm($form, $id)
    {
        $fields = [];
        foreach ($form as $field) {
            $fields[] = $field;
            $fields = array_merge($fields, $this->getFieldsFlatten($field));
        }
        return collect($fields)->where('id', $id)->first();
    }

    /**
     * Apply value text to field
     */
    public function applyValueText(&$field, $value)
    {
        $fieldValue = isset($value[$field['id']]) ? $value[$field['id']] : null;
        if($fieldValue !== null){
            switch ($field['type']) {
                case 'select':
                case 'checkbox':
                    $options = $this->getDataSource($field);
                    $field['value_text'] = collect($options)->where('value', $fieldValue)->first()['label'] ?? '';
                    break;
                default:
                $field['value_text'] = $fieldValue;
                    break;
            }

            // with file_picker, value is an json string 
            if($field['type'] == 'file_picker'){
                $fieldValue = json_decode($fieldValue, true);
            }
            // Always apply value to field
            $field['value'] = $fieldValue;
        }
        return $field;
    }

    /**
     * Get data source for field
     */
    public function getDataSource($field)
    {
        $options = $field['options'] ?? [];
        if(empty($field['data_source'])){
            return $options;
        }
        switch ($field['data_source']) {
            case 'country':
                $options = get_country_lists();
                $options = array_map(function ($option, $key) {
                    return [
                        'value' => $key,
                        'label' => $option,
                    ];
                }, $options, array_keys($options));
                break;
        }
        return $options;
    }
}