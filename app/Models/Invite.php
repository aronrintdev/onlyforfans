<?php

namespace App\Models;

use DB;
use App\Interfaces\Guidable;
use App\Models\Traits\UsesUuid;
use App\Models\Traits\SluggableTraits;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invite extends BaseModel implements Guidable
{
    use HasFactory, UsesUuid, Sluggable, SluggableTraits;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['cattrs', 'meta',];

    public static $vrules = [];

    public function sluggable(): array
    {
        return ['slug' => [
            'source' => ['sluggableContent'],
        ]];
    }

    public function getSluggableContentAttribute(): string
    {
        return 'Invite from ' . $this->inviter->username;
    }


    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->token = str_random();
        });
    }

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function getJoinLinkAttribute($value)
    {
        return route('auth.register', ['token' => $this->token]);
    }

    //--------------------------------------------
    // Methods
    //--------------------------------------------

    // %%% --- Nameable Interface Overrides (via BaseModel) ---

    public function renderName(): string
    {
        return $this->id;
    }

    // %%% --- Other ---

    // %FIXME: move to observer/boot 'create'
    /*
    public static function doCreate(string $name, User $owner) : Vault {
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
     */
}
