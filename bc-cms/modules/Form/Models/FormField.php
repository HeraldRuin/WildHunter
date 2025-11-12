<?php

namespace Modules\Form\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatus;

class FormField extends BaseModel
{
    use SoftDeletes;
    use HasStatus;

    protected $table = 'bc_form_fields';
    
    protected $casts = [
        'metadata' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function parentField()
    {
        return $this->belongsTo(FormField::class, 'parent_id');
    }
}

