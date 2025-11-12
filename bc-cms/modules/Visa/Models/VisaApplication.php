<?php
namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\BaseModel;
use App\Traits\HasStatus;

class VisaApplication extends BaseModel
{
    use SoftDeletes;
    use HasStatus;
    
    protected $table = 'bc_visa_applications';
}
