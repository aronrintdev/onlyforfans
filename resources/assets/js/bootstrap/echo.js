/**
 * Laravel Echo and Pusher bootstrapping
 */

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Vue from 'vue'
import Pusher from 'pusher-js'
import Echo from 'laravel-echo'

if (typeof window.Pusher === 'undefined') {
  window.Pusher = Pusher
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
  })
}

window.setLastSeenOfUser = function (status) {
  $.ajax({
      type: 'post',
      url: '/update-last-seen',
      data: { status: status },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      },
      success: function (data) {
      },
  });
};

window.onbeforeunload = function () {
  // updateUserStatus(1, 0);
  setLastSeenOfUser(0);
  window.Echo.leave('user-status');
  //return undefined; to prevent dialog while window.onbeforeunload
  return undefined;
};

// 
$(window).on('load', function() {
  setLastSeenOfUser(1);
});

/**
 * Add to Vue instance
 */
Vue.use({
  install: (app, options) => {
    app.prototype.$echo = window.Echo
  }
})
