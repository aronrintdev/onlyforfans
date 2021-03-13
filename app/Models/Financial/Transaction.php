<?php

namespace App\Models\Financial;

use App\Models\Access;
use App\Models\Casts\Money;
use App\Models\Traits\UsesUuid;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use UsesUuid;

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
    public function settleFees()
    {
        //
    }

    /**
     * Settles the balance amount for this transaction
     */
    public function settleBalance()
    {
        $this->balance = $this->asMoney($this->calculateBalance());
        $this->balance = $this->balance->add($this->credit_amount);
        $this->balance = $this->balance->subtract($this->debit_amount);
        $this->settled_at = Carbon::now();
    }

    /**
     * Calculates balance from past settled transactions
     */
    public function calculateBalance()
    {
        $balance = Transaction::select(DB::raw('sum(credit_amount) - sum(debit_amount) as amount'))
            ->where('account_id', $this->account->getKey())
            ->where('created_at', '<', $this->created_at)
            ->whereNotNull('settled_at');
        return $balance->amount;
    }


}