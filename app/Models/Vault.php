<?php

namespace App\Models;

use DB;
use App\Models\Traits\SluggableTraits;
use App\Interfaces\Ownable;
//use App\Interfaces\Nameable;
use App\Interfaces\Guidable;
use App\Interfaces\ShortUuid;
use App\Interfaces\Sluggable;
use App\Models\Traits\UsesShortUuid;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vault extends BaseModel implements Guidable, Sluggable, Ownable
{
    use UsesUuid;
    use SluggableTraits;
    use HasFactory;
    use OwnableFunctions;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public static $vrules = [];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->is_primary = true; // this one - created at boot - is the primary
        });

        static::created(function ($model) {
            // rootFolder
            Vaultfolder::create([
                'vfname' => 'Root',
                'vault_id' => $model->id,
                'parent_id' => null,
                'user_id' => $model->user_id,
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

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function vaultfolders()
    {
        return $this->hasMany('App\Models\Vaultfolder');
    }

    public function getRootFolder()
    {
        return $this->vaultfolders()->whereNull('parent_id')->first();
    }

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'custom_attributes' => 'array',
        'metadata' => 'array',
    ];

    //--------------------------------------------
    // Scopes
    //--------------------------------------------

    /**
     * get the primary vault for a user
     */
    public function scopePrimary($query, User $user)
    {
        return $query->where('is_primary', 1)->where('user_id', $user->id);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Implement Sluggable Interface ---

    public function sluggableFields(): array
    {
        return [ 'vname' ];
    }

    // %%% --- Overrides in Model Traits (via BaseModel) ---

    public static function _renderFieldKey(string $key): string
    {
        $key = trim($key);
        switch ($key) {
            default:
                $key =  parent::_renderFieldKey($key);
        }
        return $key;
    }

    public function renderField(string $field): ?string
    {
        $key = trim($field);
        switch ($key) {
                /*
            case 'metadata':
            case 'custom_attributes':
                return json_encode($this->{$key});
             */
            default:
                return parent::renderField($field);
        }
    }

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->vname;
    }

    // %%% --- Other ---

    // %FIXME: move to observer/boot 'create'
    public static function doCreate(string $vname, User $owner): Vault
    {
        $vault = DB::transaction(function () use ($vname, &$owner) {
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