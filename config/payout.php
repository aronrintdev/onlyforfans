<?php
/*
 |--------------------------------------------------------------------------
 | Controls payout and payoutGateway configuration
 | -------------------------------------------------------------------------
 */

return [
    'batch' => [
        'rollover' => [
            /**
             * The UTC time at which Payout Batches will start collecting to a new batch.
             * Default is 0:00 UTC
             */
            'time' => env('PAYOUT_BATCH_ROLLOVER_TIME', '0:00'),

            /**
             * Prep new batch if transaction added this close to rollover
             */
            'prep_at' => [
                'amount' => env('PAYOUT_BATCH_ROLLOVER_PREP_AT_AMOUNT', 10),
                'unit'   => env('PAYOUT_BATCH_ROLLOVER_PREP_AT_UNIT'  , 'minutes'),
            ],
        ],
    ],


];
