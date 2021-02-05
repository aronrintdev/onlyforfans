<?php

/**
 * config/ziggy.php
 *
 * For more information: https://github.com/tighten/ziggy
 */

return [
    'except' => [
        '_debugbar.*',
        'debugbar.*',
        'admin.*',
        'webhook.*',
    ],
    'groups' => [
        'admin' => [ 'admin.*' ],
    ],
];
