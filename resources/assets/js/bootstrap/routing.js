/**
 * Adds Ziggy routing to Vue if available
 * Make sure to call @routes directive in header to insure preloading
 */
import Vue from 'vue';

if (window.route) {
  Vue.mixin({ methods: { $route: window.route }});
}
