/**
 * Adds $forceCompute to Vue component instance. This will force a computed property to recompute it's value
 * use:
 * ```
 * this->$forceCompute('computedPropertyName',)
 * // Skips component update
 * this->$forceCompute('computedPropertyName', false)
 * ```
 */

export default {
  install(Vue, options) {
    Vue.prototype.$forceCompute = function(computedName, forceUpdate = true) {
      if (this._computedWatchers[computedName]) {
        this._computedWatchers[computedName].run()
        if (forceUpdate) {
          this.$forceUpdate()
        }
      }
    }
  }
}
