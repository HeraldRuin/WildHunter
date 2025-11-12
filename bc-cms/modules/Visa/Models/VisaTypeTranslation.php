<?php
namespace Modules\Visa\Models;


class VisaTypeTranslation extends VisaType
{
    public $table = 'bc_visa_type_translations';

    public $fillable = [
        'name',
    ];
}
