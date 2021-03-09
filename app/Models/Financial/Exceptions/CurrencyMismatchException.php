<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;

class CurrencyMismatchException extends RuntimeException
{
    protected $model1;
    protected $Model2;

    public function __construct($model1, $model2)
    {
        $this->make($model1, $model2);
    }

    public function make($model1, $model2)
    {
        $this->model1 = $model1;
        $this->model2 = $model2;

        $this->message = "Currency Mismatch! [{$model1->currency}] does not match [{$model2->currency}]";
    }
}