/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const isProduction = process.env.NODE_ENV === 'production';
const logLevel = process.env.MIX_LOG_LEVEL || isProduction ? 'error' : 'debug';

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
    logLevel : logLevel,
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

import IsEmpty from './plugins/isEmpty';
Vue.use(IsEmpty);

import ForceCompute from './plugins/forceCompute';
Vue.use(ForceCompute);

import WhenAvailable from './plugins/whenAvailable';
Vue.use(WhenAvailable);

//import BootstrapVue from 'bootstrap-vue' //Importing
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
 // Telling Vue to use this in whole application
Vue.use(BootstrapVue, {
  BToast: { toaster: 'b-toaster-top-center'},
});
Vue.use(BootstrapVueIcons);

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

import Clipboard from 'v-clipboard'
Vue.use(Clipboard)

import VueSlider from 'vue-slider-component';
import 'vue-slider-component/theme/default.css';
Vue.component('VueSlider', VueSlider);

import VueTagsInput from '@johmun/vue-tags-input';
Vue.component('VueTagsInput', VueTagsInput);

import VueLazyload from 'vue-lazyload';
const loadimage = require('./../static/images/loading.gif');

Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: loadimage,
  loading: loadimage,
  attempt: 1
});

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

import 'photoswipe/dist/photoswipe.css';
import 'photoswipe/dist/default-skin/default-skin.css';
import Photoswipe from 'vue-pswipe';
Vue.use(Photoswipe);

/**
 * v-mask: https://github.com/probil/v-mask#readme
 */
import { VueMaskDirective } from 'v-mask';
Vue.directive('mask', VueMaskDirective);

/**
 * v-clipboard
 */
import VueClipboard from 'vue-clipboard2'
VueClipboard.config.autoSetContainer = true // add this line
Vue.use(VueClipboard)

/**
 * vue-social-sharing
 */
import VueSocialSharing from 'vue-social-sharing'
Vue.use(VueSocialSharing);

/**
 * vue-plyr
 */
import VuePlyr from 'vue-plyr'
import 'vue-plyr/dist/vue-plyr.css'
Vue.use(VuePlyr, {
  plyr: {}
});

/**
 * vue-masonry
 */
import { VueMasonryPlugin } from 'vue-masonry'
Vue.use(VueMasonryPlugin)

Vue.directive('custom-click-outside', {
  bind: function (el, binding, vnode) {
    el.clickOutsideEvent = function (event) {
      // here I check that click was outside the el and his children
      if (!(el == event.target || el.contains(event.target))) {
        // and if it did, call method provided in attribute value
        vnode.context[binding.expression](event);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent)
  },
  unbind: function (el) {
    document.body.removeEventListener('click', el.clickOutsideEvent)
  },
});



/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('main-navbar', require('./components/common/MainNavbar.vue').default);
//Vue.component('my-vault', require('./components/vault/Dashboard.vue').default);
//Vue.component('my-saved', require('./components/saved/Dashboard.vue').default);

require('./bootstrap/filters')

//export const eventBus = new Vue({ });

// Localization translations
import i18n from './i18n';

import VueRouter from 'vue-router';
Vue.use(VueRouter);
import router from './routes/app.routes';

import App from './views/templates/App.vue';

const app = new Vue({
    router,
    i18n,
    store,
    render: h => h(App),
}).$mount('#app');
