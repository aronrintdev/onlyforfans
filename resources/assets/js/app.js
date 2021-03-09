/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const isProduction = process.env.NODE_ENV === 'production';

import store from './store';

require('./bootstrap');
require('./bootstrap/fontAwesome');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//axios.defaults.baseURL = '/';

import Vue from 'vue';

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

import VueObserveVisibility from 'vue-observe-visibility';
Vue.use(VueObserveVisibility);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'
Vue.use(BootstrapVue) // Telling Vue to use this in whole application
Vue.use(BootstrapVueIcons)

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

import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'
Vue.component('VueSlider', VueSlider)

import VueTagsInput from '@johmun/vue-tags-input'
Vue.component('VueTagsInput', VueTagsInput)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('main-navbar', require('./components/common/MainNavbar.vue').default);
Vue.component('my-vault', require('./components/vault/Dashboard.vue').default);
Vue.component('my-saved', require('./components/saved/Dashboard.vue').default);

// converts from cents to dollars, and formats
Vue.filter('niceCurrency', function (valueInCents) {
  let value = valueInCents ? (valueInCents/100) : 0
  value.toFixed(2)
  return `$${value}`
})

Vue.filter('niceGuid', function (v) {
  return v.slice(-12)
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

export const eventBus = new Vue({
/*
    methods: {
        switchDetails: function(obj) {
            this.$emit('detailsWasSwitched', obj);
        },
        fixStatus: function(index) {
            this.$emit('statusWasFixed', index);
        },
    }
*/
});

/**
 * Loading localization translations
 */
import i18n from './i18n'

import VueRouter from 'vue-router'
Vue.use(VueRouter)
import router from './routes/app.routes'

import App from './views/templates/App.vue'

const app = new Vue({
    router,
    i18n,
    store,
    render: h => h(App),
}).$mount('#app');
