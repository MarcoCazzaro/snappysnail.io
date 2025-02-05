<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastFiveImages implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /*
        $modelImagesIds = ($this->data['modelImagesIds'] ?? []);
        if (is_string($modelImagesIds)) {
            $modelImagesIds = explode(',', $modelImagesIds);
        }
        $modelImagesIds = array_filter($modelImagesIds);

        $tempImagesPaths = ($this->data['tempImagesPaths'] ?? []);
        if (is_string($tempImagesPaths)) {
            $tempImagesPaths = explode(',', $tempImagesPaths);
        }
        $tempImagesPaths = array_filter($tempImagesPaths);
        if (count($modelImagesIds) + count($tempImagesPaths) < 5) {
            $fail('Devi inserire almeno 5 foto');
        }
        */
    }
}
