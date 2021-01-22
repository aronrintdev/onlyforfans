/**
 * Utility function for making sure that global objects are loaded before trying to use them.
 * Returns Promise for when object is available.
 */
import { indexOf, mapKeys } from 'lodash'

/**
 * Returns Promise that resolves when object is available
 * @param {[String, String[]]} names - Name or names of the object on the window global to check
 * @param {Object} options
 * ```
 * {
 *    type?: // Specific type to check for, pass array if you are using multiple
 *    instance?: // Specific instance to check for, pass array if you are using multiple
 *    interval?: 100 // Interval between checks (in ms),
 *    failAfter?: 100 // Number of times to check object before throwing error
 *    withDebug: false // Debug log for each attempt at getting objects, pass in logger you wish to use
 * }
 * ```
 * @returns {Promise} returns window item or items being checked.
 */
export const whenAvailable = function (names, options = {}) {
  const multipleItems = typeof names === 'object'
  names = multipleItems ? names : [ names ]
  const types = typeof options.type === 'object' ?
    options.type :
    typeof options.type === 'undefined' ?
    undefined :
    [options.type]
  const instances = typeof options.instance === 'object' ?
    options.instance :
    typeof options.instance === 'undefined' ?
    undefined :
    [options.type]
  const interval = options.interval || 100
  const failAfter = options.failAfter || 100
  const withDebug = options.withDebug || false

  function debug(...args) {
    if (withDebug) {
      withDebug.debug(...args)
    }
  }

  return new Promise((resolve, reject) => {
    function check(attempt = 1) {
      var available = true
      const checks = names.map((name, i) => {
        var thisAvailable = false
        if (types && types[i]) {
          if (typeof window[name] === types[i]) {
            return `typeof ${types[i]}`
          }
        } else if (instances && instances[i]) {
          if (window[name] instanceof instances[i]) {
            return `instanceof ${instances[i].toString()}`
          }
        } else {
          if (window[name]) {
            return 'defined'
          }
        }
        return false
      })
      if (indexOf(checks, false) === -1) {
        debug(`Objects on window ${JSON.stringify(names)} available after ${attempt} checks`, { checks })
        resolve(multipleItems ? names.map(name => (window[name])) : window[names[0]])
        return
      }
      debug(`Objects on window ${JSON.stringify(names)} not available on check ${attempt}, attempting again in ${interval}ms`, { checks: mapKeys(checks, (v, i) => (names[i])) })
      if (attempt >= failAfter) {
        reject(new Error(`Objects on window ${JSON.stringify(names)} did not become available in ${attempt} checks (${ interval * attempt / 1000 } seconds)`))
        return
      }
      setTimeout(() => check(attempt + 1), interval)
    }
    check()
  })
}

export default {
  install(Vue, options) {
    Vue.prototype.$whenAvailable = whenAvailable
  }
}
