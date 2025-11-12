<?php

namespace Modules\Form\Admin;

use App\BaseComponent;
use Modules\Form\Models\Form;
use Illuminate\Support\Facades\Auth;
use Modules\Form\FormBuilder;

class FormPage extends BaseComponent
{
    public $form;

    public function mount($id)
    {
        $this->checkPermission('form_edit');

        $form = app(Form::class);
        if(!$this->hasPermission('form_manage_others')){
            $form->where('author_id', Auth::user()->id);
        }
        $this->form = $form->find($id);
        if(!$this->form){

            // create new form
            // TODO: remove this after testing
            $this->form = $form->create([
                'id' => $id,
                'author_id' => Auth::user()->id,
                'name' => 'New Form',
                'slug' => 'new-form',
                'content' => 'New Form Content',
                'status' => 'publish',
            ]);

            //return redirect()->route('form.admin.index')->with('error', 'Form not found');
        }
    }

    public function render()
    {
        $fieldGroups = $this->fieldGroups();
        $fieldTypes = $this->fieldTypes();
        $data = [
            'page_title' => 'Edit Form',
            'form' => $this->form,
            'main_content_class' => 'p-0',
            'fieldGroups' => $fieldGroups,
            'fieldTypes' => $fieldTypes,
        ];
        return view('Form::admin.edit', $data)->extends('Layout::admin.app',$data);
    }


    public function store()
    {
        dd('store');
    }

    #[Computed]
    public function fieldGroups()
    {
        $formBuilder = app(FormBuilder::class);
        $types = $formBuilder->getTypes();
        $groups = $formBuilder->getGroups();
        foreach($groups as $group => $groupData){
            $groups[$group]['types'] = collect($types)->filter(function($type) use ($group){
                return $type['group'] == $group;
            })->map(function($type, $key){
                $classInstance = app($type['class']);
                return [
                    'type' => $key,
                    'name' => $classInstance->getName(),
                    'icon' => $classInstance->getIcon(),
                    'position' => $type['position'],
                    'group' => $type['group'],
                    'settings' => $classInstance->getSettings(),
                ];
            })->sortBy('position')->values();
        }
        return $groups;
    }

    #[Computed]
    public function fieldTypes()
    {
        return collect($this->fieldGroups())->map(function($group){
            return $group['types'];
        })->flatten(1)->keyBy('type')->all();
    }
}
