<?php
namespace App;

use App\SluggableTraits;
use App\Interfaces\Guidable;
//use App\Interfaces\Nameable;
use App\Interfaces\Sluggable;

class Vault extends BaseModel implements Guidable, Sluggable
{
    use SluggableTraits;

    protected $guarded = ['id','created_at','updated_at'];

    public static $vrules = [
    ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function user() {
        return $this->belongsTo('App\User');
    }
    public function vaultfolders() {
        return $this->belongsTo('App\Vaultfolder');
    }

    //--------------------------------------------
    // Accessors/Mutators
    //--------------------------------------------

    protected $casts = [
        'meta' => 'array',
        'cattrs' => 'array',
    ];

    /*
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
     */

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields() : array {
        return ['vname'];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key) : string
    {
        $key = trim($key);
        switch ($key) {
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field) : ?string
    {
        $key = trim($field);
        switch ($key) {
            /*
            case 'meta':
            case 'cattrs':
                return json_encode($this->{$key});
             */
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName() : string {
        return $this->vname;
    }

    // %%% --- Other ---

}
