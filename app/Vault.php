<?php
namespace App;

use DB;
use App\SluggableTraits;
use App\Interfaces\Ownable;
//use App\Interfaces\Nameable;
use App\Interfaces\Guidable;
use App\Interfaces\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vault extends BaseModel implements Guidable, Sluggable, Ownable
{
    use SluggableTraits;
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];
    public static $vrules = [ ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();
        
        static::created(function ($model) {
            $rootfolder = Vaultfolder::create([
                'vfname' => 'Root',
                'vault_id' => $model->id,
                'parent_id' => null,
            ]);
        });

        static::deleting(function ($model) {
            foreach ($model->vaultfolders as $o) {
                $o->delete();
            }
        });
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function user() {
        return $this->belongsTo('App\User');
    }
    public function vaultfolders() {
        return $this->hasMany('App\Vaultfolder');
    }

    public function getRootFolder() {
        return $this->vaultfolders()->whereNull('parent_id')->first();
    }

    // %%% --- Implement Ownable Interface ---

    public function getOwner() : ?User {
        return $this->user;
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
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

    // %FIXME: move to observer/boot 'create'
    public static function doCreate(string $vname, User $owner) : Vault {
        $vault = DB::transaction(function () use($vname, &$owner) {
            $v = Vault::create([
                'vname' => $vname,
                'user_id' => $owner->id,
            ]);
            $vf = Vaultfolder::create([
                'parent_id' => null,
                'vault_id' => $v->id,
                'vfname' => 'Root',
            ]);
            $v->refresh();
            return $v;
        });
        return $vault;
    }

}
