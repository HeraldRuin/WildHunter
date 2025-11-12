<?php
namespace Modules\Visa\Models;

use App\BaseModel;

class VisaServiceTranslation extends BaseModel
{
    public $table = 'bc_visa_service_translations';

    protected $seo_type = 'visa_translation';

    public $fillable = [
        'title',
        'content'
    ];
}
