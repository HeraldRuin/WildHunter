<?php
namespace Modules\Agency\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Property\Models\Property;
use Modules\Agency\Models\Agency;

class BravoContactObject extends BaseModel
{
    use SoftDeletes;
    protected $table = 'bc_contact_object';
    protected $fillable = [
        'name',
        'email',
        'message',
        'phone',
        'object_id',
        'object_model',
        'vendor_id'
    ];

    public function agency() {
        return $this->belongsTo(Agency::class, 'object_id');
    }

    public function property() {
        return $this->belongsTo(Property::class, 'object_id');
    }

//    protected $cleanFields = ['message'];
}
