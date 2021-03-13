<?php

namespace App\Models\Financial;

use App\Models\Casts\Money;
use Illuminate\Support\Carbon;
use App\Models\Traits\UsesUuid;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Financial\Exceptions\FeesTooHighException;
use App\Models\Financial\Exceptions\TransactionAlreadySettled;

class Transaction extends Model
{
    use UsesUuid,
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

    /* ---------------------------- Relationships --------------------------- */
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

    /* ------------------------------ Functions ----------------------------- */

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
            }
        }
        // Make sure user still has amount left
        if (!$result['remainder']->isPositive()) {
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
                        'type' => $this->type,
                        'shareable_id' => $this->shareable_id,
                        'metadata' => [ 'fee' => true ],
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
        $lastSummary = TransactionSummary::lastFinalized($this->account)->with('to')->first();
        // Calculate from last summary if there is one
        if (isset($lastSummary)) {
            $query = $query->where('settled_at', '>', $lastSummary->to->settled_at);
        }
        $balance = $this->asMoney($query->pluck('amount'));
        if (isset($lastSummary)) {
            $balance = $lastSummary->balance->add($balance);
        }

        return $balance;
    }


}