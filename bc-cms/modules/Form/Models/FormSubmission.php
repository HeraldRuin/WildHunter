<?php

namespace Modules\Form\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatus;

class FormSubmission extends BaseModel
{
    use SoftDeletes;
    use HasStatus;

    protected $table = 'bc_form_submissions';
    
    protected $casts = [
        'metadata' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function fields()
    {
        return $this->hasMany(FormSubmissionField::class);
    }
}

