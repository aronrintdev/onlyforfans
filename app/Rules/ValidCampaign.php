<?php

namespace App\Rules;

use App\Models\Campaign;
use Illuminate\Contracts\Validation\Rule;

class ValidCampaign implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_string($value)) {
            $campaign = Campaign::find($value);
            if (!isset($campaign)) {
                return false;
            }
        } else if($value instanceof Campaign) {
            $campaign = $value;
        } else {
            // Not known item
            return false;
        }
        return $campaign->isValid();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Campaign is not valid.';
    }
}
