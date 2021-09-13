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

    protected $appends = [
        'targeted_customer_group',
        'is_subscriber_count_unlimited',
    ];

    protected $attributes = [
        'active' => true,
    ];

    // ================= Accessors/Mutators | Casts ==================

    // has_new really means "for new subscribers"
    // has_expired really means "for expired subscribers"
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

    public function getIsSubscriberCountUnlimitedAttribute($value)
    {
        return ( empty($this->subscriber_count) || ($this->subscriber_count === -1) );
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

    // De-active all existing active campaigns for the user
    public static function deactivateAll($user) {
        Campaign::where('creator_id', $user->id)
            ->where('active', true)
            ->update(['active' => false]);
    }

}
