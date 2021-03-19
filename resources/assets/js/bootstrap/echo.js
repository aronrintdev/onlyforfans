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

window.getCalenderFormatForLastSeen = function (
    dateTime, format = 'hh:mma', needToConvertLocalDate = 1) {
    let date = (needToConvertLocalDate) ? moment(dateTime).
    utc(dateTime).
    local() : moment(dateTime);
    return date.calendar(null, {
        sameDay: '[Today], ' + format,
        lastDay: '[Yesterday], ' + format,
        lastWeek: 'dddd, ' + format,
        sameElse: function () {
            if (moment().year() === moment(dateTime).year()) {
                return 'MMM D, ' + format;
            } else {
                return 'MMM D YYYY, ' + format;
            }
        },
    });
};

window.updateUserStatus = function (userId, status) {
    let statusHolder = $(".status-holder-"+ userId);

    if (status == 1) {
        statusHolder.addClass('online');        
    } else {
        setTimeout(function () {            
            let last_seen = 'Last seen ' +
                getCalenderFormatForLastSeen(Date(), 'hh:mma', 0);
            
        }, 3000)
    }
};

window.onbeforeunload = function () {
  window.Echo.leave('user-status');
  // updateUserStatus(1, 0);
  setLastSeenOfUser(0);
  //return undefined; to prevent dialog while window.onbeforeunload
  return undefined;
};

// 
$(window).on('load', function() {
  setLastSeenOfUser(1);
  window.Echo.join(`user-status`)
    .here((users) => {
        users.forEach(user => {
          updateUserStatus(user.id, 1);
        });
    })
    .joining((user) => {
      updateUserStatus(user.id, 1);
    })
    .leaving((user) => {
      updateUserStatus(user.id, 0);
    });
});

/**
 * Add to Vue instance
 */
Vue.use({
  install: (app, options) => {
    app.prototype.$echo = window.Echo
  }
})
