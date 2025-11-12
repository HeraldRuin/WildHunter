<?php
namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\BaseModel;
use App\Traits\HasStatus;

class VisaType extends BaseModel
{
    use SoftDeletes;
    use HasStatus;
    protected $table = 'bc_visa_types';

    protected $translation_class = VisaTypeTranslation::class;


    public function scopeSearch($query){
        return $query->where('status', 'publish');
    }
}