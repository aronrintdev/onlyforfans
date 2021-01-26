/**
 * Laravel Echo and Pusher bootstrapping
 */

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'


if (typeof window.Pusher === 'undefined') {
    window.Pusher = require('pusher-js');
}

if (typeof window.Echo === 'undefined') {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY || window.pusherKey,
        // key: 'c0277f01daca608700b8',
        cluster: process.env.MIX_PUSHER_APP_CLUSTER || window.pusherCluster,
        // cluster: 'us2'
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
        // encrypted: true,
    });
}

