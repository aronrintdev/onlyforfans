<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use App\Models\Casts\Money;
use Illuminate\Database\Eloquent\Builder;

class TransactionSummary extends Model
{
    use UsesUuid;

    protected $table = 'financial_transaction_summaries';

    /**
     * We should not be filling anything from the UI here, All UI interactions should be read-only.
     */
    protected $guarded = [];

    protected $casts = [
        'balance'        => Money::class,
        'balance_delta'  => Money::class,
        'credit_sum'     => Money::class,
        'debit_sum'      => Money::class,
        'credit_average' => Money::class,
        'debit_average'  => Money::class,
    ];


    /* ---------------------------- Relationships --------------------------- */
    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function from()
    {
        return $this->hasOne(Transaction::class, 'from_transaction_id');
    }

    public function to()
    {
        return $this->hasOne(Transaction::class, 'to_transaction_id');
    }

    public function latestSettled()
    {
        return $this->to()->max('settled_at');
    }

    /* ------------------------------ Functions ----------------------------- */
    /**
     * Creates a new un-finalized Transaction Summary
     *
     * @param  Account  $account
     * @param  TransactionSummaryTypeEnum  $type
     * @param  array  $options
     * @return  TransactionSummary
     */
    public static function start(Account $account, $type = TransactionSummaryTypeEnum::CUSTOM, $options = [] ): TransactionSummary
    {
        return TransactionSummary::create(array_merge([
            'account_id' => $account->getKey(),
            'type' => $type,
            'finalized' => false,
        ], $options));
    }

    /**
     * Get the latest finalized Summary for an account. Latest is latest to_transaction settled_at
     *
     * @param  Account  $account
     * @return  TransactionSummary
     */
    public static function lastFinalized(Account $account)
    {
        return TransactionSummary::select(TransactionSummary::getTableName() . '.*')
            ->join(Transaction::getTableName(), Transaction::getTableName() . '.id', '=', TransactionSummary::getTableName() . '.to_transaction_id' )
            ->where('finalized', true)
            ->orderByDesc(Transaction::getTableName() . '.settled_at')
            ->limit(1);
    }


}