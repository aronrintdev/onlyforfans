<?php

namespace App\Models\Financial\Traits;

use App\Models\Financial\Transaction;
use App\Enums\Financial\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model scopes for the Transaction Model
 *
 * Placed here to keep line length of Transaction Model lower
 *
 * @method static static|Builder inRange(array $range) - [ 'from', 'to' ]
 * @method static static|Builder settled()
 * @method static static|Builder notSettled()
 * @method static static|Builder latestSettled()
 * @method static static|Builder sameAccountAs(Transaction $transaction)
 * @method static static|Builder failed()
 * @method static static|Builder pending()
 * @method static static|Builder isDebit()
 * @method static static|Builder isCredit()
 * @method static static|Builder type(string $type)
 *
 * @method static static|Builder scopeIsChargeback()
 * @method static static|Builder scopeIsChargebackPartial()
 * @method static static|Builder scopeIsRefund()
 * @method static static|Builder scopeIsFee()
 * @method static static|Builder scopeIsTransfer()
 * @method static static|Builder scopeIsPayout()
 * @method static static|Builder scopeIsSale()
 * @method static static|Builder scopeIsSubscription()
 * @method static static|Builder isTip()
 *
 * @package App\Models\Financial\Traits
 */
trait TransactionScopes
{

    /**
     * Only transactions within a range
     * ```
     * $transactions->inRange([ 'from' => '', 'to' => '' ])->get();
     * ```
     *
     * @param  Builder  $query
     * @param array $range [ 'from' => Carbon, 'to' => Carbon ]
     * @return Builder
     */
    public function scopeInRange($query, $range)
    {
        return $query->where('created_at', '>=', $range['from'])
            ->where('created_at', '<', $range['to']);
    }

    /**
     * Transactions that have been settled.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSettled($query)
    {
        return $query->whereNotNull('settled_at');
    }

    /**
     * Transactions that have NOT been settled.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNotSettled($query)
    {
        return $query->whereNull('settled_at');
    }

    /**
     * Order by latest settled at
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeLatestSettled($query)
    {
        return $query->latest('settled_at');
    }

    /**
     * Transactions from the same account as the one passed in
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSameAccountAs($query, Transaction $transaction)
    {
        return $query->where('account_id', $transaction->account_id);
    }


    /**
     * Transactions that have failed.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('failed_at');
    }

    /**
     * Transactions that are pending settling.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('settled_at')->whereNull('failed_at');
    }

    /**
     * Transaction is a debit
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDebit($query)
    {
        return $query->where('debit_amount', '>', 0);
    }

    /**
     * Transaction is a credit
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCredit($query)
    {
        return $query->where('credit_amount', '>', 0);
    }

    /**
     * Transaction of a specific type
     *
     * @param  Builder  $query
     * @param  string  $type
     * @return Builder
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }


    /* ---------------------------------- Types --------------------------------- */

    /**
     * Transaction is of type 'chargeback'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsChargeback($query)
    {
        return $query->type(TransactionTypeEnum::CHARGEBACK);
    }

    /**
     * Transaction is of type 'chargeback_partial'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsChargebackPartial($query)
    {
        return $query->type(TransactionTypeEnum::CHARGEBACK_PARTIAL);
    }

    /**
     * Transaction is of type 'refund'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsRefund($query)
    {
        return $query->type(TransactionTypeEnum::CREDIT);
    }

    /**
     * Transaction is of type 'fee'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsFee($query)
    {
        return $query->type(TransactionTypeEnum::FEE);
    }

    /**
     * Transaction is of type 'transfer'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsTransfer($query)
    {
        return $query->type(TransactionTypeEnum::TRANSFER);
    }

    /**
     * Transaction is of type 'payout'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsPayout($query)
    {
        return $query->type(TransactionTypeEnum::PAYOUT);
    }

    /**
     * Transaction is of type 'sale'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsSale($query)
    {
        return $query->type(TransactionTypeEnum::SALE);
    }

    /**
     * Transaction is of type 'subscription'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsSubscription($query)
    {
        return $query->type(TransactionTypeEnum::SUBSCRIPTION);
    }


    /**
     * Transaction is of type 'tip'
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsTip($query)
    {
        return $query->type(TransactionTypeEnum::TIP);
    }

}
