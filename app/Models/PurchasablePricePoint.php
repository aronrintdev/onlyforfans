<?php

namespace App\Models;

use App\Enums\ShareableAccessLevelEnum;
use App\Interfaces\HasPricePoints;
use App\Interfaces\PricePoint;
use Carbon\Carbon;
use App\Models\Casts\Money;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Financial\Traits\HasCurrency;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Price point of a purchasable item.
 *
 * @property string       $id
 * @property string       $purchasable_id
 * @property string       $purchasable_type
 * @property \Money\Money $price
 * @property string       $currency
 * @property bool         $current
 * @property bool         $active
 * @property Carbon|null  $available_at
 * @property Carbon|null  $expires_at
 * @property string|null  $available_with
 * @property string       $access_level
 * @property array|null   $custom_attributes
 * @property array|null   $metadata
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property Carbon|null  $deleted_at
 * 
 * @property Purchaseable $purchasable
 *
 * Scopes
 * @method static static|Builder active()
 * @method static static|Builder default()
 * @method static static|Builder expired()
 * @method static static|Builder forItem(Purchasable $item)
 * @method static PurchasablePricePoint|null getCurrent()
 * @method static static|Builder ofCurrency(string|null $currencyCode)
 * @method static static|Builder promoCode(string $promoCode)
 * @method static static|Builder upcoming()
 *
 * @package App\Models
 */
class PurchasablePricePoint extends Model implements PricePoint
{
    use UsesUuid,
        SoftDeletes,
        HasCurrency;

    protected $tableName = 'purchasable_price_points';

    protected $guarded = [];

    /**
     * Model's Default values
     *
     * @var array
     */
    protected $attributes = [
        'current' => false,
        'active' => false,
        'access_level' => ShareableAccessLevelEnum::PREMIUM,
    ];

    /* -------------------------------- Casts ------------------------------- */
    #region Casts
    protected $casts = [
        'price'             => Money::class,
        'current'           => 'bool',
        'active'            => 'bool',
        'custom_attributes' => 'array',
        'metadata'          => 'array',
    ];

    protected $dates = [
        'available_at',
        'expires_at',
    ];

    #endregion Casts

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    /**
     * The purchasable item
     * @return MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo();
    }

    #endregion Relationships

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes
    /**
     * Only return active price points
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($query) {
                $query->where('expires_at', '>=', Carbon::now())
                    ->orWhereNull('expires_at');
            })->where(function ($query) {
                $query->where('available_at', '<=', Carbon::now())
                    ->orWhereNull('available_at');
        });
    }

    /**
     * Is a default price, e.i. no special times or promo codes
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query)
    {
        return $query->whereNull('available_at')->whereNull('expires_at')->whereNull('available_with');
    }

    /**
     * Expired Price points
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope to specific purchasable item
     *
     * @param Builder $query
     * @param Purchaseable $item
     * @return Builder
     */
    public function scopeForItem(Builder $query, Purchaseable $item)
    {
        return $query->where([
            'purchasable_id'   => $item->getKey(),
            'purchasable_type' => $item->getMorphString(),
        ]);
    }

    /**
     * Gets the current price point
     *
     * @param  Builder  $query
     * @return PurchasablePricePoint
     */
    public function scopeGetCurrent($query)
    {
        return $query->where('current', true)->orderByDesc('updated_at')->first();
    }

    /**
     * Gets the current price point
     *
     * @param  Builder  $query
     * @return PurchasablePricePoint
     */
    public function scopeGetCurrentDefault($query)
    {
        return $query->default()->where('current', true)->orderByDesc('updated_at')->first();
    }

    /**
     * Scopes price points to a currency
     *
     * @param mixed $query
     * @param string|null $currency
     * @return Builder
     */
    public function scopeOfCurrency($query, $currency = null)
    {
        if (isset($currency)) {
            return $query->where('currency', $currency);
        }
        return $query->where('currency', static::getDefaultCurrency());
    }

    /**
     * Scopes promo code check to query
     *
     * @param Builder $query
     * @param string $promoCode
     * @return Builder
     */
    public function scopePromoCode(Builder $query, string $promoCode)
    {
        return $query->where('available_with', $promoCode);
    }

    /**
     * Scopes upcoming price points
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('active', true)->where('available_at', '>', Carbon::now());
    }

    #endregion Scopes

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Makes this price point the active current point for this item and currency
     * @return void
     */
    public function saveAsCurrentDefault()
    {
        DB::transaction(function() {
            PurchasablePricePoint::forItem($this->purchasable)
                ->default()
                ->ofCurrency($this->currency)
                ->where('current', true)
                ->update([ 'current' => false, 'active' => false ]);
            $this->current = true;
            $this->active = true;
            $this->save();
        });
    }

    /**
     * Gets or creates default price point item
     * @param int|\Money\Money  $price
     * @return PurchasablePricePoint
     */
    public static function getDefaultFor(HasPricePoints $item, $price, $access_level = ShareableAccessLevelEnum::PREMIUM): PurchasablePricePoint
    {
        return $item->pricePoints()->firstOrCreate([
            'price'          => ($price instanceof \Money\Money) ? $price->getAmount() : $price,
            'currency'       => ($price instanceof \Money\Money) ? $price->getCurrency() : static::getDefaultCurrency(),
            'available_at'   => null,
            'expires_at'     => null,
            'available_with' => null,
            'access_level'   => $access_level,
        ]);
    }

    /**
     * Returns the Price Point of item with
     *
     * @param Purchaseable $item
     * @param string $promoCode
     * @return Collection
     * ```
     * [
     *      bool 'available' => "If promo code is available",
     *      bool 'expired'   => "If promo code was previously available",
     * ]
     * ```
     */
    public static function checkPromoCode(Purchaseable $item, string $promoCode, string $currency = null): Collection
    {
        return new Collection([
            'available' => static::forItem($item)->ofCurrency($currency)->promoCode($promoCode)->active()->count() > 0,
            'expired'   => static::forItem($item)->ofCurrency($currency)->promoCode($promoCode)->count() > 0,
        ]);
    }

    /**
     * Checks if Price point is active
     * @param PurchasablePricePoint $pricePoint
     * @return bool
     */
    public static function isActive(PurchasablePricePoint $pricePoint): bool
    {
        $pricePoint->refresh();
        return $pricePoint->active && ($pricePoint->expires_at->isFuture() || !isset($pricePoint->expires_at));
    }

    #endregion Functions

}