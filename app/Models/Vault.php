<?php
namespace App\Models;

use DB;
use App\Interfaces\Ownable;
use App\Interfaces\Guidable;
use App\Models\Traits\SluggableTraits;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vault extends BaseModel implements Guidable, Ownable
{
    use UsesUuid, HasFactory, OwnableFunctions, Sluggable, SluggableTraits;

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
                'vfname' => 'Home',
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
        return $this->belongsTo(User::class);
    }
    public function vaultfolders()
    {
        return $this->hasMany(Vaultfolder::class);
    }

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
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

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => ['vname'],
        ]];
    }

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
            case 'meta':
            case 'cattrs':
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

    public function isRoot() : bool
    {
        return is_null($this->parent_id);
    }

    public function getRootFolder() : ?Vaultfolder
    {
        return $this->vaultfolders()->whereNull('parent_id')->first();
    }

}
