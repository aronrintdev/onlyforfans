<?php

namespace App\Http\Requests;

use App\BlockedProfile;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BlockedProfileRequest
 */
class BlockedProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return BlockedProfile::$rules;
    }
    
    public function messages()
    {
        return [
            'ip_address.*' => 'Please enter either IP Address or Country.'
        ];
    }
}
