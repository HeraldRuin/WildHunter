<?php

namespace Modules\Form\Models;

use App\BaseModel;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Form\Models\FormField;

class Form extends BaseModel
{
    use SoftDeletes;
    use HasStatus;

    protected $table = 'bc_forms';
    
    protected $casts = [
        'metadata' => 'array',
    ];

    protected $fillable = [
        'title',
        'content',
        'status',
        'author_id',
        'create_by',
        'update_by'
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}

