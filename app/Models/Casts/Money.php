<?php

namespace App\Models\Casts;

use Money\Currency;
use Money\Money as MoneyPhp;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Money implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return new MoneyPhp( $value, new Currency($model->currency) );
    }

    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof MoneyPhp) {
            return $value->getAmount();
        }

        return $value;
    }
}