<?php

namespace App\Models;

use Exception;
use App\Interfaces\Ownable;
use App\Interfaces\UuidId;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Referral extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableFunctions;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function referral()
    {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function timeline()
    {
        return $this->belongsTo(Timeline::class);
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->user]);
    }
}
