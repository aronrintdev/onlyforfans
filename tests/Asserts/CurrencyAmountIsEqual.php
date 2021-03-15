<?php

namespace Tests\Asserts;

use Money\Money;
use PHPUnit\Framework\Constraint\Constraint;

final class CurrencyAmountIsEqual extends Constraint
{
    /**
     * @var mixed
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toString(): string
    {
        return 'currency amount is equal to';
    }

    protected function matches($other): bool
    {
        if ($this->value instanceof Money) {
            $this->value = (int)$this->value->getAmount();
        }

        if ($other instanceof Money) {
            $other = (int)$other->getAmount();
        }

        if ($this->value === $other) {
            return true;
        }
        return false;
    }
}