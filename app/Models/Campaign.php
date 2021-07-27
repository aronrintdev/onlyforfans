<?php
namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Interfaces\UuidId;
use App\Interfaces\Ownable;

use App\Models\Traits\OwnableTraits;

use App\Models\Traits\UsesUuid;

class Campaign extends Model implements UuidId, Ownable
{
    use UsesUuid, OwnableTraits;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function creator() {
        return $this->belongsTo(User::class);
    }

    public function getPrimaryOwner(): User {
        return $this->creator;
    }

    public function getOwner(): ?Collection {
        return new Collection([ $this->getPrimaryOwner() ]);
    }

}
