<?php
namespace App;

//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Storage;
//use Storage;
//use App\Libs\Utils\ViewHelpers;
//use App\Libs\Image;
use App\SluggableTraits;
use App\Interfaces\Guidable;
//use App\Interfaces\Nameable;
use App\Interfaces\Sluggable;
use App\Enums\MediafileTypeEnum;

class Mediafile extends BaseModel implements Guidable, Sluggable
{
    use SluggableTraits;

    protected $guarded = ['id','created_at','updated_at'];

    public static $vrules = [
    ];

    //--------------------------------------------
    // Polymorhpic Relationships
    //      ~ Organization, Vendor, User
    //--------------------------------------------
    public function resource() {
        return $this->morphTo();
    }

    //--------------------------------------------
    // Accessors/Mutators
    //--------------------------------------------

    public function getMetaAttribute($value) {
        return !empty($value) ? json_decode($value,true) : [];
    }

    public function setMetaAttribute($value) {
        $this->attributes['meta'] = !empty($value) ? json_encode($value) : null;
    }

    public function getCattrsAttribute($value) {
        return !empty($value) ? json_decode($value,true) : [];
    }

    public function setCattrsAttribute($value) {
        $this->attributes['cattrs'] = !empty($value) ? json_encode($value) : null;
    }
/*
    public function getFilenameAttribute() {
        // like "3a889b0d-5194-5105-7c0b-5c789342dc53.png"
        return $this->guid.'.'.$this->orig_ext;
    }
 */

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields() : array
    {
        return ['orig_filename'];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key) : string
    {
        $key = trim($key);
        switch ($key) {
            case 'mftype':
                $key = 'Media File Type';
                break;
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field) : ?string
    {
        $key = trim($field);
        switch ($key) {
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
            case 'mftype':
                return empty($this->mftype) ? 'N/A' : MediafileTypeEnum::render($this->mftype);
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName() : string 
    {
        return $this->orig_filename;
    }

    // %%% --- Other ---

}
