<?php

namespace Modules\Media\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageSizeRule implements ValidationRule
{
    /**
     * @var int
     */
    public $maxWidth;
    /**
     * @var int
     */
    public $maxHeight;

    protected $lastError;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $maxWidth,int $maxHeight)
    {
        //
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedExtsImage = [
            'jpg',
            'jpeg',
            'bmp',
            'png',
            'gif',
        ];
        if(!in_array(strtolower($value->getClientOriginalExtension()), $allowedExtsImage)) {
            // not image
            $fail($this->message());
        }
        $imagedata = getimagesize($value->getPathname());
        if (empty($imagedata)) {
            $this->lastError = __("Can not get image size");
            $fail($this->message());
        }
        if (!empty($this->maxWidth) and $imagedata[0] > $this->maxWidth) {
            $this->lastError = __("Maximum width allowed is: :number", ['number' => $this->maxWidth]);
            $fail($this->message());
        }
        if (!empty($this->maxHeight) and $imagedata[1] > $this->maxHeight) {
            $this->lastError = __("Maximum height allowed is: :number", ['number' => $this->maxHeight]);
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
        return $this->lastError;
    }
}
