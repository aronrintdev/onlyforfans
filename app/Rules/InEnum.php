<?php

namespace App\Rules;

use App\Enums\SmartEnum;
use Illuminate\Contracts\Validation\Rule;

class InEnum implements Rule
{

    /**
     * Enum to test against
     * @var SmartEnum
     */
    protected $enum;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(SmartEnum $enum)
    {
        $this->enum = $enum;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->enum->isValidKey($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $enum = class_basename($this->enumClass);
        return ":attribute must be of of type {$enum}.";
    }
}
