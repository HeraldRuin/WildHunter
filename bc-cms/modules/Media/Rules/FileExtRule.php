<?php

namespace Modules\Media\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileExtRule implements ValidationRule
{
    /**
     * @var array
     */
    public $acceptedExt;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $acceptedExt)
    {
        //
        $this->acceptedExt = $acceptedExt;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("File type invalid");
    }

     /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value->getClientOriginalExtension(), $this->acceptedExt)) {
            $fail($this->message());
        }
    }
}
