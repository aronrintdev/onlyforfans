<?php
return [
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', 'pk_test_kd9HWNDTQUWGyZOyNCZxnEGc00aJqDeuHG'),
    'secret_key' => env('STRIPE_SECRET_KEY', 'sk_test_DP3udsRoL7eUS1SAKxWiMuGf00ykXU5JjG'),
    'client_id' => env('STRIPE_CLIENT_ID', 'ca_HJLaNwakmU9S2RbgHJymewd826j6Wbtb'),
    'platform_fee' => env('PLATFORM_FEE', 10),
    'webhook' => env('STRIPE_WEBHOOK', 'whsec_Q7L23oi0augJo69ilxWFusbKhN75oDzW')
];
