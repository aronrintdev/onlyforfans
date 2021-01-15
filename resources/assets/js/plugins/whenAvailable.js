/**
 * Utility function for making sure that global objects are loaded before trying to use them.
 * Returns Promise for when object is available.
 */

/**
 * Returns Promise that resolves when object is available
 * @param {String} name - Name of the object on the window global
 * @param {Object} options
 * ```
 * {
 *    type?: // Specific type to check for,
 *    instance?: // Specific instance to check for,
 *    interval?: 100 // Interval between checks (in ms),
 *    failAfter?: 1000 // Number of times to check object before throwing error
 * }
 * ```
 * @returns {Promise}
 */
export const whenAvailable = function (name, options = {}) {
  return new Promise((resolve, reject) => {
    const { type, instance } = options
    const interval = options.interval || 100
    const failAfter = options.failAfter || 1000
    function check(attempt = 0) {
      if (attempt >= failAfter) {
        reject(new Error(`Object window.${name} did not become available in ${attempt} checks`))
        return
      }
      var available = false
      if (type) {
        if (typeof window[name] === type) {
          available = true
        }
      } else if (instance) {
        if (window[name] instanceof instance) {
          available = true
        }
      } else {
        if (window[name]) {
          available = true
        }
      }
      if (available) {
        resolve(window[name])
        return
      }
      setTimeout(() => check(attempt++), interval)
    }
    check(name)
  })
}

export default {
  install(Vue, options) {
    Vue.prototype.$whenAvailable = whenAvailable
  }
}
