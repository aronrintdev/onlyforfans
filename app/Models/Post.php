<?php
namespace App\Models;

use DB;
use Auth;
use Exception;
use App\Interfaces\UuidId;
use App\Interfaces\Ownable;
use App\Interfaces\Likeable;
use App\Interfaces\Deletable;
use App\Enums\PaymentTypeEnum;
use App\Enums\ShareableAccessLevelEnum;
use App\Interfaces\Reportable;
use App\Interfaces\Commentable;
use App\Interfaces\HasPricePoints;
use App\Interfaces\PricePoint;
use App\Interfaces\Contenttaggable;
use App\Models\Traits\ContenttaggableTraits;
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
use Laravel\Scout\Searchable;
use Money\Money as Money;

use App\Models\Likeable as LikeableModel;
use App\Models\Contenttaggable as ContenttaggableModel;

class Post extends Model
    implements
        UuidId,
        Deletable,
        Purchaseable,
        HasPricePoints,
        Contenttaggable,
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
    ContenttaggableTraits,
    Sluggable,
    SluggableTraits,
    ShareableTraits,
    FormatMoney,
    HasCurrency,
    Searchable;

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
            // delete the Likeables, not the 'likes' relation, which is actually the liker (user) !!
            $likeables = LikeableModel::where('likeable_type', 'posts')->where('likeable_id', $model->id)->delete();

            // delete the Contenttagables (join table records)
            $contenttaggales = ContenttaggableModel::where('contenttaggable_type', 'posts')->where('contenttaggable_id', $model->id)->delete();
        });
    }

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];
    protected $appends = [ 'isLikedByMe', 'isFavoritedByMe', 'customSlug' ];
    protected $fillable = [ 'schedule_datetime', 'description', 'price', 'price_for_subscribers', 'currency', 'user_id', 'active', 'type', 'expire_at' ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['description', 'customSlug']
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

    public function getCustomSlugAttribute()
    {
        return date('Y-m-d-H-i-s');
    }

    protected $casts = [
        'price' => CastsMoney::class,
        'price_for_subscribers' => CastsMoney::class,
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

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'resource');
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

    public function contentflags()
    {
        return $this->morphMany(Contentflag::class, 'flaggable');
    }

    public function contenttags() { // all content tags
        return $this->morphToMany(Contenttag::class, 'contenttaggable')->withPivot('access_level')->withTimestamps();
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
            $q1->orWhere('timelines.user_id', request()->user()->id);
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
        return "posts_index";
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
            'name'        => $this->timeline->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'id'          => $this->getKey(),
        ];
    }

    #endregion Searchable
    /* ---------------------------------------------------------------------- */

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

    /**
     * Required by Purchaseable, Checks if price if valid
     *
     * @param int|Money $amount
     * @param string $currency
     * @return bool
     */
    public function verifyPrice($amount, $currency = 'USD'): bool
    {
        if (!$amount instanceof Money) {
            $amount = CastsMoney::toMoney($amount, $currency);
        }
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
        return $this->getOwner()->first()->getEarningsAccount($system, $currency);
    }

    public function getDescriptionNameString(): string
    {
        return 'Post';
    }

    #endregion PaymentSendable


    public function canBeDeleted(): bool
    {
        return !($this->transactions->count() > 0);
    }
}
