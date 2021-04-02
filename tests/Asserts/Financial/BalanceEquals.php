<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Transaction;
use Money\Money;
use PHPUnit\Framework\Constraint\Constraint;

final class BalanceEquals extends Constraint
{

    /**
     * @var bool
     */
    protected $amount;

    /**
     * @var string
     */
    protected $modelClassName;

    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    public function toString(): string
    {
        $amount = $this->amount;
        if ($amount instanceof Money) {
            $amount = $amount->getAmount();
        }
        return "{$this->modelClassName} has a balance of {$amount}";
    }

    public function failureDescription($model): string
    {
        $balance = $model->balance instanceof Money ? $model->balance->getAmount() : $model->balance;
        $amount = $this->amount instanceof Money ? $this->amount->getAmount() : $this->amount;
        return "{$this->modelClassName} with a balance of {$balance} has balance of {$amount}";
    }

    public function matches($model): bool
    {
        $this->modelClassName = class_basename($model);

        // Check model
        if (!isset($model->balance)) {
            return false;
        }

        $amount = $this->amount;
        if (!$amount instanceof Money) {
            $amount = $model->asMoney($amount);
        }

        return $amount->equals($model->balance);
    }
}
