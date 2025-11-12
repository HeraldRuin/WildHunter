<?php

namespace Modules\Form\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatus;

class FormSubmissionField extends BaseModel
{
    use SoftDeletes;
    use HasStatus;

    protected $table = 'bc_form_submission_fields';
    
    protected $casts = [
        'metadata' => 'array',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class);
    }
}

