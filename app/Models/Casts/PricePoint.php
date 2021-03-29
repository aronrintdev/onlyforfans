<?php

namespace App\Models\Casts;

use App\Interfaces\HasPricePoints;
use Money\Currency;
use Money\Money as MoneyPhp;
use App\Models\PurchasablePricePoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;

/**
 * Casts integer amounts on models with a currency to Money\Money objects
 *
 * @package App\Models\Casts
 */
class PricePoint implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Get value as Money\Money Object that uses model's currency
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return \Money\Money
     */
    public function get($model, $key, $value, $attributes): \Money\Money
    {
        // TODO: Add user currency preference checking
        $pricePoint = $model->pricePoints()->getCurrent();
        return (isset($pricePoint)) ? $pricePoint->price : new MoneyPhp(0, new Currency($model->currency));
    }

    /**
     * Returns the value of a money object as integer
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return void
     */
    public function set($model, $key, $value, $attributes)
    {
        // Save value passed in as current default price point
        if ($model instanceof HasPricePoints) {
            PurchasablePricePoint::getDefaultFor($model, $value)->saveAsCurrentDefault();
        }
        if ($value instanceof MoneyPhp) {
            return (int)$value->getAmount();
        }
        return (int)$value;
    }

    /**
     * Get serialized representation of the value. i.e. toArray value.
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return int
     */
    public function serialize($model, string $key, $value, array $attributes): int
    {
        return (int)$this->get($model, $key, $value, $attributes)->getAmount();
    }
}
