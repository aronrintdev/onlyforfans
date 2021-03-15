<?php

namespace Tests\Asserts;

trait CustomAsserts
{
    /**
     * Compares Money\Money currency values for equality in amount, can also pass in int
     */
    protected function assertCurrencyAmountIsEqual($expected, $actual, string $message = '')
    {
        $constraint = new CurrencyAmountIsEqual($expected);
        $this->assertThat($actual, $constraint, $message);
    }
}