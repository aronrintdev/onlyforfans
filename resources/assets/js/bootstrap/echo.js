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
  var options = {}
  if (process.env.NODE_ENV === 'production') {
    options = {
      broadcaster: 'pusher',
      key: process.env.MIX_PUSHER_APP_KEY || window.pusherKey,
      host: process.env.MIX_WS_HOST || window.location.hostname,
      httpHost: process.env.MIX_WS_HOST || window.location.hostname,

      wsHost: process.env.MIX_WS_HOST || window.location.hostname,
      wsPort: process.env.MIX_WS_PORT || 6001,
      wssPort: process.env.MIX_WS_PORT || 6001,
      forceTLS: process.env.MIX_PUSHER_FORCE_TLS || false,
      encrypted: process.env.MIX_PUSHER_ENCRYPTED || false,
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      disabledTransports: ['sockjs', 'xhr_polling', 'xhr_streaming'],
    }
  } else {
    options = {
      broadcaster: 'pusher',
      key: process.env.MIX_PUSHER_APP_KEY || window.pusherKey,
      host: window.location.hostname,
      httpHost: window.location.hostname,
      wsHost: process.env.MIX_WS_HOST || window.location.hostname,
      wsPort: process.env.MIX_WS_PORT || 6001,
      forceTLS: false,
      encrypted: false,
      disableStats: true,
      enabledTransports: ['ws'],
      disabledTransports: ['sockjs', 'xhr_polling', 'xhr_streaming'],
    }
  }
  window.Echo = new Echo(options)
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
