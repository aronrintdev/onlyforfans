<?php

namespace Tests\Helpers\Financial;

use App\Enums\Financial\AccountTypeEnum;
use Faker\Factory;
use Faker\Generator;
use App\Models\Financial\Account;
use Illuminate\Support\Collection;
use App\Models\Financial\Transaction;

/**
 * Builder for generating random transactions for accounts
 * @package Tests\Helpers\Financial
 */
class TransactionsBuilder
{
    /**
     * Number of transactions to create
     * @var int
     */
    protected $transactionCount = 1;

    /**
     * Accounts that get credited in transactions
     * @var array
     */
    protected $creditAccounts = [];

    /**
     * Accounts that get debited in transactions
     * @var array
     */
    protected $debitAccounts = [];

    /**
     * Chance of a specific credit account being picked for transaction
     * @var array
     */
    // protected $creditAccountChances = [];

    /**
     * Chance of a specific debit account being picked for transaction
     * @var array
     */
    // protected $debitAccountChances = [];

    /**
     * Min amount of any transaction
     * @var int
     */
    protected $minAmount = 100;

    /**
     * Max amount of any transaction
     * @var int
     */
    protected $maxAmount = 10000;

    /**
     * Faker instance
     * @var Generator
     */
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Set transactions to be created
     *
     * @param int $count
     * @return TransactionsBuilder
     */
    public function withCount(int $count): TransactionsBuilder
    {
        $this->transactionCount = $count;
        return $this;
    }

    /**
     * Set what account the transactions should come from
     *
     * @param Account $account
     * @return TransactionsBuilder
     */
    public function fromAccount(Account $account): TransactionsBuilder
    {
        $this->debitAccounts = new Collection([ $account ]);
        return $this;
    }

    /**
     * Set multiple accounts the transactions should come from
     *
     * @param Collection|array $accounts
     * @return TransactionsBuilder
     */
    public function fromAccounts($accounts, /* array $chances = [] */): TransactionsBuilder
    {
        if (!$accounts instanceof Collection) {
            $accounts = new Collection($accounts);
        }
        $this->debitAccounts = $accounts;
        // $this->debitAccountChances = $chances;
        return $this;
    }

    /**
     * Set what account the transactions should go to.
     *
     * @param Account $account
     * @return TransactionsBuilder
     */
    public function toAccount(Account $account): TransactionsBuilder
    {
        $this->creditAccounts = new Collection([ $account ]);
        return $this;
    }

    /**
     * Set multiple accounts the transactions should go from
     *
     * @param mixed $accounts
     * @return TransactionsBuilder
     */
    public function toAccounts($accounts, /* array $chances = [] */): TransactionsBuilder
    {
        if (!$accounts instanceof Collection) {
            $accounts = new Collection($accounts);
        }
        $this->creditAccounts = $accounts;
        // $this->creditAccountChances = $chances;
        return $this;
    }

    /**
     * Creates transaction pairs and returns them in a Collection
     *
     * @return Collection Collection of the transaction pairs created
     */
    public function create(): Collection
    {
        if (empty($this->debitAccounts)) {
            $this->fromAccount(Account::factory()->asIn()->create());
        }
        if (empty($this->creditAccounts)) {
            $this->toAccount(Account::factory()->asInternal()->create());
        }

        $transactions = new Collection([]);
        for ($i = 0; $i > $this->transactionCount; $i++) {
            $transactions->merge($this->createTransaction);
        }
        return $transactions;
    }

    /**
     * Create new transaction pair chosen at random from properties
     *
     * @return Collection
     */
    public function createTransaction(): Collection
    {
        $debitAccount = $this->faker->randomElement($this->debitAccounts);
        $creditAccount = $this->faker->randomElement($this->creditAccounts);
        $amount =  $debitAccount->asMoney($this->faker->numberBetween($this->minAmount, $this->maxAmount));

        // Check balances of internal accounts, if there is some money left, set amount to total of balance
        if ($debitAccount->type === AccountTypeEnum::INTERNAL) {
            if ($debitAccount->balance->isZero()) {
                // Account balance is Zero, can't make any transactions
                return new Collection([]);
            }
            if ($debitAccount->balance->subtract($amount)->isNegative()) {
                $amount = clone $debitAccount->balance;
            }
        }

        $transactions = new Collection([]);
        // Check if need to move to Internal account first.
        if ($debitAccount->type === AccountTypeEnum::IN) {
            if ($debitAccount->getWalletAccount()->id !== $creditAccount->id) {
                $transactions = $transactions->moveToWallet($amount);
                $debitAccount = $debitAccount->getWalletAccount();
                $debitAccount->settleBalance();
            }
        }

        $transactions = $transactions->merge($debitAccount->moveTo($amount, $creditAccount));
        return $transactions;
    }

}