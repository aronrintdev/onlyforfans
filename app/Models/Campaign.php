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

    // ================= Accessors/Mutators | Casts ==================

    public function getTargetedCustomerGroupAttribute($value)
    {
        if ( $this->has_new && $this->has_expired ) {
            return 'new-and-expired';
        } else if ( $this->has_new ) {
            return 'new';
        } else if ( $this->has_expired ) {
            return 'expired';
        } else {
            return null; // invalid state (?)
        }
    }

    // ================= Relationships ==================

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // ================= Other ==================

    public function getPrimaryOwner(): User {
        return $this->creator;
    }

    public function getOwner(): ?Collection {
        return new Collection([ $this->getPrimaryOwner() ]);
    }

}
