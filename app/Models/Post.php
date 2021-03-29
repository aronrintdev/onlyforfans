<?php
namespace App\Models;

use DB;
use Auth;
use Exception;
use App\Models\Fanledger;
use App\Interfaces\UuidId;
use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\Deletable;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\Reportable;
use App\Interfaces\Commentable;
use App\Interfaces\HasPricePoints;
use App\Interfaces\PricePoint;
use App\Models\Traits\UsesUuid;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Traits\OwnableTraits;
use App\Models\Financial\Transaction;
use App\Models\Traits\LikeableTraits;
use App\Models\Traits\SluggableTraits;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Interfaces\Purchaseable; // was PaymentReceivable
use App\Interfaces\Tippable;
use App\Models\Casts\Money as CastsMoney;
use App\Models\Casts\PricePoint as CastsPricePoint;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Financial\Traits\HasSystem;
use App\Models\Traits\FormatMoney;
use App\Models\Traits\ShareableTraits;
use Money\Money as Money;

/**
 * Post Model
 * @package App\Models
 * 
 * @property string $id
 * @property string $slug
 * @property string $user_id
 * @property string $postable_type
 * @property string $postable_id
 * @property string $description
 * @property bool   $active
 * @property string $type
 * @property Money $price
 * @property string $currency
 * @property array  $cattrs
 * @property array  $meta
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Post extends Model
    implements
        UuidId,
        Deletable,
        Purchaseable,
        HasPricePoints,
        Tippable,
        Likeable,
        Reportable,
        Commentable
{
    use UsesUuid,
    SoftDeletes,
    HasFactory,
    OwnableTraits,
    LikeableTraits,
    Sluggable,
    SluggableTraits,
    ShareableTraits,
    FormatMoney,
    HasCurrency;

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            /*
            if (!$model->canBeDeleted()) {
                throw new Exception('Can not delete Post (26)'); // or soft delete and give access to purchasers (?)
            }
             */
            // %TODO: should ref count these (?), or N/A as we are cloning (ie ref count always 1)?
            foreach ($model->mediafiles as $o) {
                //Storage::disk('s3')->delete($o->filename); // Remove from S3  -> moved to Mediafile model
                $o->delete();
            }
            foreach ($model->comments as $o) {
                $o->delete();
            }
            foreach ($model->likes as $o) {
                $o->delete();
            }
        });
    }

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = [ 'isLikedByMe', 'isBookmarkedByMe', ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => [ 'description' ]
            ]
        ];
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    public function getIsLikedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        return $sessionUser ? $this->likes->contains($sessionUser->id) : false;
    }

    public function getIsBookmarkedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        $exists = Bookmark::where('user_id', $sessionUser->id)
            ->where('bookmarkable_id', $this->id)
            ->where('bookmarkable_type', 'posts')
            ->first();
        return $exists ? true : false;
    }

    protected $casts = [
        'price' => CastsMoney::class,
        // 'price' => CastsPricePoint::class, // Uses current price point or 0
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    public function toArray()
    {
        $array = parent::toArray();
        // Localize Price
        $array['price_display'] = static::formatMoney($array['price']);
        return $array;
    }

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function bookmarks()
    {
        //return $this->morphMany(Bookmark::class, 'bookmarkable')->withTimestamps();
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    // can be shared with many users (via [shareables])
    public function sharees()
    {
        return $this->morphToMany('App\Models\User', 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }

    public function mediafiles()
    {
        return $this->morphMany('App\Models\Mediafile', 'resource');
    }

    public function ledgersales()
    {
        return $this->morphMany('App\Models\Fanledger', 'purchaseable');
    }

    // owner of the post
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function timeline()
    {
        //return $this->belongsTo('App\Models\Timeline', 'postable_id')->where('postable_type', $this->getMorphString('App\Models\Timeline')); // %FIXME: FAILS REGRESSION
        return $this->belongsTo('App\Models\Timeline', 'postable_id');
    }

    // Direct comments
    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable')->where('parent_id', null);
    }

    // Direct comments and all comment replies
    public function allComments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    public function pricePoints(): MorphMany
    {
        return $this->morphMany(PurchasablePricePoint::class, 'purchasable');
    }

    //--------------------------------------------
    // %%% Methods
    //--------------------------------------------

    // `user()` alias
    public function poster()
    {
        return $this->user();
    }

    public function getOwner(): ?Collection
    {
        return new Collection([$this->user]);
    }

    // %%% --- Implement Purchaseable Interface ---
    #region Purchaseable

    public function receivePayment(
        string $fltype, // PaymentTypeEnum
        User $sender,
        int $amountInCents,
        array $cattrs = []
    ): ?Fanledger {
        $result = DB::transaction(function () use ($fltype, $amountInCents, $cattrs, &$sender) {

            switch ($fltype) {
                case PaymentTypeEnum::TIP:
                    $result = Fanledger::create([
                        'fltype' => $fltype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    break;
                case PaymentTypeEnum::PURCHASE:
                    $result = Fanledger::create([
                        'fltype' => $fltype,
                        'seller_id' => $this->user->id,
                        'purchaser_id' => $sender->id,
                        'purchaseable_type' => 'posts',
                        'purchaseable_id' => $this->id,
                        'qty' => 1,
                        'base_unit_cost_in_cents' => $amountInCents,
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                    /*
                    $sender->sharedposts()->attach($this->id, [
                        'cattrs' => json_encode($cattrs ?? []),
                    ]);
                     */
                    break;
                default:
                    throw new Exception('Unrecognized payment type : ' . $fltype);
            }

            return $result;
        });

        return $result ?? null;
    }

    public function verifyPrice($amount): bool
    {
        $amount = $this->asMoney($amount);
        return $this->price->equals($amount);
    }

    #endregion Purchaseable

    /* --------------------------- HasPricePoints --------------------------- */
    #region HasPricePoints

    public function currentPrice($currency = null): ?Money
    {
        $current = PurchasablePricePoint::forItem($this)->ofCurrency($currency)->getCurrent();
        return isset($current) ? $current->price : null;
    }

    public function checkPromoCode(string $promoCode): Collection
    {
        return PurchasablePricePoint::checkPromoCode($this, $promoCode);
    }

    public function verifyPricePoint(PricePoint $pricePoint): bool
    {
        return PurchasablePricePoint::isActive($pricePoint);
    }

    #endregion HasPricePoints

    /* --------------------------- PaymentSendable -------------------------- */
    #region PaymentSendable

    public function getOwnerAccount(string $system, string $currency): Account
    {
        return $this->getOwner()->first()->getInternalAccount($system, $currency);
    }

    public function getDescriptionNameString(): string
    {
        return 'Post';
    }

    #endregion PaymentSendable


    public function canBeDeleted(): bool
    {
        return !($this->ledgersales->count() > 0);
    }
}
