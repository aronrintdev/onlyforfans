<?php

namespace App\Models\Financial;

use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Casts\Money;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Financial\Traits\HasSystemByAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Financial\Exceptions\FeesTooHighException;
use App\Models\Financial\Exceptions\TransactionAlreadySettled;
use App\Models\Financial\Exceptions\TransactionNotSettledException;

class Transaction extends Model
{
    use UsesUuid,
        HasSystemByAccount,
        HasCurrency,
        HasFactory;

    protected $table = 'financial_transactions';

    protected $guarded = [
        'settled_at',
        'failed_at',
    ];

    protected $dates = [
        'settled_at',
        'failed_at',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::updating(function ($transaction) {
            if (isset($transaction->settled_at) && !$transaction->isDirty('settled_at') && $transaction->isDirty()) {
                throw new TransactionAlreadySettled($transaction);
            }
        });
        static::saving(function ($transaction) {
            if (isset($transaction->settled_at) && !$transaction->isDirty('settled_at') && $transaction->isDirty()) {
                throw new TransactionAlreadySettled($transaction);
            }
        });
    }

    #region Casts
    protected $casts = [
        'metadata' => 'array',
        'credit_amount' => Money::class,
        'debit_amount' => Money::class,
        'balance' => Money::class,
    ];

    public function getSystemAttribute()
    {
        return $this->account->system;
    }

    #endregion

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function sharable()
    {
        // TODO: References Sharable table
        // return $this->hasOne(Access::class);
    }

    public function reference()
    {
        return $this->hasOne(Transaction::class, 'reference_id');
    }

    #endregion

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Creates and the transactions for fees, taxes, and any other items that
     * need to be taken out of this transaction.
     */
    public function settleFees(): Collection
    {
        // Check for fees already settled
        if (isset($this->settled_at)) {
            throw new TransactionAlreadySettled($this);
        }
        if (isset($this->metadata) && isset($this->metadata['feeTransactions'])) {
            throw new TransactionAlreadySettled($this);
        }

        // Get Defaults
        $fees = Config::get('transactions.systems.' . $this->system . '.fees');

        // Check for fee default overwrites for this account
        // TODO: Create DB Table and get this check

        // Allocate Funds
        $takes = [];
        foreach($fees as $key => $fee) {
            $takes[$key] = $fee['take'];
        }
        if ( array_sum($takes) >= 100 ) {
            throw new FeesTooHighException($this->system, $fees, $this, array_sum($takes) . '%' );
        }
        // User remaining ratio
        $takes = array_merge($takes, [ 'remainder' => 100 - array_sum($takes)]);
        $result = $this->credit_amount->allocate($takes);

        // Check minimums and adjust
        foreach ($fees as $key => $fee) {
            if ( $result[$key]->lessThan($this->asMoney($fee['min'] ?? 0)) ) {
                $diff = $this->asMoney($fee['min'])->subtract($result[$key]);
                $result[$key] = $result[$key]->add($diff);
                $result['remainder'] = $result['remainder']->subtract($diff);
                if ($result['remainder']->isNegative()) {
                    // Add back to make remainder zero
                    $diff = $this->asMoney(0)->subtract($result['remainder']);
                    $result[$key] = $result[$key]->subtract($diff);
                    $result['remainder'] = $result['remainder']->add($diff);
                }
            }
        }
        // Make sure user still has amount left, if this is not a chargeback partial amount
        if (!$result['remainder']->isPositive() && $this->type !== TransactionTypeEnum::CHARGEBACK_PARTIAL ) {
            $feeTotal = 0;
            foreach ($fees as $key => $fee) {
                $feeTotal += $result[$key]->getAmount();
            }
            throw new FeesTooHighException($this->system, $fees, $this, $feeTotal);
        }

        // Move to System Accounts
        $transactions = new Collection([]);
        foreach ($fees as $key => $fee) {
            if ($result[$key]->isPositive()) {
                $transactions[$key] = $this->account->moveTo(
                    Account::getFeeAccount($key, $this->system, $this->currency),
                    $result[$key],
                    [
                        'ignoreBalance' => true,
                        'description' => $fee->description ?? "Transaction {$key}",
                        'type' => TransactionTypeEnum::FEE,
                        'shareable_id' => $this->shareable_id,
                        'metadata' => [ 'fee' => true, 'feeFor' => $this->getKey() ],
                    ]
                );
            }
        }
        // Save fees as metadata
        $this->metadata = array_merge(
            $this->metadata ?? [],
            [
                'feeTransactions' => $transactions->map(function($item) {
                    return $item->map(function($item) {
                        return $item->getKey();
                    })->all();
                })->all(),
            ]
        );

        $this->save();
        $this->settleBalance();
        $this->save();
        // Settle balance on all debit side fee transactions
        $transactions->each(function($items) {
            $items['debit']->settleBalance();
            $items['debit']->save();
            $items['debit']->refresh();
        });
        return $transactions;
    }

    /**
     * Settles the balance amount for this transaction
     */
    public function settleBalance()
    {
        if (isset($this->settled_at)) {
            throw new TransactionAlreadySettled($this);
        }
        $this->balance = $this->calculateBalance();
        $this->balance = $this->balance->add($this->credit_amount);
        $this->balance = $this->balance->subtract($this->debit_amount);
        $this->settled_at = Carbon::now();
    }

    /**
     * Calculates balance from past settled transactions
     */
    public function calculateBalance()
    {
        $query = Transaction::select(DB::raw('sum(credit_amount) - sum(debit_amount) as amount'))
            ->where('account_id', $this->account->getKey())
            ->whereNotNull('settled_at');

        // Find last finalized transaction summary balance
        $lastSummary = TransactionSummary::lastFinalized($this->account)->with('to:settled_at')->first();
        // Calculate from last summary if there is one
        if (isset($lastSummary)) {
            $query = $query->where('settled_at', '>', $lastSummary->to->settled_at);
        }
        $balance = $query->pluck('amount');
        $balance = $this->asMoney($query->value('amount'));
        if (isset($lastSummary)) {
            $balance = $lastSummary->balance->add($balance);
        }

        return $balance;
    }

    /**
     * Create chargeback transaction for this transaction
     *
     * @param int|Money|null $amount Optional amount if not charging back full transaction amount
     * @return Collection Collection of chargeback transactions => [ 'debit', 'credit' ]
     */
    public function chargeback($partialAmount = null): Collection
    {
        $transactions = new Collection([]);
        if ($this->debit_amount->isPositive()) {
            $debitTrans = $this->account;
            $creditTrans = $this->reference->account;
        } else if ($this->credit_amount->isPositive()) {
            $debitTrans = $this->reference->account;
            $creditTrans = $this->account;
        }
        $options = [
            'type' => TransactionTypeEnum::CHARGEBACK,
            'shareable_id' => $debitTrans->sharable_id ?? null,
            'metadata' => [
                'chargebackFor' => [
                    'debit' => $debitTrans->getKey(),
                    'credit' => $creditTrans->getKey(),
                ],
            ],
            'ignoreBalance' => true, // A chargeback may send the account into the negative
        ];
        // Check for fee transactions
        if (isset($creditTrans->metadata['feeTransactions'])) {
            // Chargeback Fee transactions
            foreach($creditTrans->metadata['feeTransactions'] as $feeTransactions) {
                $feeTransaction = Transaction::with('reference')->find($feeTransactions['debit']);
                $transactions['fees'] = new Collection($feeTransaction->chargeback());
            }
        }
        // Create chargeback items
        $transactions->push(
            $creditTrans->account->moveTo($debitTrans->account, $debitTrans->debit_amount, $options)
        );

        // If not settled, these transactions need to be settled now to avoid new fees being calculated on them.
        if (!isset($debitTrans->settled_at)) {
            $debitTrans->settleBalance();
            $debitTrans->save();
        }
        if (!isset($creditTrans->settled_at)) {
            $creditTrans->settleBalance();
            $debitTrans->save();
        }

        // If this is a partial chargeback, move funds back on new transaction so new fees can be calculated.
        if (isset($partialAmount)) {
            $debitTrans->account->moveTo($creditTrans->account, $partialAmount, [
                'type' => TransactionTypeEnum::CHARGEBACK_PARTIAL,
                'shareable_id' => $debitTrans->sharable_id ?? null,
            ]);
        }
        return $transactions;
    }

    /**
     * Gets instance of the next settled transaction in this transaction's account
     *
     * @param bool $withLock Locks transaction and reference for update
     * @return Transaction
     */
    public function getNextSettledTransaction($withLock = false): Transaction
    {
        if (!isset($this->settled_at)) {
            throw new TransactionNotSettledException($this);
        }
        $query = Transaction::where('account_id', $this->account_id)
            ->where('settled_at', '>', $this->settled_at)
            ->orderBy('settled_at');
        if ($withLock) {
            $query->with(['reference' => function ($query) {
                $query->lockForUpdate();
            }])->lockForUpdate();
        }
        return $query->first();
    }

    /**
     * Get instance of the next created transaction in this transaction's account
     *
     * @param bool $withLock Locks transaction and reference for update
     * @return Transaction
     */
    public function getNextTransaction($withLock = false): Transaction
    {
        $query = Transaction::where('account_id', $this->account_id)
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at');
        if ($withLock) {
            $query->with(['reference' => function ($query) {
                $query->lockForUpdate();
            }])->lockForUpdate();
        }
        return $query->first();
    }

    /**
     * Get instance of the next created debit transaction in this transaction's account
     *
     * @param bool $withLock Locks transaction and reference for update
     * @return Transaction
     */
    public function getNextDebitTransaction($withLock = false): Transaction
    {
        $query = Transaction::where('account_id', $this->account_id)
            ->where('created_at', '>', $this->created_at)
            ->where('debit_amount', '>', 0)
            ->orderBy('created_at');
        if ($withLock) {
            $query->with(['reference' => function ($query) {
                $query->lockForUpdate();
            }])->lockForUpdate();
        }
        return $query->first();
    }

    /**
     * Get instance of the next created credit transaction in this transaction's account
     *
     * @param bool $withLock Locks transaction and reference for update
     * @return Transaction
     */
    public function getNextCreditTransaction($withLock = false): Transaction
    {
        $query = Transaction::where('account_id', $this->account_id)
            ->where('created_at', '>', $this->created_at)
            ->where('credit_amount', '>', 0)
            ->orderBy('created_at');
        if ($withLock) {
            $query->with(['reference' => function ($query) {
                $query->lockForUpdate();
            }])->lockForUpdate();
        }
        return $query->first();
    }

    #endregion

}
