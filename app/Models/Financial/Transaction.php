<?php

namespace App\Models\Financial;

use Exception;
use App\Models\Casts\Money;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Financial\Traits\HasSystemByAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Financial\Exceptions\FeesTooHighException;
use App\Models\Financial\Exceptions\TransactionAlreadySettled;
use Illuminate\Support\Facades\Log;

/**
 * Financial Transaction Model
 *
 * @property string       $id
 * @property string       $account_id
 * @property \Money\Money $credit_amount
 * @property \Money\Money $debit_amount
 * @property \Money\Money $balance
 * @property string       $currency
 * @property string       $type
 * @property string       $description
 * @property string       $reference_id
 * @property string       $resource_type
 * @property string       $resource_id
 * @property array        $metadata
 * @property Carbon       $settled_at
 * @property Carbon       $failed_at
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 *
 *
 * @method static static|Builder inRange(array $range) - [ 'from', 'to' ]
 * @method static static|Builder settled()
 * @method static static|Builder notSettled()
 * @method static static|Builder failed()
 * @method static static|Builder pending()
 * @method static static|Builder isDebit()
 * @method static static|Builder isCredit()
 * @method static static|Builder type(string $type)
 * @method static static|Builder isTip()
 *
 * @package App\Models\Financial
 */
class Transaction extends Model
{
    use UsesUuid,
        HasSystemByAccount,
        HasCurrency,
        HasFactory;

    protected $connection = 'financial';
    protected $table = 'transactions';

    protected $forceCombV4Uuid = true;

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

    protected $dateFormat = 'Y-m-d H:i:s.u';

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

    protected function getDateFormate()
    {
        // Need high accuracy on this table
        return 'Y-m-d H:i:s.u';
    }

    #endregion

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function resource()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->hasOne(Transaction::class, 'reference_id');
    }

    /**
     * Transaction this fee is for
     */
    public function fee_for()
    {
        return $this->belongsTo(Transaction::class, 'fee_for');
    }

    /**
     * Fee transactions for this transaction
     */
    public function feeTransactions()
    {
        return $this->hasMany(Transaction::class, 'fee_for');
    }

    #endregion Relationships

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes

    /**
     * Only transactions within a range
     * ```
     * $transactions->inRange([ 'from' => '', 'to' => '' ])->get();
     * ```
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param array $range [ 'from' => Carbon, 'to' => Carbon ]
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInRange($query, $range)
    {
        return $query->where('created_at', '>=', $range['from'])
            ->where('created_at', '<', $range['to']);
    }

    /**
     * Transactions that have been settled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSettled($query)
    {
        return $query->whereNotNull('settled_at');
    }

    /**
     * Transactions that have NOT been settled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotSettled($query)
    {
        return $query->whereNull('settled_at');
    }

    /**
     * Transactions that have failed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('failed_at');
    }

    /**
     * Transactions that are pending settling.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('settled_at')->whereNull('failed_at');
    }

    public function scopeIsDebit($query)
    {
        return $query->where('debit_amount', '>', 0);
    }

    public function scopeIsCredit($query)
    {
        return $query->where('credit_amount', '>', 0);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIsTip($query)
    {
        return $query->type(TransactionTypeEnum::TIP);
    }

    #endregion Scopes

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Creates and the transactions for fees, taxes, and any other items that
     * need to be taken out of this transaction.
     *
     * @return Collection|null
     */
    public function settleFees()
    {
        try {
            DB::Transaction(function() {
                // Check for fees already settled
                if (isset($this->settled_at)) {
                    throw new TransactionAlreadySettled($this);
                }
                $skipFees = false;
                if (isset($this->fee_for)) {
                    // Skip Fees transactions
                    $skipFees = true;
                    // throw new TransactionAlreadySettled($this);
                }

                $transactions = new Collection([]);
                if(!$skipFees) {
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
                    foreach ($fees as $key => $fee) {
                        if ($result[$key]->isPositive()) {
                            $transactions[$key] = $this->account->moveTo(
                                Account::getFeeAccount($key, $this->system, $this->currency),
                                $result[$key],
                                [
                                    'ignoreBalance' => true,
                                    'description' => $fee->description ?? "Transaction {$key}",
                                    'type' => TransactionTypeEnum::FEE,
                                    'resource_type' => $this->resource_type,
                                    'resource_id' => $this->resource_id,
                                    'fee_for' => $this->getKey(),
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
                } // End Fees creation
                $this->settleBalance();
                $this->save();
                // Settle balance on all debit side fee transactions
                $transactions->each(function($items) {
                    $items['debit']->settleBalance();
                    $items['debit']->save();
                    $items['debit']->refresh();
                });
                return $transactions;
            });
        } catch(Exception $e) {
            $this->failed_at = Carbon::now();
            $this->metadata = array_merge($this->metadata ?? [], [
                'failed' => [
                    'exception' => class_basename($e),
                    'message' => $e->getMessage(),
                ]
            ]);
            Log::error('Transaction Settle Balance Error', ['error' => $e]);
            $this->save();
        }
    }

    /**
     * Settles the balance amount for this transaction
     */
    public function settleBalance()
    {
        if (isset($this->settled_at)) {
            throw new TransactionAlreadySettled($this);
        }
        try {
            DB::transaction(function() {
                $this->balance = $this->calculateBalance();
                $this->balance = $this->balance->add($this->credit_amount);
                $this->balance = $this->balance->subtract($this->debit_amount);
                $this->settled_at = Carbon::now();
                $this->save();
            });
        } catch(Exception $e) {
            $this->failed_at = Carbon::now();
            $this->metadata = array_merge($this->metadata ?? [], [
                'failed' => [
                    'exception' => class_basename($e),
                    'message' => $e->getMessage(),
                ]
            ]);
            Log::error('Transaction Settle Balance Error', [ 'error' => $e ]);
            $this->save();
        }

    }

    /**
     * Calculates balance from past settled transactions
     */
    public function calculateBalance()
    {
        $query = Transaction::select(DB::raw('sum(credit_amount) - sum(debit_amount) as amount'))
            ->where('account_id', $this->account->getKey())
            ->settled();

        // Find last finalized transaction summary balance
        $lastSummary = TransactionSummary::lastFinalized($this->account);
        // Calculate from last summary if there is one
        if (isset($lastSummary)) {
            $query = $query->where('created_at', '>', $lastSummary->to);
        }
        // $balance = $query->pluck('amount');
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
            $debitTrans = $this;
            $creditTrans = $this->reference;
        } else {
            $debitTrans = $this->reference;
            $creditTrans = $this;
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
            $creditTrans->save();
        }

        // If this is a partial chargeback, move funds back on new transaction so new fees can be calculated.
        if (isset($partialAmount)) {
            $debitTrans->account->moveTo($creditTrans->account, $partialAmount, [
                'type' => TransactionTypeEnum::CHARGEBACK_PARTIAL,
                'shareable_id' => $debitTrans->sharable_id ?? null,
                'ignoreBalance' => true,
            ]);
        }
        return $transactions;
    }

    /**
     * Get instance of the next created transaction in this transaction's account
     *
     * @param array $options Options for getting next transaction
     * ```
     * [
     *      'has' => 'debit_amount', // or 'credit_amount', This is optional
     *      'by' => 'created_at', // or 'settled_at', // Timestamp to go by
     *      'ignore' => new Collection([]) // Collection of transactions to not pick as next
     *      'withLock' => false, // Locks transaction and reference for update
     * ]
     * ```
     * @return Transaction
     */
    public function getNextTransaction(array $options = []): ?Transaction
    {
        // Default Options
        if (!isset($options['by'])) {
            $options['by'] = 'created_at';
        }
        if (!isset($options['ignore'])) {
            $options['ignore'] = new Collection([]);
        }
        if (!isset($options['withLock'])) {
            $options['withLock'] = false;
        }

        $query = Transaction::where('account_id', $this->account_id)
            ->where($options['by'], '>=', $this->{$options['by']})
            ->where('id', '!=', $this->getKey()) // Not this transaction
            ->orderBy($options['by']);

        if (isset($options['has'])) {
            $query->where($options['has'], '>', 0);
        }

        foreach($options['ignore'] as $ignore) {
            $query->where('id', '!=', $ignore->getKey());
        }

        if ($options['withLock']) {
            $query->with(['reference' => function ($query) {
                $query->lockForUpdate();
            }])->lockForUpdate();
        }
        return $query->first();
    }

    public function shouldCalculateFees(): bool
    {
        // From Internal to Internal account that is not a fee transaction
        $feeOn = Config::get("transactions.systems.{$this->system}.feesOn");
        return $this->reference->account->type === AccountTypeEnum::INTERNAL
            && $this->account->type === AccountTypeEnum::INTERNAL
            && $this->credit_amount->isPositive()
            && in_array($this->type, $feeOn);
    }

    #endregion

}
