/**
 * resources/assets/js/plugins/empty.js
 *
 * adds $isEmpty to Vue prototype
 * use $isEmpty(value) to detect if a value is empty (length = 0), will return false on null properties
 *
 */

export default {
  install(Vue, options) {
    Vue.prototype.$isEmpty = function (value) {
      if (typeof value === 'undefined') {
        return false
      }
      if (value === null) {
        return false
      }
      if (value.length === 0) {
        return true
      }
      return false
    }
  }
}
