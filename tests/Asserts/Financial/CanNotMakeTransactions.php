<?php

namespace Tests\Asserts\Financial;

use App\Models\Financial\Account;
use PHPUnit\Framework\Constraint\Constraint;

final class CanNotMakeTransactions extends Constraint
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
        return 'can not make transactions';
    }

    public function matches($account): bool
    {
        // Check model
        if (!isset($account->can_make_transactions)) {
            return false;
        }

        if ($account->can_make_transactions !== false) {
            return false;
        }

        // Check DB Record
        if (!$this->lazy) {
            if (Account::where([
                'id' => $account->getKey(),
                'can_make_transactions' => false
            ])->count() === 0) {
                return false;
            }
        }

        return true;
    }
}
