<?php

namespace App\Models;

use Exception;
use App\Interfaces\Ownable;
use App\Interfaces\UuidId;
use App\Models\Traits\UsesUuid;
use App\Traits\OwnableFunctions;
use Illuminate\Support\Collection;

class Notes extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableFunctions;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function getOwner(): ?Collection
    {
        return new Collection([$this->user]);
    }
}
