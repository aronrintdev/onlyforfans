/**
 * resources/assets/js/bootstrap/filters.js
 */
import Vue from 'vue'
import currencyCodes from 'currency-codes'
import moment from 'moment'

/**
 * Converts raw currency value to localized currency display.
 */
Vue.filter('niceCurrency', function (value, currency = 'USD') {
  if (typeof value === 'object') {
    currency = value.currency || 'USD'
    value = value.amount
  }

  let currencyDigits = currencyCodes.code(currency).digits
  let currencyModifier = Math.pow(10, currencyDigits)
  let formatter = new Intl.NumberFormat(navigator.languages, { style: 'currency', currency })

  return formatter.format(value / currencyModifier)
})

Vue.filter('niceGuid', function (v) {
  if ( typeof v === 'string' ) {
    return v ? v.slice(-12) : ''
  } else {
    return ''
  }
})

Vue.filter('niceBool', function (v) {
  if ( v === 0 || v===false) {
    return 'N'
  }  else if ( v === 1 || v===true) {
    return 'Y'
  } else {
    return '?'
  }
})

Vue.filter('enumPostType', function (k) {
  switch (k) {
    case 'free':
      return 'Free'
    case 'price':
      return 'Purchase-Only'
    case 'paid':
      return 'Subscriber-Only'
  }
})

// Assumes post as input
Vue.filter('isSubscriberOnly', function (p) {
  return p.type === 'paid'
})

Vue.filter('isPurchaseable', function (p) {
  return p.type === 'price'
})

Vue.filter('ucfirst', function(str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
})

Vue.filter('niceDate', function (value) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  return moment(value).format('MMMM Do, YYYY')
})

Vue.filter('niceDateTime', function (value) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  return moment(value).format('MMMM Do, YYYY HH:mm:ss')
})

Vue.filter('niceDateTimeShort', function (value) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  return moment(value).format('MMM Do, YYYY HH:mm:ss')
})
