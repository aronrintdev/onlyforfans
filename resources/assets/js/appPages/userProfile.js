/**
 * Temp Vue container for user profile page
 */
const isProduction = process.env.NODE_ENV === 'production';
require('../bootstrap');
import Vue from 'vue';

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

import ForceCompute from '../plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from '../plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
// import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
// Vue.use(BootstrapVue) // Telling Vue to use this in whole application
// Vue.use(BootstrapVueIcons)

Vue.component('online-status', require('../components/user/OnlineStatus.vue').default);

/**
 * Loading localization translations
 */
import i18n from '../i18n';

const app = new Vue({
    i18n,
    el: '#user-cover',
});