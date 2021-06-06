<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;
use App\Enums\Financial\TransactionSummaryTypeEnum;
use App\Models\Casts\Money;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Financial\Traits\HasSystemByAccount;

class TransactionSummary extends Model
{
    use UsesUuid,
        HasSystemByAccount;

    protected $connection = 'financial';
    protected $table = 'transaction_summaries';

    /**
     * We should not be filling anything from the UI here, All UI interactions should be read-only.
     */
    protected $guarded = [];

    #region casts
    protected $casts = [
        'balance'        => Money::class,
        'balance_delta'  => Money::class,
        'credit_sum'     => Money::class,
        'debit_sum'      => Money::class,
        'credit_average' => Money::class,
        'debit_average'  => Money::class,
    ];

    public function getCurrencyAttribute()
    {
        return $this->account->currency;
    }

    #endregion

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function from_transaction()
    {
        return $this->belongsTo(Transaction::class, 'from_transaction_id');
    }

    public function to_transaction()
    {
        return $this->belongsTo(Transaction::class, 'to_transaction_id');
    }

    public function latestSettled()
    {
        return $this->to()->max('settled_at');
    }

    #endregion

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

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

    #endregion

}
