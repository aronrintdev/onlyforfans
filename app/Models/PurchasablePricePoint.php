<?php

namespace App\Models;

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

/**
 * Price point of a purchasable item.
 *
 * @property string      $id
 * @property string      $purchasable_id
 * @property string      $purchasable_type
 * @property int         $price
 * @property string      $currency
 * @property bool        $current
 * @property bool        $active
 * @property Carbon|null $expires_at
 * @property string|null $available_with
 * @property string      $access_level
 * @property array|null  $custom_attributes
 * @property array|null  $metadata
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * Scopes
 * @method static static|Builder active()
 * @method static static|Builder forItem(Purchasable $item)
 * @method static static|Builder getCurrent()
 * @method static static|Builder ofCurrency(string|null $currencyCode)
 * @method static static|Builder promoCode(string $promoCode)
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

    /* -------------------------------- Casts ------------------------------- */
    #region Casts
    protected $casts = [
        'amount'            => Money::class,
        'custom_attributes' => 'array',
        'metadata'          => 'array',
    ];

    protected $dates = [
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
        return $query->where('active', true)->where(function ($query) {
            $query->where('expires_at', '>=', Carbon::now())
                ->orWhereNull('expires_at');
        });
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

    #endregion Scopes

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

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

    #endregion Functions

}