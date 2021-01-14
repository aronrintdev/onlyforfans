/**
 * Laravel Echo and Pusher bootstrapping
 */

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    // key: process.env.PUSHER_APP_KEY || window.pusherKey,
    // cluster: process.env.PUSHER_APP_CLUSTER || window.pusherCluster,
    key: 'c0277f01daca608700b8',
    cluster: 'us2'
});
