<?php

namespace App\Models;

use App\Enums\SubscriptionPeriodEnum;
use App\Interfaces\Subscribable;
use Money\Money;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use App\Models\Casts\Money as CastsMoney;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Financial\Traits\HasCurrency;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $subscribeable_type
 * @property string $subscribeable_id
 * @property Money  $price
 * @property string $currency
 * @property string $period
 * @property int    $period_interval
 * @property Collection  $custom_attributes
 * @property Carbon|null $disabled_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static static|Builder active()
 * @method static static|Builder disabled()
 * @method static static|Builder for(Subscribable $subscribable)
 * @method static static|Builder period()
 *
 * @package App\Models
 */
class SubscriptionPrice extends Model
{
    use UsesUuid, HasCurrency, SoftDeletes;

    protected $table = 'subscription_prices';

    protected $guarded = [];

    protected $casts = [
        'price' => CastsMoney::class,
        'custom_attributes' => 'collection',
    ];

    protected $dates = [
        'disabled_at',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */
    #region Relationships

    public function subscribeable()
    {
        return $this->morphTo();
    }

    #endregion Relationships

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */
    #region Scopes

    /**
     * Price that is active
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereNull('disabled_at');
    }

    /**
     * Price that is disabled
     */
    public function scopeDisabled(Builder $query)
    {
        return $query->whereNotNull('disabled_at');
    }

    /**
     * Price 'for' a specific subscribable item
     */
    public function scopeFor(Builder $query, Subscribable $subscribable)
    {
        return $query->where('subscribeable_type', $subscribable->getMorphString())
            ->where('subscribeable_id', $subscribable->getKey());
    }

    public function scopePeriod(Builder $query, $period, $period_interval)
    {
        return $query->where('period', $period)->where('period_interval', $period_interval);
    }

    #endregion Scopes

    /* -------------------------------------------------------------------------- */
    /*                                   Methods                                  */
    /* -------------------------------------------------------------------------- */
    #region Methods

    /* --------------------------------- Statics -------------------------------- */

    public static function updatePrice(
        Subscribable $subscribable,
        Money        $price,
        string       $period = SubscriptionPeriodEnum::DAILY,
        int          $period_interval = 30
    ): ?SubscriptionPrice
    {
        // Check if active already exists
        $check = static::for($subscribable)
            ->period($period, $period_interval)
            ->active()
            ->where('price', $price->getAmount())
            ->where('currency', $price->getCurrency()->getCode())
            ->first();
        if ($check) {
            // Return matched active item if already exist
            return $check;
        }

        // If price is zero will just disable other active prices
        if (!$price->isZero()) {
            // Add new price
            $subscriptionPrice = static::create([
                'subscribeable_type' => $subscribable->getMorphString(),
                'subscribeable_id'   => $subscribable->getKey(),
                'price'              => $price->getAmount(),
                'currency'           => $price->getCurrency()->getCode(),
                'period'             => $period,
                'period_interval'    => $period_interval,
            ]);
        }

        // Disable old prices
        $query = static::for($subscribable)
            ->period($period, $period_interval)
            ->active();
        if (isset($subscriptionPrice)) {
            // Don't disable new item
            $query->where('id', '!=', $subscriptionPrice->id);
        }
        $query->update([ 'disabled_at' => Carbon::now() ]);

        return $subscriptionPrice ?? null;
    }

    public static function activePriceFor(
        Subscribable $subscribable,
        string       $period = SubscriptionPeriodEnum::DAILY,
        int          $period_interval = 30,
        string       $currency = 'USD'
    ): ?SubscriptionPrice
    {
        return static::for($subscribable)
            ->period($period, $period_interval)
            ->where('currency', $currency)
            ->active()->latest()->first();
    }

    public static function oneMonthPrice(Subscribable $subscribable, string $currency = 'USD'): ?SubscriptionPrice
    {
        return static::activePriceFor($subscribable, SubscriptionPeriodEnum::DAILY, 30, $currency);
    }

    public static function threeMonthPrice(Subscribable $subscribable, string $currency = 'USD'): ?SubscriptionPrice
    {
        return static::activePriceFor($subscribable, SubscriptionPeriodEnum::DAILY, 90, $currency);
    }

    public static function sixMonthPrice(Subscribable $subscribable, string $currency = 'USD'): ?SubscriptionPrice
    {
        return static::activePriceFor($subscribable, SubscriptionPeriodEnum::DAILY, 180, $currency);
    }

    public static function twelveMonthPrice(Subscribable $subscribable, string $currency = 'USD'): ?SubscriptionPrice
    {
        return static::activePriceFor($subscribable, SubscriptionPeriodEnum::DAILY, 360, $currency);
    }

    /* ------------------------------- Non Static ------------------------------- */

    public function disable(): self
    {
        $this->update([ 'disabled_at' => Carbon::now() ]);
        return $this;
    }

    #endregion Methods

}
