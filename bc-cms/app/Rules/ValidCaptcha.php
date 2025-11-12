<?php

namespace App\Rules;

use App\Helpers\ReCaptchaEngine;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCaptcha implements ValidationRule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value or !ReCaptchaEngine::verify($value)) {
            $fail($this->message());
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Please verify captcha.');
    }
}
