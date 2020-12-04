<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

use App\Interfaces\Sluggable;

// common/baseline implementation of interfaces used by Eloquent models
trait ModelTraits {

    // %TODO: TESTME
    public function renderName() : string
    {
        $displayField = self::getDisplayField('renderName');
        return $this->{$displayField};
    }

    // Default implementation - to add filters, overide in child class
    // %TODO: TESTME
    public static function getSelectOptions($includeBlank=true, $keyField='id', $filters=[]) : array
    {
        $displayField = self::getDisplayField('renderName');
        $options = $includeBlank ? [''=>''] : [];
        foreach (static::get() as $i => $r) {
            $options[$r->{$keyField}] = $r->{$displayField};
        }
        return $options;
    }

    private static function getDisplayField(string $mname) : string
    {
        if ( in_array('App\Models\Sluggable', class_implements(static::class)) ) {
            $sluggableFields = static::sluggableFields();
            $displayField = $sluggableFields[0];
        } else {
            throw new Exception('BaseModel method '.$mname.'() requires implementation of Sluggable interface, or chilkd class must override this method');
        }
        return $displayField;
    }

    public static function _renderFieldKey(string $key) : string
    {
        return self::base_renderFieldKey($key);
    }

    // child classes can override, but impl should call parent
    public function renderFieldKey(string $key) : string
    {
        return static::_renderFieldKey($key);  // %NOTE: late static binding
    }


    // child classes can override, but impl should call parent
    public function renderField(string $field) : ?string
    {
        $key = trim($field);
        switch ($key) {
            case 'created_at_str':
                return Carbon::parse($this->created_at)->format('m-j-Y');
            case 'guid':
                //return strtoupper($this->{$field});
                return strtoupper(substr($this->{$field},0,8));
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$field});
            case 'created_at':
            case 'updated_at':
            case 'deleted_at':
                //return ViewHelpers::makeNiceDate($this->{$field},1,1); // number format, include time
                //return date('m-d-Y t:i:s', strtotime($this->{$field}));
                //return $d->format('Y.m.d H:i:s');

                //dd( $this->created_at);
                //return $this->created_at->format(Y.m.d);

                return $this->{$field};
            default:
                return $this->{$field};
        }
    }


    // common baseline code
    protected static function base_renderFieldKey(string $key) : string
    {
        $key = trim($key);
        switch ($key) {
            case 'guid':
                $key = 'GUID';
                break;
            case 'id':
                $key = 'PKID';
                break;
            case 'cattrs':
                $key = 'Custom Attrs';
                break;
            case 'meta':
                $key = 'Meta Attrs';
                break;
            case 'created_at_str':
            case 'created_at':
                $key = 'Created';
                break;
            case 'created_at_str':
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
}
