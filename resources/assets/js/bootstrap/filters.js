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

Vue.filter('niceCurrencyRounded', function (value, currency = 'USD') {
  if (typeof value === 'object') {
    currency = value.currency || 'USD'
    value = value.amount
  }

  let currencyDigits = currencyCodes.code(currency).digits
  let currencyModifier = Math.pow(10, currencyDigits)
  let formatter = new Intl.NumberFormat(navigator.languages, { style: 'currency', currency })

  return formatter.format(value / currencyModifier).replace('.00', '')
})

Vue.filter('niceGuid', function (v) {
  if ( typeof v === 'string' ) {
    return v ? v.slice(0, 8) : ''
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

Vue.filter('niceNumber', function (v) {
  if (typeof v === 'number') {
    return new Intl.NumberFormat().format(v)
  }
  return v
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

Vue.filter('niceDate', function (value, isShort=false) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  if (isShort) {
    return moment(value).format('M/D/YY')
  } else {
    return moment(value).format('MMMM Do, YYYY')
  }
})

Vue.filter('niceDateTime', function (value, is24=true) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  if (is24) {
    //return moment(value).format('MMM Do, YYYY HH:mm:ss')
    return moment(value).format('MMM Do, YYYY HH:mm')
  } else {
    //return moment(value).format('MMM Do, YYYY h:mm:ss a')
    return moment(value).format('MMM Do, YYYY h:mm a')
  }
})

Vue.filter('niceDateTimeShort', function (value) {
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  return moment(value).format('MMM Do, YYYY HH:mm:ss')
})

Vue.filter('niceFilesize', function (value) {
  const K = 1000
  const M = K * 1000
  const G = M * 1000
  if (typeof value === 'undefined' || value === null || value === '') {
    return ''
  }
  if ( value >= G  ) {
    return Math.floor(value/G)+' GB'
  } else if ( value >= M ) {
    return Math.floor(value/M)+' MB'
  } else if (value >= K ) {
    return Math.floor(value/K)+' KB'
  } else {
    return value
  }
})

Vue.filter('renderCampaignBlurb', function (campaign) {
  const { created_at, offer_days, targeted_customer_group } = campaign
  let str = 'For '
  switch ( targeted_customer_group ) {
    case 'new':
      str += 'new'
      break
    case 'expired':
      str += 'expired'
      break
    case 'new-and-expired':
      str += 'new & expired'
      break
  }
  str += ' subscribers'
  str += ` \u2022  ends ${moment(created_at).add(offer_days, 'days').format('MMM D')}`
  if (campaign.is_subscriber_count_unlimited) {
    str += ` \u2022 unlimited`
  } else {
    if (campaign.remaining_count > 0) {
      str += ` \u2022 ${campaign.remaining_count} left`
    } else {
      str += ` \u2022 none left`
    }
  }
  return str
})
