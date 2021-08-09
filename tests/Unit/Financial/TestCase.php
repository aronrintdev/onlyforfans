<?php

namespace Tests\Unit\Financial;

use App\Models\Financial\Account;
use App\Models\Financial\Flag;
use Tests\TestCase as TestsTestCase;
use App\Models\Financial\SystemOwner;
use App\Models\Financial\Transaction;
use App\Models\Financial\TransactionSummary;
use Illuminate\Support\Facades\Config;
use Tests\Asserts\Financial\FinancialAsserts;

/**
 * Has setup for Financial Unit Tests
 */
class TestCase extends TestsTestCase
{
    use FinancialAsserts;

    protected $defaultSystem;
    protected $defaultCurrency;
    protected $tableNames = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->defaultSystem = Config::get('transactions.default');
        $this->defaultCurrency = Config::get('transactions.defaultCurrency');
        $this->tableNames = $this->getTableNames();

        dump('copy empty database template');
        exec('cp ' . __DIR__ . '/../../../database/templateEmpty.sqlite ' . __DIR__ . '/../../../storage/logs/tmp4test.sqlite');
    }

    public function tearDown(): void
    {
        dump('copy seeded database template');
        $this->setupDatabase();
    }

    public function getConnectionString()
    {
        return 'financial';
    }

    public function getTableNames()
    {
        return [
            'account'            => app(Account::class)->getTable(),
            'transaction'        => app(Transaction::class)->getTable(),
            'transactionSummary' => app(TransactionSummary::class)->getTable(),
            'systemOwner'        => app(SystemOwner::class)->getTable(),
            'flag'               => app(Flag::class)->getTable(),
        ];
    }
}