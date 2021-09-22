<?php
namespace App\Models;

use Money\Money;
use App\Interfaces\UuidId;

use App\Interfaces\Ownable;
use Illuminate\Support\Carbon;

use App\Models\Traits\UsesUuid;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\OwnableTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $id
 * @property string $creator_id
 * @property bool $active
 * @property bool $has_new
 * @property bool $has_expired
 * @property int $subscriber_count
 * @property int $offer_days
 * @property int $discount_percent
 * @property string $message
 * @property Carbon|null $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property bool $isSubscriberCountUnlimited
 *
 * @property User $creator
 * @property Collection $subscriptions
 *
 * @method Builder forTimeline(Timeline $timeline)
 * @method Builder isActive()
 * @method Builder hasExpired()
 * @method Builder hasNotExpired()
 * @method Builder hasRemaining()
 *
 * @package App\Models
 */
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

    protected $dates = [
        'expires_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->offer_days > 0) {
                $model->expires_at = Carbon::now()->addDays($model->offer_days);
            }
            if ($model->subscriber_count > 0) {
                $model->remaining_count = $model->subscriber_count;
            }
        });
    }

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

    /**
     * Subscriptions that used this campaign
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ================= Scopes ==========================

    /**
     * Campaigns for a specific timeline
     */
    public function scopeForTimeline($query, Timeline $timeline)
    {
        return $query->where('creator_id', $timeline->user_id);
    }

    /**
     * The campaign is active
     */
    public function scopeIsActive($query)
    {
        return $query->hasNotExpired()->hasRemaining();
    }

    public function scopeHasExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    public function scopeHasNotExpired($query)
    {
        return $query->where( function($query) {
            $query->where('expires_at', '>', Carbon::now())
                ->orWhereNull('expires_at');
        });
    }

    public function scopeHasRemaining($query)
    {
        return $query->where(function($query) {
            $query->where('remaining_count', '>', 0)
                ->orWhereNull('remaining_count');
        });
    }

    // ================= Other ==================

    public function getPrimaryOwner(): User {
        return $this->creator;
    }

    public function getOwner(): ?Collection {
        return new Collection([ $this->getPrimaryOwner() ]);
    }

    /**
     * Get the discounted price from an original price
     */
    public function getDiscountPrice(Money $originalPrice)
    {
        return $originalPrice->multiply((100 - $this->discount_percent) / 100);
    }

    /**
     * DB safely increment remaining count
     */
    public function incrementRemaining() {
        if (isset($this->remaining_count)) {
            self::where('id', $this->id)->update(['remaining_count' => DB::raw('remaining_count + 1')]);
        }
        return $this;
    }

    /**
     * DB safely decrement remaining count
     */
    public function decrementRemaining()
    {
        if (isset($this->remaining_count)) {
            self::where('id', $this->id)->update(['remaining_count' => DB::raw('remaining_count - 1')]);
        }
        return $this;
    }

    public function isValid(): bool
    {
        if ($this->active) {
            if ($this->hasRemaining() && !$this->isExpired()) {
                return true;
            }
        }
        return false;
    }

    public function hasRemaining(): bool
    {
        return $this->remaining_count === null || $this->remaining_count > 0;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isBefore(Carbon::now());
    }

    // De-active all existing active campaigns for the user
    public static function deactivateAll($user) {
        Campaign::where('creator_id', $user->id)
            ->where('active', true)
            ->update(['active' => false]);
    }

}
