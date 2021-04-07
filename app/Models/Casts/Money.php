<?php

namespace App\Models\Casts;

use Money\Currency;
use Money\Money as MoneyPhp;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Casts integer amounts on models with a currency to Money\Money objects
 *
 * @package App\Models\Casts
 */
class Money implements CastsAttributes, SerializesCastableAttributes
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
        return new MoneyPhp( $value, new Currency($model->currency) );
    }

    /**
     * Returns the value of a money object as integer
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return int
     */
    public function set($model, $key, $value, $attributes): int
    {
        if ($value instanceof MoneyPhp) {
            $attributes['currency'] = $value->getCurrency();
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
        return $this->doSerialize($value);
    }

    public static function doSerialize($value): int
    {
        if ($value instanceof MoneyPhp) {
            return (int)$value->getAmount();
        }
        return (int)$value;
    }
}