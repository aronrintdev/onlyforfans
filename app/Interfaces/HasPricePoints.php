<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Money\Money;

/**
 * A Model has price points attached to it.
 *
 * @property PricePoints $pricePoints
 *
 * @package App\Interfaces
 */
interface HasPricePoints extends IsModel
{

    /**
     * Eloquent Morph Many price points
     *
     * @return MorphMany
     */
    public function pricePoints(): MorphMany;

    /**
     * Current Active Price Point
     *
     * @param string|null $currency
     * @return Money|null
     */
    public function currentPrice($currency = null): ?Money;

    /**
     * Checks validness of a promo code
     *
     * @param string $promoCode
     * @return Collection
     */
    public function checkPromoCode(string $promoCode): Collection;

    /**
     * Verifies that a price point is valid
     *
     * @param PricePoint $pricePoint
     * @return bool
     */
    public function verifyPricePoint(PricePoint $pricePoint): bool;

}
