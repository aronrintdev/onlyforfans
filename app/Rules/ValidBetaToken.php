<?php

namespace App\Rules;

use App\Models\Token;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Validation\Rule;

class ValidBetaToken implements Rule
{

    public $value;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $this->value = $value;
        if (Token::check($value, Config::get('auth.beta.token_name'))) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "{$this->value} is not a valid Beta token.";
    }
}
