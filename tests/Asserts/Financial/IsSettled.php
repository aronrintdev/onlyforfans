<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Transaction;
use PHPUnit\Framework\Constraint\Constraint;

final class IsSettled extends Constraint
{

    /**
     * @var bool
     */
    protected $lazy;

    public function __construct($lazy = false)
    {
        $this->lazy = $lazy;
    }

    public function toString(): string
    {
        return 'is settled';
    }

    public function matches($transaction): bool
    {
        // Check model
        if (!isset($transaction->settled_at)) {
            return false;
        }

        // Check DB Record
        if (!$this->lazy) {
            if (Transaction::where('id', $transaction->getKey())->whereNotNull('settled_at')->count() === 0) {
                return false;
            }
        }

        return true;
    }
}