<?php
namespace App\Models;

use Exception;
use App\Interfaces\Ownable;
use App\Interfaces\ShortUuid;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;
use Illuminate\Support\Facades\Log;

use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesShortUuid;
use App\Models\Financial\Transaction;
use App\Models\Traits\SluggableTraits;
use App\Enums\ShareableAccessLevelEnum;
use App\Interfaces\Subscribable;
use App\Interfaces\Tippable;
use App\Models\Casts\Money;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Traits\FormatMoney;
use App\Models\Traits\ShareableTraits;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use Money\Currencies\ISOCurrencies;

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
 * @property \Money\Money $price
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
        Searchable;

    //protected $appends = [ ];
    protected $keyType = 'string';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['user', 'posts', 'followers']; // %FIXME: why is this ness? timelines.show (route-model binding) loads these by default but should be lazy loading (?) %PSG

    protected $casts = [
        'name' => 'string',
        'about' => 'string',
        'price' => Money::class,
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

    public function toArray()
    {
        $array = parent::toArray();
        // Localize Price
        $array['price_display'] = static::formatMoney($this->price);
        return $array;
    }


    public function sluggable(): array
    {
        return ['slug' => [
            'source' => [ 'user.username' ],
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

    /**
     * The subscriptions for this timeline. This includes failed and canceled subscriptions
     *
     * @return MorphToMany
     */
    public function subscriptions() {
        return $this->morphMany(Subscription::class, 'subscribable');
    }

    public function ledgersales() {
        return $this->morphMany(Fanledger::class, 'purchaseable');
    }

    public function posts() {
        return $this->morphMany(Post::class, 'postable');
    }

    public function stories() {
        return $this->hasMany(Story::class);
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

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

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

    public function receivePayment(
        string $fltype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $customAttributes = []
    ): ?Fanledger {

        $result = null;

        switch ($fltype) {
            case PaymentTypeEnum::TIP:
                $result = Fanledger::create([
                    'fltype' => $fltype,
                    'seller_id' => $this->user->id,
                    'purchaser_id' => $sender->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $this->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $amountInCents,
                    'cattrs' => json_encode($customAttributes ?? []),
                ]);
                break;
            case PaymentTypeEnum::SUBSCRIPTION:
                $result = Fanledger::create([
                    'fltype' => $fltype,
                    'seller_id' => $this->user->id,
                    'purchaser_id' => $sender->id,
                    'purchaseable_type' => 'timelines', // basically a subscription
                    'purchaseable_id' => $this->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $amountInCents,
                    'cattrs' => json_encode($customAttributes ?? []),
                ]);
                break;
            default:
                throw new Exception('Unrecognized payment type : ' . $fltype);
        }

        return $result ?? null;
    }

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

    public function verifyPrice($amount): bool
    {
        $amount = $this->asMoney($amount);
        return $this->price->equals($amount);
    }

    public function getDescriptionNameString(): string
    {
        return "Timeline of {$this->name}";
    }

    public function getOwnerAccount(string $system, string $currency): Account
    {
        return $this->getOwner()->first()->getInternalAccount($system, $currency);
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

    public function getLatestStory() : ?Story
    {
        $stories = Story::select(['id','slug','created_at'])->where('timeline_id', $this->id)->orderBy('created_at', 'desc')->get();
        return ($stories->count()>0) ? $stories[0] : null;
    }

}
