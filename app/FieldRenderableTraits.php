<?php
namespace App\Models;

use Illuminate\Support\Str;

trait FieldRenderableTraits {

    // common baseline code 
    public static function _renderFieldKey(string $key) : string
    {
        $key = trim($key);
        switch ($key) {
            case 'guid':
                $key = 'GUID';
                break;
            case 'id':
                $key = 'PKID';
                break;
            case 'created_at':
                $key = 'Created';
                break;
            case 'updated_at':
                $key = 'Updated';
                break;
            default:
                // try to capture 'boolean' fields that start with 'is'
                $key = ucwords(preg_replace('/^is_/', 'Is ', $key));
                //$key = ucwords($key);
        }
        return $key;
    }

    // child classes can override, but impl should call parent
    public function renderFieldKey(string $key) : string
    {
        return static::_renderFieldKey($key);  // %NOTE: late static binding
    }


    // child classes can override, but impl should call parent
    // $NOTE: references $this
    public function renderField(string $field) : ?string
    {
        if ( empty($field) || !property_exists($this, $field) ) {
            return '';
        }

        $key = trim($field);
        $strIn = $this->{field};

        switch ($key) {
            case 'guid':
                return Str::of($strIn)->upper();
            case 'created_at':
            case 'updated_at':
            case 'deleted_at':
                // laravel 7 does this for us now?
                //return date('m/d/Y G:i', strtotime($strIn)) // number format, include time
            default:
                return $strIn;
        }
    }

}
