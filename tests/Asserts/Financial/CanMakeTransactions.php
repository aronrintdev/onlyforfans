<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Account;
use PHPUnit\Framework\Constraint\Constraint;

final class CanMakeTransactions extends Constraint
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
        return 'can make transactions';
    }

    public function matches($account): bool
    {
        // Check model
        if (!isset($account->can_make_transactions)) {
            return false;
        }

        if ($account->can_make_transactions !== true) {
            return false;
        }

        // Check DB Record
        if (!$this->lazy) {
            if (Account::where([
                'id' => $account->getKey(),
                'can_make_transactions' => true
            ])->count() === 0) {
                return false;
            }
        }

        return true;
    }
}
