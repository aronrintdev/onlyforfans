/**
 * resources/assets/js/bootstrap/filters.js
 */
import Vue from 'vue'
import currencyCodes from 'currency-codes'

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
});

Vue.filter('niceGuid', function (v) {
  return v.slice(-12);
});

Vue.filter('enumPostType', function (k) {
  switch (k) {
    case 'free':
      return 'Free'
    case 'price':
      return 'Purchase-Only'
    case 'paid':
      return 'Subscriber-Only'
  }
});

// Assumes post as input
Vue.filter('isSubscriberOnly', function (p) {
  return p.type === 'paid';
});

Vue.filter('isPurchaseable', function (p) {
  return p.type === 'price';
});

