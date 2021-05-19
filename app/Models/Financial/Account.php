<?php

namespace App\Models\Financial;

use LogicException;
use App\Events\ItemTipped;

use App\Interfaces\Ownable;
use App\Models\Casts\Money;
use App\Interfaces\Tippable;

use App\Models\Subscription;
use App\Events\ItemPurchased;
use App\Interfaces\PricePoint;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;
use App\Interfaces\Purchaseable;
use App\Interfaces\Subscribable;
use App\Interfaces\HasPricePoints;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\OwnableTraits;
use App\Enums\SubscriptionPeriodEnum;
use App\Jobs\CreateTransactionSummary;
use Illuminate\Support\Facades\Config;
use App\Enums\ShareableAccessLevelEnum;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Traits\HasSystem;
use App\Enums\Financial\TransactionTypeEnum;
use App\Jobs\Financial\UpdateAccountBalance;
use App\Models\Financial\Traits\HasCurrency;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\InvalidCastException;
use App\Models\Financial\Exceptions\AlreadyPurchasedException;
use App\Models\Financial\Exceptions\AlreadySubscribedException;
use App\Models\Financial\Exceptions\InvalidPaymentAmountException;
use App\Models\Financial\Exceptions\Account\IncorrectTypeException;
use App\Models\Financial\Exceptions\InvalidTransactionAmountException;
use App\Models\Financial\Exceptions\Account\InsufficientFundsException;
use App\Models\Financial\Exceptions\InvalidSubscriptionAmountException;
use App\Models\Financial\Exceptions\TransactionAccountMismatchException;
use App\Models\Financial\Exceptions\Account\TransactionNotAllowedException;

/**
 * Financial Account Model
 *
 * @property string $id
 * @property string $system
 * @property string $owner_type
 * @property string $owner_id
 * @property string $name
 * @property string $type
 * @property string $currency
 * @property \Money\Money $balance
 * @property \Carbon\Carbon $balance_last_updated_at
 * @property \Money\Money $pending
 * @property \Carbon\Carbon $pending_last_updated_at
 * @property string $resource_type
 * @property string $resource_id
 * @property bool   $verified
 * @property bool   $can_make_transactions
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @package App\Models\Financial
 */
class Account extends Model implements Ownable
{
    use OwnableTraits,
        UsesUuid,
        HasSystem,
        HasCurrency,
        HasFactory,
        SoftDeletes;

    protected $connection = 'financial';
    protected $table = 'accounts';

    protected $guarded = [
        'verified',
        'can_make_transactions',
    ];

    protected $dates = [
        'balance_last_updated_at',
        'pending_last_updated_at',
        'hidden_at',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
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
                'purchasable_id' => isset($options['purchasable_id'])
                    ? $options['purchasable_id']
                    : (isset($options['purchasable'])
                        ? $options['purchasable']->getKey()
                        : null
                    ),
                'purchasable_type' => isset($options['purchasable_type'])
                    ? $options['purchasable_type']
                    : (isset($options['purchasable'])
                        ? $options['purchasable']->getMorphString()
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

        app(Dispatcher::class)->dispatch(new UpdateAccountBalance($this));
        app(Dispatcher::class)->dispatch(new UpdateAccountBalance($toAccount));
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
                ->cursor()->each(function ($transaction) {
                    if ($transaction->shouldCalculateFees()) {
                        // Calculate Fees and Taxes On this transaction, then settle transaction and all fee debit transactions
                        $transaction->settleFees();
                    } else {
                        $transaction->settleBalance();
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

            $pending = $this->asMoney(0);
            $feesFromPending = $this->asMoney(0);

            if ($this->type === AccountTypeEnum::INTERNAL) {
                // Calculate new Pending

                // Get Default Hold Period
                $holdMinutes = Config::get("transactions.systems.{$this->system}.defaults.holdPeriod");

                // TODO: Get Custom Hold Periods from DB | Jira Issue: AF-236

                if ($holdMinutes > 0) { // Don't bother executing this query if hold is 0

                    $holdSince = Carbon::now()->subMinutes($holdMinutes);

                    $holdOnTypes = Config::get("transactions.systems.{$this->system}.holdOn");
                    $pending = $this->asMoney(
                        Transaction::select(DB::raw("sum(credit_amount) as amount"))
                            ->where("account_id", $this->getKey())
                            ->where("settled_at", '>=', $holdSince->toDateString())
                            ->whereIn('type', $holdOnTypes)
                            ->whereNotNull("settled_at")
                            ->value('amount')
                    );
                    if ($pending->isPositive()) {
                        // The amount deducted by fees during pending period
                        $feesFromPending = $this->asMoney(
                            Transaction::select(DB::raw("sum(debit_amount) as amount"))
                                ->where("type", TransactionTypeEnum::FEE)
                                ->where("account_id", $this->getKey())
                                ->where("settled_at", '>=', $holdSince->toDateString())
                                ->whereNotNull("settled_at")
                                ->value('amount')
                        );
                    }
                }
            }

            // Save new balance and pending
            $this->balance = $balance->subtract($pending)->add($feesFromPending);
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
            if ($remainingAmount->isNegative()) { // Extra Balance
                $remainingAmount = $this->asMoney(0);
            }

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
                $remainingAmount = $remainingAmount->subtract($currentTransaction->debit_amount);
            }

            // Perform rollback transactions

            // Check if last transaction was partial.
            if ($remainingAmount->isNegative()) {
                $transaction = $transactions->pop();
                // Create chargeback of partial amount
                $roleBackTransactions->push(
                    $transaction->chargeback($this->asMoney(0)->subtract($remainingAmount))
                );
                if (isset($transaction->purchasable_id)) {
                    // Revoke access to item for every owner of chargeback account
                    $this->getOwner()->each(function ($owner) use ($transaction) {
                        $transaction->purchasable->revokeAccess($owner, 'chargeback');
                    });
                }
            }

            // Perform chargeback rollbacks on transactions in reverse order
            while ($transactions->count() > 0) {
                $transaction = $transactions->pop();
                $roleBackTransactions->push(
                    $transaction->chargeback()
                );
                if (isset($transaction->purchasable_id)) {
                    // Revoke access to item for every owner of chargeback account
                    $this->getOwner()->each(function ($owner) use ($transaction) {
                        $transaction->purchasable->revokeAccess($owner, 'chargeback');
                    });
                }
            }
        });

        // Set account to not be able to make transactions
        $canMakeTransactions = $this->can_make_transactions;
        $this->can_make_transactions = false;
        $this->save();

        // Raise Admin Flag
        Flag::raise($this, [
            'column' => 'can_make_transactions',
            'delta_before' => $canMakeTransactions,
            'delta_after' => false,
            'description' => 'Account was disabled due to chargeback issued',
        ]);

        return $roleBackTransactions;
    }

    /**
     * Creates transactions to purchase a purchaseable model and grants access to that model
     *
     * @param Purchaseable $purchaseable
     * @param int|Money|PricePoint $payment
     * @return Collection
     */
    public function purchase(
        Purchaseable $purchaseable,
        $payment,
        $purchaseLevel = ShareableAccessLevelEnum::PREMIUM,
        $customAttributes = [],
        $transactionAttributes = []
    ): Collection
    {
        // Prevent purchasing more than once
        if ($purchaseable->checkAccess($this->getOwner()->first(), $purchaseLevel) === true) {
            throw new AlreadyPurchasedException($purchaseable, $this->getOwner()->first());
        }

        if ($payment instanceof PricePoint) {
            $amount = $payment->price;
        } else {
            $amount = $this->asMoney($payment);
        }
        // Verify Price
        if ($payment instanceof PricePoint && $purchaseable instanceof HasPricePoints) {
            if ($purchaseable->verifyPricePoint($payment) === false) {
                throw new InvalidPaymentAmountException($this, $amount, $purchaseable);
            }
        } else {
            if ($purchaseable->verifyPrice($amount) === false) {
                throw new InvalidPaymentAmountException($this, $amount, $purchaseable);
            }
        }

        // Move funds to internal account first if this is a in account
        if ($this->type === AccountTypeEnum::IN) {
            $this->moveToInternal($amount);
            $internalAccount = $this->getInternalAccount();
            return $internalAccount->purchase(
                $purchaseable,
                $amount,
                $purchaseLevel,
                $customAttributes,
                array_merge($transactionAttributes, [
                    'ignoreBalance' => true,
            ]));
        }

        // Payment funds movement
        $transactions = $this->moveTo(
            $purchaseable->getOwnerAccount($this->system, $this->currency),
            $amount,
            array_merge($transactionAttributes, [
                'purchasable' => $purchaseable,
                'type' => TransactionTypeEnum::SALE,
                'description' => "Purchase of {$purchaseable->getDescriptionNameString()} {$purchaseable->getKey()}"
        ]));

        $purchaseable->grantAccess($this->getOwner()->first(), $purchaseLevel, array_merge($customAttributes,  [
            'purchase' => [
                'pricePoint' => ($payment instanceof PricePoint) ? $payment->getKey() : null,
                'price' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
                'transaction_id' => $transactions['credit']->getKey(),
            ],
        ]));

        ItemPurchased::dispatch($purchaseable, $this->getOwner()->first());

        return $transactions;
    }

    public function tip(
        Tippable $tippable,
        $payment,
        $customAttributes = []
    ): Collection
    {
        $amount = $this->asMoney($payment);

        // Move funds to internal account first if this is a in account
        if ($this->type === AccountTypeEnum::IN) {
            $this->moveToInternal($amount);
            $internalAccount = $this->getInternalAccount();
            return $internalAccount->tip($tippable, $amount, array_merge($customAttributes, [ 'ignoreBalance' => true ]));
        }

        // Payment funds movement
        $transactions = $this->moveTo($tippable->getOwnerAccount($this->system, $this->currency), $amount, [
            'purchasable' => $tippable,
            'type' => TransactionTypeEnum::TIP,
            'description' => "Tip to {$tippable->getDescriptionNameString()} {$tippable->getKey()}"
        ]);

        ItemTipped::dispatch($tippable, $this->getOwner()->first());

        return $transactions;
    }

    /**
     * Creates a new subscription and makes initial charge
     *
     * @param Subscribable $subscribable
     * @param int|Money $payment
     * @param array $options
     * @return Collection
     *
     * @throws AlreadySubscribedException
     * @throws InvalidCastException
     * @throws LogicException
     */
    public function createSubscription(
        Subscribable $subscribable,
        $payment,
        $options = []
    ): Subscription
    {
        // Check if already subscribed.
        if (
            Subscription::where('user_id', $this->getOwner()->first()->getKey())
                ->where('subscribable_id', $subscribable->getKey())
                ->where('subscribable_type', $subscribable->getMorphString())
                ->where('active', true)
                ->count() > 0
        ) {
            throw new AlreadySubscribedException($subscribable, $this->getOwner()->first());
        }

        // Check price
        $payment = $this->asMoney($payment);
        if ($subscribable->verifyPrice($payment) === false) {
            throw new InvalidSubscriptionAmountException($this, $payment, $subscribable);
        }

        // Create Subscription Model
        return Subscription::create([
            'subscribable_id'   => $subscribable->getKey(),
            'subscribable_type' => $subscribable->getMorphString(),
            'user_id'           => $this->getOwner()->first()->getKey(),
            'account_id'        => $this->getKey(),
            'manual_charge'     => $options['manual_charge'] ?? true,
            'period'            => $options['period'] ?? SubscriptionPeriodEnum::MONTHLY,
            'period_interval'   => $options['period_interval'] ?? 1,
            'price'             => $payment->getAmount(),
            'currency'          => $this->currency,
            'active'            => false,
            'access_level'      => $options['access_level'] ?? ShareableAccessLevelEnum::PREMIUM,
            'custom_attributes' => $options['custom_attributes'] ?? null,
            'metadata'          => $options['metadata'] ?? null,
        ]);
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
