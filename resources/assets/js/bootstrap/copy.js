/**
 * resources/assets/js/bootstrap/copy.js
 * Bootstrap copy component
 */
import Vue from 'vue'

/**
 * vue-clipboard2: https://github.com/Inndy/vue-clipboard2
 * ```
 * <button type="button"
 *   v-clipboard:copy="message"
 *   v-clipboard:success="onCopy"
 *   v-clipboard:error="onError"
 * >Copy!</button>
 * ```
 * or
 * ```
 * doCopy: function () {
 *  this.$copyText(this.message).then(function (e) {
 *    alert('Copied')
 *    console.log(e)
 *  }, function (e) {
 *      alert('Can not copy')
 *      console.log(e)
 *    })
 *  }
 * ```
 */
import VueClipboard from 'vue-clipboard2'

Vue.use(VueClipboard)
