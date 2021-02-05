/**
 * Adminstration Pages Application
 */
const isProduction = process.env.NODE_ENV === 'production';

import Vue from 'vue';

require('./bootstrap');
require('./bootstrap/fontAwesome');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * https://github.com/imcvampire/vue-axios#readme
 *
 * Adds axios to component object.
 * Use `this.axios`, `Vue.axios`, or `this.$htttp` to access axios.
 */
import VueAxios from 'vue-axios';
Vue.use(VueAxios, window.axios);

/**
 * Enable $log
 * Use: `this.$log.error(error)`
 * logLevels : ['debug', 'info', 'warn', 'error', 'fatal']
 */
import VueLogger from 'vuejs-logger';
const options = {
    isEnabled: true,
    logLevel : isProduction ? 'error' : 'debug',
    stringifyArguments : false,
    showLogLevel : true,
    showMethodName : !isProduction,
    separator: '|',
    showConsoleColors: true
};
Vue.use(VueLogger, options);

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

/**
 * Loading localization translations
 */
import i18n from './i18n'

// Admin Components
require('./components/admin');
// Vue.component('main-navbar', require('./components/common/MainNavbar.vue').default);

const app = new Vue({
    i18n,
    el: '#app',
});
