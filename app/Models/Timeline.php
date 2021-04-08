<?php
namespace App\Models;

use Exception;
use App\Interfaces\Ownable;
use App\Interfaces\ShortUuid;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;

use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesShortUuid;
use App\Models\Financial\Transaction;
use App\Models\Traits\SluggableTraits;
use App\Enums\ShareableAccessLevelEnum;
use App\Interfaces\Tippable;
use App\Models\Casts\Money;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Traits\FormatMoney;
use App\Models\Traits\ShareableTraits;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
class Timeline extends Model implements Tippable, Reportable
{
    use SoftDeletes,
        HasFactory,
        OwnableTraits,
        UsesUuid,
        Sluggable,
        SluggableTraits,
        ShareableTraits,
        FormatMoney,
        HasCurrency;

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

    // includes subscribers (ie premium + default followers)
    public function followers()
    {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->withTimestamps();
    }

    public function subscribers()
    {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')
            ->withPivot('access_level', 'shareable_type', 'sharee_id', 'is_approved', 'cattrs')
            ->where('access_level', 'premium')
            ->withTimestamps();
    }

    public function ledgersales()
    {
        return $this->morphMany(Fanledger::class, 'purchaseable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'postable');
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avatar()
    {
        return $this->belongsTo(Mediafile::class, 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo(Mediafile::class, 'cover_id');
    }

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

    public function grantAccess(User $user, string $accessLevel, $cattrs = [], $meta = []): void
    {
        //
    }
    public function revokeAccess(User $user, $cattrs = [], $meta = []): void
    {
        //
    }

    public function getOwnerAccount(string $system, string $currency): Account
    {
        return $this->owner->getInternalAccount($system, $currency);
    }

    public function verifyPrice($amount): bool
    {
        $amount = $this->asMoney($amount);
        return $this->price->equals($amount);
    }

    public function getDescriptionNameString(): string
    {
        return "Timeline of {$this->name}";
    }

    #endregion

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

}
