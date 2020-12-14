<?php
namespace App;

use App\SluggableTraits;
use App\Interfaces\Guidable;
//use App\Interfaces\Nameable;
use App\Interfaces\Sluggable;

class Vaultfolder extends BaseModel implements Guidable, Sluggable
{
    use SluggableTraits;

    protected $guarded = ['id','created_at','updated_at'];

    public static $vrules = [
    ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function vault() {
        return $this->belongsTo('App\Vault');
    }
    public function mediafiles() {
        return $this->morphMany('App\Mediafile', 'resource');
    }
    public function vfparent() {
        return $this->belongsTo('App\Vaultfolder', 'parent_id');
    }
    public function vfchildren() {
        return $this->hasMany('App\Vaultfolder', 'parent_id');
    }

    //--------------------------------------------
    // Accessors/Mutators
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields() : array {
        return ['vfname'];
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
        return $this->vfname;
    }

    // %%% --- Other ---

}
