<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class TransactionTypeEnum extends SmartEnum
{
    /** Marks a transaction as part of a chargeback. */
    const CHARGEBACK         = 'chargeback';

    /** Returned amount if chargeback did not take the full amount from the reference transaction */
    const CHARGEBACK_PARTIAL = 'chargeback_partial';

    /** Marks a transaction as part of a refund */
    const CREDIT             = 'refund';

    /** Marks a transaction as being for a fee */
    const FEE                = 'fee';

    // const PAYMENT            = 'payment'; /** Now known as transfer to avoid confusion */
    /**
     * Transfer of funds. Specifically this transaction does not incur fees or pending balances.
     */
    const TRANSFER           = 'transfer';

    /** A payout to an 'out' outbound account. */
    const PAYOUT             = 'payout';

    /** Transaction was for a sale of a resource */
    const SALE               = 'sale';

    /** Transaction was for a reoccurring subscription */
    const SUBSCRIPTION       = 'subscription';

    /** Transaction was for a tip */
    const TIP                = 'tip';

    public static $keymap = [
        self::CHARGEBACK         => 'Chargeback',
        self::CHARGEBACK_PARTIAL => 'Chargeback Partial',
        self::CREDIT             => 'Refund',
        self::FEE                => 'Fee',
        self::TRANSFER           => 'Transfer',
        self::PAYOUT             => 'Payout',
        self::SALE               => 'Sale',
        self::SUBSCRIPTION       => 'Subscription',
        self::TIP                => 'Tip',
    ];
}
