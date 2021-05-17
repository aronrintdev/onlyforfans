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
                //$o->delete();
                $o->diskmediafile->deleteReference($o->id);
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
    protected $appends = [ 'isLikedByMe', 'isFavoritedByMe', ];

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
        if ( !$sessionUser ) {
            return false;
        }
        return $this->likes->contains($sessionUser->id);
    }

    public function getIsFavoritedByMeAttribute($value)
    {
        $sessionUser = Auth::user();
        if ( !$sessionUser ) {
            return false;
        }
        $exists = Favorite::where('user_id', $sessionUser->id)
            ->where('favoritable_id', $this->id)
            ->where('favoritable_type', 'posts')
            ->first();
        return $exists ? true : false;
    }

    protected $casts = [
        'price' => CastsMoney::class,
        // 'price' => CastsPricePoint::class, // Uses current price point or 0
        'cattrs' => 'array',
        'meta' => 'array',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function favorites()
    {
        //return $this->morphMany(Favorite::class, 'favoritable')->withTimestamps();
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // can be shared with many users (via [shareables])
    public function sharees()
    {
        return $this->morphToMany(User::class, 'shareable', 'shareables', 'shareable_id', 'sharee_id')->withTimestamps();
    }

    public function mediafiles()
    {
        return $this->morphMany(Mediafile::class, 'resource');
    }

    public function ledgersales()
    {
        return $this->morphMany(Fanledger::class, 'purchaseable');
    }

    // owner of the post
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shareables()
    {
        return $this->morphMany(Shareable::class, 'shareable');
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
    // %%% Scopes
    //--------------------------------------------

    public function scopeByTimeline($query, $timelineID)
    {
        return $query->where('postable_type', 'timelines')->where('postable_id', $timelineID);
    }

    public function scopeHomeTimeline($query)
    {
        return $query->whereHas('timeline', function($q1) {
            $q1->whereHas('followers', function($q2) {
                $q2->where('users.id', request()->user()->id );
            });
        });
    }

    public function scopeSort($query, $sortBy)
    {
        switch ($sortBy) {
        case 'likes':
            $query->orderBy('likes_count', 'desc');
            break;
        case 'comments':
            $query->orderBy('comments_count', 'desc');
            break;
        default:
            $query->latest();
        }
        return $query;
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

    public function getPrimaryOwner(): User
    {
        return $this->user;
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
        return isset($current) ? $current->price : $this->price;
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
