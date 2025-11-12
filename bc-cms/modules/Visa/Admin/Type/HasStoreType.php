<?php

namespace Modules\Visa\Admin\Type;

use Modules\Visa\Models\VisaType;

trait HasStoreType
{
    public function store()
    {
        $this->checkPermission('visa_manage_others');
        $data = $this->validate();

        // Check row from id
        if($this->id){
            $visaType = app(VisaType::class)->find($this->id);
            if(!$visaType){
                $this->sendError(__('Visa Type not found'));
                return;
            }
        }else{
            $visaType = app()->make(VisaType::class);
        }
        $visaType->fillByAttr(['name','status'], $data);

        $visaType->saveOriginOrTranslation($this->lang);
        
        $this->sendSuccess(__('Visa Type saved'));

    }
}
