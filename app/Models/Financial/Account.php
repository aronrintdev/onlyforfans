<?php

namespace App\Models\Financial;

use App\Interfaces\Ownable;
use App\Models\Casts\Money;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;
use App\Models\Traits\OwnableTraits;
use App\Jobs\CreateTransactionSummary;

use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Traits\HasSystem;
use App\Jobs\Financial\UpdateAccountBalance;
use App\Models\Financial\Traits\HasCurrency;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\TransactionAccountMismatchException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;

class Account extends Model implements Ownable
{
    use OwnableTraits,
        UsesUuid,
        HasSystem,
        HasCurrency,
        HasFactory;

    protected $table = 'financial_accounts';

    protected $guarded = [
        'verified',
        'can_make_transaction',
    ];

    protected $dates = [
        'balance_last_updated_at',
        'pending_last_updated_at',
        'hidden_at',
    ];

    protected $casts = [
        'balance' => Money::class,
        'pending' => Money::class,
    ];

    protected static function booted()
    {
        static::creating(function (self $model): void {
            if (!isset($model->system)) {
                $model->system = Config::get('transaction.default', '');
            }
        });
    }


    /* ------------------------------ Relations ----------------------------- */
    #region Relations
    /**
     * Owner of account
     */
    public function owner()
    {
        return $this->morphTo();
    }

    public function resource()
    {
        return $this->morphTo();
    }
    #endregion


    /* ------------------------------ Functions ----------------------------- */
    #region Functions
    /**
     * Move funds to this owners internal account
     *
     * @return Collection - Transaction created successfully
     */
    public function moveToInternal($amount, array $options = []): Collection
    {
        if ($this->type !== AccountTypeEnum::IN) {
            throw new IncorrectTypeException($this, AccountTypeEnum::IN);
        }

        // Get internal account in this system and currency
        $internalAccount = $this->owner->getInternalAccount($this->system, $this->currency);

        return $this->moveTo($internalAccount, $amount, $options);
    }

    /**
     * Gets instance of internal account of owner
     * @return Account
     */
    public function getInternalAccount(): Account
    {
        return $this->owner->getInternalAccount($this->system, $this->currency);
    }

    /**
     * Move funds from one account to another, returns debit and credit transactions in collection
     *
     * @return  Collection  [ 'debit' => debit Transaction, 'credit' => credit Transaction ]
     */
    public function moveTo($toAccount, $amount, array $options = []): Collection
    {
        // Options => string $description, $access = null, $metadata = null
        $amount = $this->asMoney($amount);

        if (!$amount->isPositive()) {
            throw new InvalidTransactionAmountException($amount, $this);
        }

        $this->verifySameCurrency($toAccount);

        // Verify that both accounts allowed to make transactions
        $this->verifyCanMakeTransactions();
        $toAccount->verifyCanMakeTransactions();

        // Make transactions
        $debitTransaction = null;
        $creditTransaction = null;
        DB::transaction(function() use($toAccount, $amount, $options, &$debitTransaction, &$creditTransaction) {
            $fromAccount = Account::lockForUpdate()->find($this->getKey());
            $amount = $this->asMoney($amount);

            // Verify from account has valid balance if it is an internal account
            $newBalance = $fromAccount->balance->subtract($amount);
            $ignoreBalance = isset($options['ignoreBalance']) ? $options['ignoreBalance'] : false;
            if (
                $fromAccount->type === AccountTypeEnum::INTERNAL
                && $newBalance->isNegative()
                && ! $ignoreBalance
            ) {
                throw new InsufficientFundsException($fromAccount, $amount->getAmount(), $newBalance->getAmount());
            }
            $fromAccount->balance = $newBalance;
            $fromAccount->balance_last_updated_at = Carbon::now();
            $fromAccount->save();

            $commons = [
                'currency' => $fromAccount->currency,
                'description' => $options['description'] ?? null,
                'type' => $options['type'] ?? TransactionTypeEnum::PAYMENT,
                'shareable_id' => isset($options['shareable_id'])
                    ? $options['shareable_id']
                    : (isset($options['shareable'])
                        ? $options['shareable']->getKey()
                        : null
                    ),
                'metadata' => $options['metadata'] ?? null,
            ];

            $debitTransaction = Transaction::create(array_merge([
                'account_id' => $fromAccount->getKey(),
                'credit_amount' => 0,
                'debit_amount' => $amount->getAmount(),
            ], $commons));

            $creditTransaction = Transaction::create(array_merge([
                'account_id' => $toAccount->getKey(),
                'credit_amount' => $amount->getAmount(),
                'debit_amount' => 0,
            ], $commons));

            // Add reference ids
            $debitTransaction->reference_id = $creditTransaction->getKey();
            $creditTransaction->reference_id = $debitTransaction->getKey();

            $debitTransaction->save();
            $creditTransaction->save();
        }, 1);

        $this->refresh();

        UpdateAccountBalance::dispatch($this);
        UpdateAccountBalance::dispatch($toAccount);
        return new Collection([ 'debit' => $debitTransaction, 'credit' => $creditTransaction ]);
    }


    /**
     * Settles the account balance and pending transactions for an account
     */
    public function settleBalance()
    {
        DB::transaction(function () {
            $account = Account::lockForUpdate()->find($this->getKey());

            // Settle all pending transactions
            Transaction::where('account_id', $account->getKey())
                ->whereNull('settled_at')
                ->whereNull('failed_at')
                ->with(['reference.account', 'account'])
                ->orderBy('created_at')
                ->lockForUpdate()
                ->chunkById(10, function ($transactions) {
                    $feeOn = Config::get("transactions.systems.{$this->system}.feesOn");
                    foreach ($transactions as $transaction) {
                        if ( // From Internal to Internal account that is not a fee transaction
                            $transaction->reference->account->type === AccountTypeEnum::INTERNAL
                            && $transaction->account->type === AccountTypeEnum::INTERNAL
                            && in_array($transaction->type, $feeOn)
                        ) {
                            // Calculate Fees and Taxes On this transaction, then settle transaction and all fee debit transactions
                            $transaction->settleFees();
                        } else {
                            $transaction->settleBalance();
                            $transaction->save();
                        }
                    }
                });

            // Calculate up to date balance from current settled transactions
            $query = Transaction::select(DB::raw('sum(credit_amount) - sum(debit_amount) as amount'))
                ->where('account_id', $this->getKey())
                ->whereNotNull('settled_at');

            $lastSummary = TransactionSummary::lastFinalized($this)->with('to')->first();
            if (isset($lastSummary)) {
                $query = $query->where('settled_at', '>', $lastSummary->to->settled_at);
            }
            $balance = $this->asMoney($query->value('amount'));
            if (isset($lastSummary)) {
                $balance = $lastSummary->balance->add($balance);
            }

            // Check for any failed transaction that debit account and subtract that sum from balance
            //   Usually this will be zero with no failed transactions
            $failedAmount = Transaction::select(DB::raw('sum(debit_amount) as amount'))
                ->where('account_id', $this->getKey())
                ->whereNotNull('failed_at')
                ->value('amount');
            if (isset($failedAmount)) {
                $balance = $balance->subtract($this->asMoney($failedAmount));
            }

            if ($this->type === AccountTypeEnum::INTERNAL) {
                // Calculate new Pending

                // Get Default Hold Period
                $holdMinutes = Config::get('transactions.systems' . $this->system . '.holdPeriod');

                // TODO: Get Custom Hold Periods from DB | Jira Issue: AF-236

                if ($holdMinutes > 0) { // Don't bother executing this query if hold is 0

                    $holdSince = Carbon::now()->subMinutes($holdMinutes);

                    $ttn = Transaction::getTableName(); // Transaction Table Name
                    $atn = Account::getTableName(); // Account Table Name
                    $pending = $this->asMoney(
                        Transaction::select(DB::raw('sum(credit_amount) as amount'))
                            ->join("{$ttn} as ref", "{$ttn}.reference_id", '=', 'ref.id')
                            ->join("{$atn} as account", 'ref.account_id', '=', 'account.id')
                            ->where("{$ttn}.account_id", $this->getKey())
                            ->where("{$ttn}.settled_at", '>', $holdSince->toDateString())
                            ->where('account.type', AccountTypeEnum::INTERNAL) // Only transactions from other internal accounts
                            ->whereNotNull("settled_at")
                            ->value('amount')
                    );
                } else {
                    $pending = $this->asMoney(0);
                }
            } else {
                // Non internal accounts don't have pending balance
                $pending = $this->asMoney(0);
            }

            // Save new balance and pending
            $this->balance = $balance->subtract($pending);
            $this->balance_last_updated_at = Carbon::now();
            $this->pending = $pending;
            $this->pending_last_updated_at = Carbon::now();

            $this->save();

            // Check if summary needs to be made
            $query = Transaction::where('account_id', $this->getKey())->whereNotNull('settled_at');
            if (isset($lastSummary)) {
                $query = $query->where('settled_at', '>', $lastSummary->to->settled_at);
            }
            $count = $query->count();
            $summarizeAt = new Collection(Config::get('transactions.summarizeAt'));
            $priority = $summarizeAt->sortBy('count')->firstWhere('count', '>=', $count);
            if (isset($priority) && $count >= $priority['count']) {
                $queue = Config::get('transactions.summarizeQueue');
                CreateTransactionSummary::dispatch($this, TransactionSummaryTypeEnum::BUNDLE, 'Transaction Count')
                    ->onQueue("{$queue}-{$priority}");
            }
        });
    }

    /**
     * Handle a chargeback on an in account, rolls back transactions made with this transaction
     *
     * @param Transaction $transaction  The original transaction that was charge-backed
     * @param int|Currency $amount  The amount that was charged back uses transaction amount if not provided
     * @return Collection Collection of new transactions created for the charge back
     */
    public function handleChargeback(Transaction $transaction, $amount = null ): Collection
    {
        if ($this->type !== AccountTypeEnum::IN) {
            throw new IncorrectTypeException($this, AccountTypeEnum::IN);
        }

        if ($transaction->account_id !== $this->getKey()) {
            throw new TransactionAccountMismatchException($this, $transaction);
        }

        $roleBackTransactions = new Collection([]);
        DB::transaction(function () use ($transaction, $amount, &$roleBackTransactions) {
            $account = Account::lockForUpdate()->find($this->getKey());
            if (!isset($amount)) {
                $amount = clone $transaction->debit_amount;
            }

            $amount = $this->asMoney($amount);

            if (!$amount->isPositive()) {
                throw new InvalidTransactionAmountException($amount, $transaction);
            }

            // Find all transactions original transitions funded
            // First is transaction pair to internal account
            $remainingAmount = clone $amount;
            $currentTransaction = $transaction->reference;

            $transactions = new Collection([ $currentTransaction ]);

            // Take funds from the owners internal account balance first if there is any balance
            $internalAccount = $currentTransaction->account;
            $internalAccount->settleBalance();
            $internalAccount->refresh();
            if ($internalAccount->balance->isPositive()) {
                $remainingAmount = $remainingAmount->subtract($internalAccount->balance);
            }

            // TODO: Raise timestamp accuracy to ms

            while ($remainingAmount->isPositive()) {
                // Get next Debit transaction in owners internal account
                // This is the next purchase made after funds were deposited there, Usually one, but there could
                // potentially be more transactions so we need to check amounts and rollback all transactions that where
                // funded by this transaction
                $currentTransaction = $currentTransaction->getNextTransaction([
                    'has' => 'debit_amount',
                    'withLock' => true,
                    'ignore' => $transactions,
                ]);
                if (!isset($currentTransaction)) {
                    break;
                }
                $transactions->push($currentTransaction);
                $remainingAmount = $remainingAmount->subtract($currentTransaction->credit_amount);
            }

            // Perform rollback transactions

            // Check if last transaction was partial.
            if ($remainingAmount->isNegative()) {
                $transaction = $transactions->pop();
                // Create chargeback of partial amount
                $roleBackTransactions->push(
                    $transaction->chargeback($this->asCurrency(0)->subtract($remainingAmount))
                );
            }

            // Perform chargeback rollbacks on transactions in reverse order
            do {
                $transaction = $transactions->pop();
                $roleBackTransactions->push(
                    $transaction->chargeback()
                );
            } while ($transactions->count() > 0);
        });

        return $roleBackTransactions;
    }


    /**
     * Get a system fee account
     *
     * @param  string  $name - Name of fee
     * @param  string  $system - Financial System
     * @param  string  $currency - Account currency
     * @return  Account
     */
    public static function getFeeAccount(string $name, string $system, string $currency)
    {
        $systemOwner = SystemOwner::firstOrCreate([
            'name' => $name,
            'system' => $system,
        ]);
        return $systemOwner->getInternalAccount($system, $currency);
    }

    #endregion

    /* ------------------------- Debugging Functions ------------------------ */
    #region Debugging Functions
    /**
     * Dumps Leger of Account transactions
     * @return void
     */
    public function dumpLedger($message = '')
    {
        if (Config::get('app.env') === 'production') {
            return;
        }
        dump("Account Ledger Dump Begin {$message}", $this->attributes);
        $transactions = Transaction::where('account_id', $this->getKey())->orderBy('settled_at')->get();
        foreach($transactions as $key => $transaction) {
            $current = $key + 1;
            dump("Transaction {$current} of {$transactions->count()}", $transaction->attributes);
        }
        dump("Account Leger Dump End {$message}");
    }

    #endregion

    /* ----------------------- Verification Functions ----------------------- */
    #region VerificationFunctions
    /**
     * Check if account can make a transactions
     */
    public function canMakeTransactions()
    {
        return $this->can_make_transactions;
    }

    /**
     * Verify that account can make a transaction
     */
    public function verifyCanMakeTransactions()
    {
        if (!$this->canMakeTransactions()) {
            throw new TransactionNotAllowedException($this);
        }
    }

    #endregion

    /* ------------------------------- Ownable ------------------------------ */
    #region Ownable
    public function getOwner(): Collection
    {
        return new Collection([ $this->owner ]);
    }

    #endregion
}
