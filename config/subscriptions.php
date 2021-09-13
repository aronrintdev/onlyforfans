<?php

return [
    /**
     * How long to wait before allowing a resubscription
     */
    'resubscribeWaitPeriod' => [
        'unit'     => 'day',
        'interval' => 30,
    ],

    'minPriceInCents' => 300, // subscription including discount can't drop below $3.00 US
];
