<?php
/**
 * Financial Transaction configurations
 */

use App\apis\Segpay\Api;

/**
 * Note: All currency values in whole integers
 */
return [

    /**
     * Default system to use
     */
    'default' => 'segpay',

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

            'minTransaction' => [
                /**
                 * The minimum fee amount on any transaction
                 */
                'fee' => env('TRANSACTIONS_SEGPAY_MIN_FEE', 1),

                /**
                 * The minimum tax amount on any transaction
                 */
                'tax' => env('TRANSACTIONS_SEGPAY_MIN_TAX', 0),

                /**
                 * The minimum amount that can be transferred into the system on a single transaction
                 */
                'in' => env('TRANSACTIONS_SEGPAY_MIN_IN', 1),

                /**
                 * The minimum amount that can be transferred out of the system on a single transaction
                 */
                'out' => env('TRANSACTIONS_SEGPAY_MIN_OUT', 1),
            ],
        ],
        // 'other system' => [],
    ],

    /**
     * Api classes
     */
    'apis' => [
        'segpay' => Api::class,
        // 'otherProcessor' => null,
    ],

];
