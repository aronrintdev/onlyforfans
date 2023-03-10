<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Casts\Money as CastsMoney;
use App\Interfaces\Tippable;
use Laravel\Scout\Searchable;

use App\Interfaces\Reportable;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use App\Models\Traits\FormatMoney;
use Illuminate\Support\Collection;
use App\Enums\VerifyStatusTypeEnum;
use App\Models\Traits\OwnableTraits;
use App\Enums\SubscriptionPeriodEnum;
use App\Models\Financial\Transaction;
use App\Models\Traits\ShareableTraits;
use App\Models\Traits\SluggableTraits;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Traits\SubscribeableTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Money\Money;
use Money\Number;

/**
 * Timeline Model
 *
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $about
 * @property string $avatar_id
 * @property string $cover_id
 * @property bool   $verified
 * @property bool   $is_follow_for_free
 * @property Money $price @deprecated
 * @property string $currency
 * @property array  $cattrs
 * @property array  $meta
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @package App\Models
 */
class Timeline extends Model implements Subscribable, Tippable, Reportable
{
    use SoftDeletes,
        HasFactory,
        OwnableTraits,
        UsesUuid,
        Sluggable,
        SluggableTraits,
        ShareableTraits,
        FormatMoney,
        HasCurrency,
        Searchable,
        SubscribeableTraits;

    //protected $appends = [ ];
    protected $keyType = 'string';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['posts', 'followers']; // %FIXME: why is this ness? timelines.show (route-model binding) loads these by default but should be lazy loading (?) %PSG

    protected $attributes = [
        'is_follow_for_free' => true,
    ];

    protected $casts = [
        'name' => 'string',
        'about' => 'string',
        // 'price' => CastsMoney::class,
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    // public function getAvatarAttribute($value)
    // {
    //     $isNull = is_null($value);
    //     Log::info("avatar attribute: {$value} {$isNull}");
    //     return is_null($value) ? (object)['filepath' => url('/images/default_avatar.svg')] : $value;
    //         // : (object) ['filepath' => url('user/avatar/default-' . $this->gender . '-avatar.png')];
    // }

    // public function getCoverAttribute($value)
    // {
    //     return is_null($value) ? (object)['filepath' => url('/images/locked_post.png')] : $value;
    //         //: (object) ['filepath' => url('user/cover/default-' . $this->gender . '-cover.png')]; // %TODO %FIXME
    // }

    // %FIXME [timelines].verified field should be deprecated (renamed then removed)
    public function getVerifiedAttribute($value) {
        return $this->user->verifyrequest && ($this->user->verifyrequest->vstatus===VerifyStatusTypeEnum::VERIFIED);
    }

    /* ---------------------------------- price --------------------------------- */
    public function getPriceAttribute()
    {
        $price = $this->getOneMonthPrice();
        if ($price) {
            return $price->price;
        }
        return null;
    }
    public function setPriceAttribute($value)
    {
        if ($value instanceof Money || Number::fromString($value)->isInteger()) {
            $this->updateOneMonthPrice($this->castToMoney($value));
        }
    }
    /* -------------------------------------------------------------------------- */

    public function getSubscriptionPricesAttribute($value)
    {
        return SubscriptionPrice::for($this)->active()->get();
    }

    public function toArray()
    {
        $array = parent::toArray();
        // Localize Price
        $array['price_display'] = static::formatMoney($this->price ?? Money::USD(0));
        $array['userstats'] = $this->getUserstats();
        return $array;
    }


    public function sluggable(): array
    {
        return ['slug' => [
            'source' => [ 'name' ],
        ]];
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    // includes subscribers (ie premium + default followers)
    public function followers() {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->withTimestamps();
    }

    /**
     * Active subscribers to this timeline
     * @return MorphToMany
     */
    public function subscribers() {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->where('access_level', 'premium')
            ->withTimestamps();
    }

    public function favorites() {
        return $this->hasMany(Favorite::class, 'favoritable_id');
    }

    public function referrals() {
        return $this->hasMany(Referral::class, 'referral_id');
    }

    /**
     * The subscriptions for this timeline. This includes failed and canceled subscriptions
     *
     * @return MorphToMany
     */
    public function subscriptions() {
        return $this->morphMany(Subscription::class, 'subscribable');
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'resource');
    }

    public function posts() {
        return $this->morphMany(Post::class, 'postable');
    }

    public function stories() {
        return $this->hasMany(Story::class);
    }

    public function storyqueues() {
        return $this->hasMany(Storyqueue::class);
    }

    public function user() { // timeline owner
        return $this->belongsTo(User::class);
    }

    public function avatar() {
        return $this->belongsTo(Mediafile::class, 'avatar_id');
    }

    public function cover() {
        return $this->belongsTo(Mediafile::class, 'cover_id');
    }

    public function prices() {
        return $this->morphMany(SubscriptionPrice::class, 'subscribeable')->active();
    }

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    public function scopeHasPrice($query)
    {
        return $query->has('prices');
    }

    /*
    public function scopeSort($query, $sortBy, $sortDir='desc')
    {
        switch ($sortBy) {
        case 'slug':
        case 'created_at':
            $query->orderBy($sortBy, $sortDir);
            break;
        default:
            $query->latest();
        }
        return $query;
    }
     */

    // %%% --- Implement Purchaseable Interface ---
    #region Purchasable


    #endregion Purchasable

    /* ---------------------------------------------------------------------- */
    /*                               Searchable                               */
    /* ---------------------------------------------------------------------- */
    #region Searchable

    /**
     * Name of the search index associated with this model
     * @return string
     */
    public function searchableAs()
    {
        return "timelines_index";
    }

    /**
     * Get value used to index the model
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->getKey();
    }

    /**
     * Get key name used to index the model
     * @return string
     */
    public function getScoutKeyName()
    {
        return 'id';
    }

    /**
     * What model information gets stored in the search index
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'name'     => $this->name,
            'slug'     => $this->slug,
            'username' => $this->user->username,
            'id'       => $this->getKey(),
        ];
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */

    /* ---------------------------- Subscribable ---------------------------- */
    #region Subscribable

    public function setPrice(
        Money $amount,
        string $period = SubscriptionPeriodEnum::DAILY,
        int $period_interval = 30
    ): SubscriptionPrice
    {
        return SubscriptionPrice::updatePrice($this, $amount, $period, $period_interval);
    }

    public function verifyPrice(
        $amount,
        string $period = SubscriptionPeriodEnum::DAILY,
        int $period_interval = 30
    ): bool
    {
        $amount = $this->asMoney($amount);

        $subscriptionPrice = SubscriptionPrice::activePriceFor($this, $period, $period_interval);

        if (!isset($subscriptionPrice)) {
            // No active subscription price for this period
            return false;
        }

        if ($subscriptionPrice->price->equals($amount)) {
            // Amount is good for this subscription
            return true;
        }

        // See if there are any promotions
        $promotions = Campaign::forTimeline($this)->isActive()->get();

        if ($promotions->count() > 0) {
            foreach ($promotions as $promotion) {
                $discount = $promotion->getDiscountPrice($subscriptionPrice->price);
                if ($discount->equals($amount)) {
                    return true;
                }
            }
        }

        // No active promotions match this price
        return false;
    }

    // added by %PSG 20210914 
    //  ~ %FIXME: see above...this could be useful as well as a standalone method %ERIK
    //  ~ %FIXME: will need to account for 'batches', but this can be the base price (ie monthly, pre-discounts)
    public function getBaseSubPriceInCents() // : Money
    {
        $subscriptionPrice = SubscriptionPrice::oneMonthPrice($this);
        if ($subscriptionPrice) {
            return $subscriptionPrice->price;
        }
        return Money::USD(0);
        // if (isset($this->getUserstats()['subscriptions']) && isset($this->getUserstats()['subscriptions']['price_per_1_months'])) {
        //     $price = $this->asMoney($this->getUserstats()['subscriptions']['price_per_1_months'] * 100);
        // } else {
        //    $price = $this->price;
        // }
        // return $price;
    }

    public function getDescriptionNameString(): string
    {
        return "Timeline of {$this->name}";
    }

    public function getOwnerAccount(string $system, string $currency): Account
    {
        return $this->getOwner()->first()->getEarningsAccount($system, $currency);
    }

    #endregion Subscribable


    // Is the user provided following my timeline (includes either premium or default)
    public function isUserFollowing(User $user): bool
    {
        return $this->followers->contains($user->id);
    }

    // Is the user provided following my timeline (includes either premium or default)
    public function isUserSubscribed(User $user): bool
    {
        return $this->followers->where('pivot.access_level', ShareableAccessLevelEnum::PREMIUM)->contains($user->id);
    }

    // Is timeline owned by a user (alias for `isOwner()`)
    public function isOwnedBy(User $user): bool
    {
        return $this->isOwner($user);
    }

    // %%% --- Implement Ownable Interface ---

    public function getOwner(): ?Collection
    {
        return new Collection([ $this->user ]);
    }

    public function getPrimaryOwner(): User
    {
        return $this->user;
    }

    public function getLatestStory(User $viewer) : ?Storyqueue
    {
        //$stories = Story::select(['id','slug','created_at'])->where('timeline_id', $this->id)->orderBy('created_at', 'desc')->get();
        $stories = Storyqueue::select(['id','created_at'])
            ->where('timeline_id', $this->id)
            //->where('viewer_id', $viewer->id)
            ->orderBy('created_at', 'desc')->get();
        return ($stories->count()>0) ? $stories[0] : null;
    }

    public function getUserstats()
    {
        return $this->user->getStats();
    }

    // Has the viewer seen all 'active' slides in this timeline's story (?)
    public function isEntireStoryViewedByUser($viewerId) : bool
    {
        $daysWindow = Config::get('stories.window_days');
        $notViewedCount = Storyqueue::where('timeline_id', $this->id)
            ->where('viewer_id', $viewerId)
            ->whereNull('viewed_at')
            ->where('created_at','>=',Carbon::now()->subDays($daysWindow))
            ->count();
        return ( $notViewedCount === 0 );
    }

    // No viewable stories within last time period 'window' for *this* timeline
    public function isStoryqueueEmpty() : bool
    {
        $daysWindow = Config::get('stories.window_days');
        $activeCount = Story::where('timeline_id', $this->id)
            ->where('created_at','>=',Carbon::now()->subDays($daysWindow))
            ->count();
        return ( !$activeCount );
    }

    //public function setSubscriptionPrice(int $priceInCents)
    //{
    //}

}
