<?php
/**
 * Financial Transaction configurations
 */

use App\apis\Segpay\Api;
use App\Enums\Financial\TransactionTypeEnum;

/**
 * Note: All currency values in whole integers
 */
return [

    /**
     * Default system to use
     */
    'default' => env('TRANSACTIONS_DEFAULT_SYSTEM', 'segpay'),
    'defaultCurrency' => env('TRANSACTIONS_DEFAULT_CURRENCY', 'USD'),

    'disableAll' => env('TRANSACTIONS_DISABLE_ALL', 0),
    'disableUserAbilityAtRegister' => env('TRANSACTIONS_USER_DISABLE_AT_START', 0),

    /**
     * All transactions live within a system, all background money in a system must live in same location, e.i. bank
     * account, crypto account, ect.
     * This prevents any cross overs where issues may occur
     */
    'systems' => [
        'segpay' => [
            'defaults' => [
                /**
                 * System default and fallback currency
                 */
                'currency' => 'USD',

                /**
                 * Default Api to use
                 */
                'api' => 'segpay',

                /**
                 * Default holding period before content creator can take out funds.
                 * In minutes
                 */
                'holdPeriod' => env('TRANSACTIONS_SEGPAY_HOLD_PERIOD', 3 * 24 * 60), // default 3 days
            ],
            /**
             * Available currencies,
             * 'Code' => 'Common Name'
             */
            'availableCurrencies' => [
                'USD' => 'US Dollar',
                'AUD' => 'Australian Dollar',
                'CAD' => 'Canadian Dollar',
                'CHF' => 'Swiss Franc',
                'DKK' => 'Danish Krona',
                'HKD' => 'Hong Kong Dollar',
                'JPY' => 'Japanese Yen',
                'NOK' => 'Norwegian Krona',
                'SEK' => 'Swedish Krona',
                'EUR' => 'Euro',
                'GBP' => 'British Pound',
            ],

            /**
             * What fees to apply to internal to internal account transactions
             */
            'fees' => [
                /**
                 * Fee options:
                 * take => the percentage of the transaction this fee will take
                 * min => the minimum amount that will always be taken for this fee
                 */
                'platformFee' => [
                    'take' => env('TRANSACTIONS_SEGPAY_PLATFORM_FEE_PERCENTAGE', 30),
                    'min' => env('TRANSACTIONS_SEGPAY_MIN_PLATFORM_FEE', 5),
                ],
                // 'tax' => [
                //     'take' => env('TRANSACTIONS_SEGPAY_TAX_PERCENTAGE', 5),
                //     'min' => env('TRANSACTIONS_SEGPAY_MIN_TAX', 0),
                // ],
            ],

            /**
             * These are the transaction types that fees are calculated on, other transaction types will not calculate
             * and store fees
             */
            'feesOn' => [
                TransactionTypeEnum::SALE,
                TransactionTypeEnum::SUBSCRIPTION,
                TransactionTypeEnum::TIP,
                TransactionTypeEnum::CHARGEBACK_PARTIAL,
            ],

            /**
             * Transaction Types that cause a hold on funds for a creator. i.e puts funds in pending for pending period
             */
            'holdOn' => [
                TransactionTypeEnum::SALE,
                TransactionTypeEnum::SUBSCRIPTION,
                TransactionTypeEnum::TIP,
                TransactionTypeEnum::CHARGEBACK_PARTIAL,
            ],

            'minTransaction' => [
                /**
                 * The minimum amount that can be transferred into the system on a single transaction
                 */
                'in' => env('TRANSACTIONS_SEGPAY_MIN_IN', 300),

                /**
                 * The minimum amount that can be transferred out of the system on a single transaction
                 */
                'out' => env('TRANSACTIONS_SEGPAY_MIN_OUT', 300),
            ],
        ],
        // 'other system' => [],

    ], // systems

    /**
     * Name of the queue running summarizations
     *      Note this is now the connection name
     */
    'summarizeQueue' => 'financial-summaries',

    /**
     * Number of not summarized transactions in a account to request a summary be made with priority
     */
    'summarizeAt' => [
        [
            'priority' => env('QUEUE_PREFIX', '') . 'financial-summaries',
            'count'    => env('TRANSACTIONS_SUMMARIZE_AT', 1000), // 1k
        ], [
            'priority' => env('QUEUE_PREFIX', '') . 'financial-summaries-high',
            'count'    => env('TRANSACTIONS_SUMMARIZE_AT_HIGH', 10000), // 10k
        ], [
            'priority' => env('QUEUE_PREFIX', '') . 'financial-summaries-urgent',
            'count'    => env('TRANSACTIONS_SUMMARIZE_AT_URGENT', 100000) // 100k
        ],
    ],

    /**
     * Api classes
     */
    'apis' => [
        'segpay' => Api::class,
        // 'otherProcessor' => null,
    ],

];
