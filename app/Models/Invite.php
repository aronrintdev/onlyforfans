<?php

namespace App\Models;

use DB;
use App\SluggableTraits;
use App\Interfaces\Guidable;

class Invite extends BaseModel implements Guidable
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['custom_attributes', 'metadata',];

    public static $vrules = [];

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
        return $this->belongsTo('App\Models\User', 'inviter_id');
    }

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'custom_attributes' => 'array',
        'metadata' => 'array',
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
        $vault = DB::transaction(function () use($name, &$owner) {
            $v = Vault::create([
                'name' => $name,
                'user_id' => $owner->id,
            ]);
            $vf = VaultFolder::create([
                'parent_id' => null,
                'vault_id' => $v->id,
                'fname' => 'Root',
            ]);
            $v->refresh();
            return $v;
        });
        return $vault;
    }
     */
}
