<?php

namespace App\Rules;

use App\UsernameRule;
use Illuminate\Contracts\Validation\Rule;
use Log;

class ValidUsername implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private $message = 'Invalid Username';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        Log::debug('Checking valid_username');
        $rule = UsernameRule::check($value);
        if ($rule) {
            Log::debug('valid_username: caught rule');
            $this->message = $rule->explanation;
            return false;
        }
        Log::debug('valid_username: username ok');
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

}
