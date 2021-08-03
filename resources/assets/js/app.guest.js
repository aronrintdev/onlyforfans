/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const isProduction = process.env.NODE_ENV === 'production';

import store from './store/guest';

require('./bootstrap');
require('./bootstrap/fontAwesome');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//axios.defaults.baseURL = '/';


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

import VueAxios from 'vue-axios';
Vue.use(VueAxios, window.axios);

import VueI18n from 'vue-i18n';
Vue.use(VueI18n);

import { VueReCaptcha } from 'vue-recaptcha-v3';
Vue.use(VueReCaptcha, { siteKey: process.env.MIX_RECAPTCHAV3_SITEKEY });

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

/**
 * Vue Animejs
 */
import VueAnime from 'vue-animejs';
Vue.use(VueAnime);

import VueTimeago from 'vue-timeago';
Vue.use(VueTimeago, {
  name: 'Timeago', // Component name, `Timeago` by default
  locale: 'en', // Default locale
  /*
  // We use `date-fns` under the hood
  // So you can use all locales from it
  locales: {
    'zh-CN': require('date-fns/locale/zh_cn'),
    ja: require('date-fns/locale/ja')
  }
  */
});

/**
 * VueSlider
 */
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'
Vue.component('VueSlider', VueSlider)

/**
 * vue-croppie
 */
import VueCroppie from 'vue-croppie';
import 'croppie/croppie.css' // import the croppie css manually
Vue.use(VueCroppie);

/**
 * vue2-touch-events
 */
import Vue2TouchEvents from 'vue2-touch-events';
Vue.use(Vue2TouchEvents);

import VueAwesomeSwiper from 'vue-awesome-swiper';
import 'swiper/css/swiper.css';
Vue.use(VueAwesomeSwiper);
// ---

/**
 * Loading localization translations
 */
import i18n from './i18n'

/**
 * Route Loading for Guest
 */
import VueRouter from 'vue-router'
Vue.use(VueRouter)
import router from './routes/guest.routes'
import App from './views/templates/Guest.vue'

const app = new Vue({
  router,
  i18n,
  store,
  render: h => h(App),
}).$mount('#app');

